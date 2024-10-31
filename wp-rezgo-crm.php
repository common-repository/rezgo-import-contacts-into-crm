<?php
/*
Plugin Name: Rezgo Import Contacts into CRM
Plugin URI: http://wordpress.org/extend/plugins/rezgo-import-contacts-crm/
Description: The plugin connects to your Rezgo account using the Rezgo Webhook and API and, based on your settings, will add the main billing contact to your selected CRM or email marketing software.
Version: 0.1
Author: alexvp
Author URI: http://alexvp.elance.com
*/

$Rezgo2CrmObj= new Rezgo2Crm();

//init 
register_activation_hook( __FILE__, array($Rezgo2CrmObj,'install') );
register_deactivation_hook( __FILE__, array($Rezgo2CrmObj,'uninstall') );
// UI
add_action('admin_menu', array($Rezgo2CrmObj,'admin_menu') );
add_action('wp_ajax_rezgo_crm', array($Rezgo2CrmObj, 'ajax_rezgo_crm') );
add_action('admin_head', array($Rezgo2CrmObj,'admin_head') );
// webhook
add_action('init', array($Rezgo2CrmObj,'try_call_webhook') );

// init Constant Contact SDK here :(
include_once dirname( __FILE__)."/libs/Ctct/autoload.php";
use Ctct\ConstantContact;
use Ctct\Components\Contacts\Contact;
use Ctct\Components\Contacts\Note;
use Ctct\Components\Contacts\Address;
use Ctct\Components\Contacts\CustomField;
use Ctct\Components\Contacts\ContactList;
use Ctct\Components\Contacts\EmailAddress;
use Ctct\Exceptions\CtctException;
// as "use" didn't work inside function


class Rezgo2Crm{
	var $text_domain = "Rezgo2Crm";
	var $api_endpoint = "http://xml.rezgo.com/xml";
	var $settings_name = "rezgo2crm";
	var $mapping_name = "rezgo2crm_fields";
	var $hook_var= "rezgo-crm-webhook";
	var $per_page = 25;
	//VR settings
	var $vertical_response_wsdl = "https://api.verticalresponse.com/wsdl/1.0/VRAPI.wsdl"; 
	var $vertical_response_ses_time = 1;  // duration of session in minutes
	

	function Rezgo2Crm() {
		global $wpdb;
		
		$this->plugin_base_url = plugins_url("/", __FILE__);
		$this->table_log = $wpdb->prefix."rezgo_crm_log";
		$this->settings = get_option($this->settings_name,array());
		if(!$this->settings['active_systems'])
			$this->settings['active_systems']=array();
			
		$this->mapping = get_option($this->mapping_name,array());
		if( !$this->mapping['mailchimp'] ) {
			$this->mapping['mailchimp']=array();
			$this->mapping['mailchimp']['email_address'] = 'EMAIL';
			$this->mapping['mailchimp']['first_name'] = 'FNAME';
			$this->mapping['mailchimp']['last_name'] = 'LNAME';
		}
		if( !$this->mapping['zohocrm'] ) {
			$this->mapping['zohocrm']=array();
			$this->mapping['zohocrm']['email_address'] = 'Email';
			$this->mapping['zohocrm']['first_name'] = 'First Name';
			$this->mapping['zohocrm']['last_name'] = 'Last Name';
			$this->mapping['zohocrm']['tour_name'] = 'Company'; // mandatory!
		}
		if( !$this->mapping['icontact'] ) {
			$this->mapping['icontact']=array();
			$this->mapping['icontact']['email_address'] = 'email';
			$this->mapping['icontact']['first_name'] = 'firstName';
			$this->mapping['icontact']['last_name'] = 'lastName';
		}
		if( !$this->mapping['constant_contact'] ) {
			$this->mapping['constant_contact']=array();
			$this->mapping['constant_contact']['email_address'] = 'email';
			$this->mapping['constant_contact']['first_name'] = 'first_name';
			$this->mapping['constant_contact']['last_name'] = 'last_name';
			$this->mapping['constant_contact']['address_1'] = 'addresses->line1';
			$this->mapping['constant_contact']['trans_num'] = 'notes->note';
			$this->mapping['constant_contact']['item_id'] = 'CustomField1';
		}
		if( !$this->mapping['vertical_response'] ) {
			$this->mapping['vertical_response']=array();
			$this->mapping['vertical_response']['email_address'] = 'email_address';
			$this->mapping['vertical_response']['first_name'] = 'first_name';
			$this->mapping['vertical_response']['last_name'] = 'last_name';
		}
		
		$this->all_systems = array('MailChimp'=>'mailchimp', 'ZohoCRM'=>'zohocrm','iContact'=>'icontact',
			'Constant Contact'=>'constant_contact','Vertical Response'=>'vertical_response'
		);
	}
	
	function install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$sql = "CREATE TABLE IF NOT EXISTS `{$this->table_log}` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`booking_timestamp` int(10) unsigned NOT NULL,
			`trans_num` varchar(255) NOT NULL,
			`first_name` varchar(255) NOT NULL,
			`last_name` varchar(255) NOT NULL,
			`email` varchar(255) NOT NULL,
			`system` varchar(255) NOT NULL,
			`is_success` enum('0','1') NOT NULL,
			`error` varchar(255) NOT NULL,
  			PRIMARY KEY (`id`)
			)";
		dbDelta( $sql );
	}
	
	function uninstall() {
		global $wpdb;
		
		delete_option($this->settings_name);
		delete_option($this->mapping_name);
		
		$wpdb->query("DROP TABLE IF EXISTS `{$this->table_log}`");
	}
	
	
	function admin_menu() {
		
		$version = get_bloginfo('version');
		$vparts = explode('.', $version);
		if ((int)$vparts[0] >= 3 && (int)$vparts[1] >= 8) {
			$plugin_icon = 'dashicons-cloud';
		} else {
			$plugin_icon = '';
		}
		
		add_menu_page(
		'Rezgo2Crm', 
		__('Rezgo to CRM', $this->text_domain), 
		'manage_options',
		'rezgo-crm-menu', 
		array(&$this, 'settings_page'),
		$plugin_icon
		);

		add_submenu_page(
		'rezgo-crm-menu',
		'Rezgo2Crm', 
		__('Global Settings', $this->text_domain),
		'manage_options',
		'rezgo-crm-menu', 
		array(&$this, 'settings_page'));
		
		foreach($this->all_systems as $system_name=>$system_id) {
			if(in_array($system_id,$this->settings['active_systems']))
				add_submenu_page(
				'rezgo-crm-menu',
				'Rezgo2Crm', 
				$system_name,
				'manage_options',
				'rezgo-crm-' . $system_id, 
				array(&$this, 'settings_page_' . $system_id));
		}
		
		add_submenu_page(
		'rezgo-crm-menu',
		'Rezgo2Crm', 
		__('Log', $this->text_domain), 
		'manage_options',
		'rezgo-crm-log', 
		array(&$this, 'log_page'));
	}
	
	function admin_head() {
		wp_register_style( 'rezgo-crm-style', plugins_url('style.css', __FILE__) );
		wp_enqueue_style( 'rezgo-crm-style' );
	}
	
	// load html pages for menu
	// $domain is text domain
	// for http://codex.wordpress.org/Function_Reference/_e
	function show_page($page) {
		$domain = $this->text_domain;
		include "html/$page.php";
	}
	function settings_page() {
		$this->show_page("settings");
	}
	
	function log_page() {
		global $wpdb;
		
		$limit = $this->per_page;
		$total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_log}");
		$total_pages = ceil($total/$limit);
		
		$page = intval(@$_GET['num']);
		if(!$page)
			$page = 1;
		if($page > $total_pages)
			$page = $total_pages;
		$ofs = ($page-1) * $limit;
		
		$this->total_pages = $total_pages;
		$this->page = $page;
		$this->pagination_url = "admin.php?page=rezgo-crm-log&num=";
		$this->logs = $wpdb->get_results("SELECT * FROM {$this->table_log} ORDER BY id DESC LIMIT $ofs,$limit");
		$this->show_page("log");
	}
	
	function ajax_rezgo_crm() {
		if(!empty($_POST['method']) AND method_exists($this,$_POST['method']))
			$this->$_POST['method']();
		else
			_e('non-valid method', $this->text_domain );
	}
	function ajax_reply($is_success,$args) {
		$args['result'] = $is_success ? 'success': 'failed';
		echo json_encode($args);
		die();
	}
	function ajax_set_rezgo_keys() {
		$url = $this->api_endpoint . '?transcode=' . $_POST['account_cid'] . '&key=' . $_POST['api_key'] . '&i=company' ;
		$reply= wp_remote_get( $url );
		if ( is_wp_error( $reply) ) 
			$this->ajax_reply(false, array('connect_problem'=>$reply->get_error_message()) );

		$xml = simplexml_load_string($reply['body']);
		if(empty($xml->domain))// we get only string with error message
			$this->ajax_reply(false, array('connect_problem'=>(string)$xml) );

		$this->settings['rezgo_account_cid'] = $_POST['account_cid'];
		$this->settings['rezgo_api_key'] = $_POST['api_key'];
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array('company_website'=>"http://{$xml->domain}.rezgo.com") );
	}
	function ajax_set_system_state() {
		if($_POST['state']=='true') {
			$this->settings['active_systems'][] = $_POST['system'];
		} else {
			$pos = array_search($_POST['system'],$this->settings['active_systems']);
			if($pos!==false)
				unset($this->settings['active_systems'][$pos]);
		}
		//var_dump($this->settings['active_systems']);die();
		update_option($this->settings_name,$this->settings);
		$this->ajax_reply(true, array() );
	}
	function ajax_set_fields() {
		$this->mapping[$_POST['system']]=$_POST['mapping'];
		//var_dump($this->mapping);die();
		update_option($this->mapping_name,$this->mapping);
		$this->ajax_reply(true, array('message'=>__("Fields updated!",$this->text_domain)) );
	}
	
	function try_call_webhook(){
		if(!isset($_GET[$this->hook_var])) 
			return ;
		
		set_time_limit(0);
		//get raw POST
		$post = file_get_contents("php://input"); 
		// if php added extra \
		if( get_magic_quotes_gpc() )
			$post = stripslashes( $post );

		$xml = @simplexml_load_string($post);
		if(!$xml OR !isset($xml->booking))
			$this->die_log("ERR:Bad XML passed");
			
		//check if got correct values
		$bookings = array();
		$req_fields = array("date","item_id","tour_name","trans_num","email_address");
		foreach($xml->booking as $b) {
			$errors = array();
			foreach($req_fields  as $f)
				if(empty($b->$f))
					$errors[]="$f is required";
			if($errors)
				$this->die_log("ERR:".join(". ", $errors));
				
			//get fields
			$t = array();
			$bookings[] = $b;
		}
		if(empty($bookings))	
			$this->die_log("ERR:No bookings. Format updated?");
		
		foreach($bookings as $b) {
			foreach($this->settings['active_systems'] as $system) {
				if( $this->{'pass_to_'.$system}($b,$success,$error) )
					$this->write_log($b,$system,$success,$error);
			}
		}
		//done
		$this->die_log("OK:".count($bookings));
	}
	
	function write_log($b,$system,$success,$error) {
		global $wpdb;
		//record result
		$t = array();
		$t['booking_timestamp']=$b->date;
		$t['trans_num'] = $b->trans_num;
		$t['first_name'] = $b->first_name;
		$t['last_name'] = $b->last_name;
		$t['email'] = $b->email_address;
		$t['system'] = $system;
		$t['is_success'] = $success ? 1: 0;
		$t['error'] = $error;
		$t= array_map("strval", $t);
		$wpdb->insert($this->table_log,$t);
	}
	
	// will add extra log here ?
	function die_log($msg) {
		die($msg);
	}
	
	function map_fields($b,$system) {
		//gather vars
		$merge = array ();
		foreach($this->mapping[$system] as $rField=>$mField) {
			if(!$mField) 
				continue;
			$merge[$mField] = (string)$b->$rField;
			// must convert to dateformat 
			if($rField=="date" OR $rField=="date_purchased") {
				$merge[$mField] = date("m/d/Y", $merge[$mField]);
			}
		}
		return $merge;
	}
	
	
	// MailChimp
	function settings_page_mailchimp() {
		$this->show_page("settings_mailchimp");
	}
	function ajax_set_keys_mailchimp() {
		if(!$_POST['api_key'] OR !$_POST['list_id'])
			$this->ajax_reply(false, array('connect_problem'=>__("API Key and List ID are required!",$this->text_domain)) );
		
		include_once dirname( __FILE__)."/libs/Mailchimp.php";
		try {
			$api = new Mailchimp($_POST['api_key']);
			$reply = $api->lists->getList( array('list_id'=>$_POST['list_id']) );
		} catch(Exception $e){
			$this->ajax_reply(false, array('connect_problem'=>$e->getMessage()) );
		}
		
		if( !empty($reply['errors']) ) {
			$text = "";
			foreach($reply['errors'] as $e)
				$text[] = $e['error'];
			$this->ajax_reply(false, array('connect_problem'=>join("<br>",$text ) ) );
		}
		$this->settings['mailchimp_list_id'] = $_POST['list_id'];
		$this->settings['mailchimp_api_key'] = $_POST['api_key'];
		$this->settings['mailchimp_no_double_optin'] = $_POST['mc_no_double_optin']=='true' ? 1:0;
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array('list_name'=>$reply['data'][0]['name']) );
	}
	function pass_to_mailchimp($b,&$success,&$error) {
		$success='';$error='';
		if(!$this->settings['mailchimp_api_key'])
			return false; // api is not configured
			
		$merge = $this->map_fields($b,'mailchimp');
		
		//call api
		include_once dirname( __FILE__)."/libs/Mailchimp.php";
		$success = true; $error ='';
		try {
			$api = new Mailchimp($this->settings['mailchimp_api_key']);
			$email = array('email'=>(string)$b->email_address);
			$reply = $api->lists->subscribe( $this->settings['mailchimp_list_id'], $email, $merge, 'html', $this->settings['mailchimp_no_double_optin']!=1 );
		} catch(Exception $e){
			$error = $e->getMessage();
			$success = false;
			echo $b->trans_num." ".$error;
		}
		return true;
	}
	
	//Zoho
	function settings_page_zohocrm() {
		$this->show_page("settings_zohocrm");
	}
	function ajax_set_keys_zohocrm() {
		if(!$_POST['auth_token'])
			$this->ajax_reply(false, array('connect_problem'=>__("AUTHTOKEN is required!",$this->text_domain)) );
		$result = $this->zoho_api_call($response,$_POST['auth_token'],"Leads",'getFields');
		if(!$result ) 
			$this->ajax_reply(false, array('connect_problem'=>$response) );
			
		$this->settings['zohocrm_auth_token'] = $_POST['auth_token'];
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array());
	}
	function pass_to_zohocrm($b,&$success,&$error) {
		$success='';$error='';
		if(!$this->settings['zohocrm_auth_token'])
			return false; // api is not configured
			
		$merge = $this->map_fields($b,'zohocrm');
		
		//make 
		$xml = new SimpleXMLElement( "<Leads></Leads>" );
		$row = $xml->addChild( 'row' );
		$row['no'] = 1;
		// setup required 
		if(empty($merge['Last Name']))
			$merge['Last Name'] = 'none';
		if(empty($merge['Company']))
			$merge['Company'] = 'none';
		foreach($merge as $k=>$v) {
			$fl = $row->addChild( 'FL',$v);
			$fl['val'] = $k;
		}
		//format it!
		$dom = dom_import_simplexml( $xml )->ownerDocument;
		$dom->formatOutput = true;
		$xml = $dom->saveXML($dom->documentElement);//skip <xml...>
		
		//call api
		$success = $this->zoho_api_call($response,$this->settings['zohocrm_auth_token'],"Leads",'insertRecords',$xml);
		if(!$success) 
			$error = $response;
		elseif(!isset($response->result->recorddetail)) {
			$succes = false;
			$error = 'unknow api error';
		}
		return true;
	}
	function zoho_api_call(&$message,$zoho_token,$type,$ops,$xml='')
	{
		$data = array();
		$data['newFormat']=1;
		$data['authtoken']=$zoho_token;
		$data['scope']="crmapi";
		$data['xmlData']=$xml;
		$url="https://crm.zoho.com/crm/private/xml/$type/$ops?".http_build_query($data);
		
		$result = file_get_contents($url);
		$xml =simplexml_load_string($result);
		
		$success=false;
		if(!$xml)
			$message="API failed: NOT XML ".$result;
		elseif(!empty($xml->error->message ))
			$message=$xml->error->message;
		else
		{
			$success=true;
			$message=$xml;
		}
		return $success;
	}
	
	//iContact
	function settings_page_icontact() {
		$this->show_page("settings_icontact");
	}
	function ajax_set_keys_icontact() {
		if(!$_POST['app_id'] OR !$_POST['api_user'] OR !$_POST['api_pass'] OR !$_POST['list_name'])
			$this->ajax_reply(false, array('connect_problem'=>__("All fields are required!",$this->text_domain)) );
		
		include_once dirname( __FILE__)."/libs/iContactApi.php";
		// Give the API your information
		iContactApi::getInstance()->setConfig(array(
				'appId'       => $_POST['app_id'], 
				'apiPassword' => $_POST['api_pass'], 
				'apiUsername' => $_POST['api_user']
		));
		// Store the singleton
		$oiContact = iContactApi::getInstance();
		$list_id = 0;
		try {
			$lists = $oiContact->getLists();
			foreach($lists as $l)
				if($l->name == $_POST['list_name'])
					$list_id = $l->listId;
		} catch(Exception $e){
			$this->ajax_reply(false, array('connect_problem'=>join("<br>",$oiContact->getErrors()) ) );
		}
		
		if(!$list_id)
			$this->ajax_reply(false, array('connect_problem'=>__("Wrong list name!",$this->text_domain)) );
		
		$this->settings['icontact_app_id'] = $_POST['app_id'];
		$this->settings['icontact_api_user'] = $_POST['api_user'];
		$this->settings['icontact_api_pass'] = $_POST['api_pass'];
		$this->settings['icontact_list_name'] = $_POST['list_name'];
		$this->settings['icontact_list_id'] = $list_id;
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array('list_name'=>$_POST['list_name'] ) );
	}
	function pass_to_icontact($b,&$success,&$error) {
		$success='';$error='';
		if(!$this->settings['icontact_app_id'])
			return false; // api is not configured
			
		$merge = $this->map_fields($b,'icontact');
		
		//call api
		include_once dirname( __FILE__)."/libs/iContactApi.php";
		// Give the API your information
		iContactApi::getInstance()->setConfig(array(
				'appId'       => $this->settings['icontact_app_id'], 
				'apiPassword' => $this->settings['icontact_api_pass'], 
				'apiUsername' => $this->settings['icontact_api_user']
		));
		// Store the singleton
		$oiContact = iContactApi::getInstance();
		$success = true; $error ='';
		try {
			$contacts = $oiContact->makeCall("/a/{$oiContact->setAccountId()}/c/{$oiContact->setClientFolderId()}/contacts", 'POST', array($merge), 'contacts');
			$oiContact->subscribeContactToList($contacts[0]->contactId, $this->settings['icontact_list_id']);
		} catch(Exception $e){
			$error = join("<br>",$oiContact->getErrors());
			$success = false;
			echo $b->trans_num." ".$error;
		}
		return true;
	}
	
	//Constant Contact
	function settings_page_constant_contact() {
		$this->show_page("settings_constant_contact");
	}
	function ajax_set_keys_constant_contact() {
		if(!$_POST['app_key'] OR !$_POST['access_token'] OR !$_POST['list_name'])
			$this->ajax_reply(false, array('connect_problem'=>__("All fields are required!",$this->text_domain)) );
			
		$cc = new ConstantContact($_POST['app_key']);
		$list_id = 0;
		try{
			$lists = $cc->getLists($_POST['access_token']);
			foreach($lists as $l)
				if($l->name == $_POST['list_name'])
					$list_id = $l->id;
		} catch (CtctException $e) {
			$errors = array();
			foreach($e->getErrors() as $error)
				$errors[] = $error['error_message'];
			$this->ajax_reply(false, array('connect_problem'=>join("<br>",$errors) ) );
		}
		if(!$list_id)
			$this->ajax_reply(false, array('connect_problem'=>__("Wrong list name!",$this->text_domain)) );
		
		$this->settings['constant_contact_app_key'] = $_POST['app_key'];
		$this->settings['constant_contact_access_token'] = $_POST['access_token'];
		$this->settings['constant_contact_list_name'] = $_POST['list_name'];
		$this->settings['constant_contact_list_id'] = $list_id;
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array('list_name'=>$_POST['list_name'] ) );
	}
	function pass_to_constant_contact($b,&$success,&$error) {
		$success='';$error='';
		if(!$this->settings['constant_contact_app_key'])
			return false; // api is not configured
			
		$merge = $this->map_fields($b,'constant_contact');
		$cc = new ConstantContact($this->settings['constant_contact_app_key']);

		//find contact by email 
		try{
			$email_exists = $cc->getContactByEmail($this->settings['constant_contact_access_token'], $merge['email']);
		} catch (CtctException $ex) {
			$errors = array();
			foreach($ex->getErrors() as $e)
				$errors[] = $e['error_message'];
			$error = join("<br>",$errors);
			$success = false;
			echo $b->trans_num." ".$error;
			return true;
		}
		$is_new = empty($email_exists->results);
        if ( $is_new ) {
            $contact = new Contact();
			$contact->addEmail($merge['email']);
		}
         else 
            $contact = $email_exists->results[0];

		//fill class data
        $contact->addList($this->settings['constant_contact_list_id']);
		unset($merge['email']);
        
        // has note?
        if(isset($merge['notes->note'])) {
			if ( $is_new )   
				$contact->notes[] = Note::create( array('note'=>$merge['notes->note']) );
			unset($merge['notes->note']);
        }
        
        //parse address?
        $addr = array();
        foreach($merge as $k=>$v) {
			if(preg_match('#^addresses->(.+)$#',$k,$m)) {
				$addr[trim($m[1])] = $v;
				unset($merge[$k]);
			}
		}
		if($addr AND $is_new) 
			$contact->addresses[] = Address::create($addr);
		
		//custom fields 
		$cc_standart_fields = explode(",", "status,first_name,middle_name,last_name,confirmed,source,prefix_name,job_title,company_name,home_phone,work_phone,cell_phone,fax,source_details");
		$contact->custom_fields = array();
        foreach($merge as $k=>$v) {
			if(!in_array($k,$cc_standart_fields)) {
				$contact->custom_fields[] = CustomField::create( array('name'=>$k, 'value'=>$v ) );
				unset($merge[$k]);
			}
		}
		//just set standart values
		foreach($merge as $k=>$v)
			$contact->$k = $v;
			
		// api call 
		$success = true; $error ='';
		try{
			if ( $is_new )   
				$reply = $cc->addContact($this->settings['constant_contact_access_token'],$contact);
			else 
				$reply = $cc->updateContact($this->settings['constant_contact_access_token'],$contact);
		} catch (CtctException $ex) {
			$errors = array();
			foreach($ex->getErrors() as $e)
				$errors[] = $e['error_message'];
			$error = join("<br>",$errors);
			$success = false;
			echo $b->trans_num." ".$error;
		}
		return true;
	}
	
	//Vertical Response
	function settings_page_vertical_response() {
		$this->show_page("settings_vertical_response");
	}
	function ajax_set_keys_vertical_response() {
		if(!$_POST['api_user'] OR !$_POST['api_pass'] OR !$_POST['list_name'])
			$this->ajax_reply(false, array('connect_problem'=>__("All fields are required!",$this->text_domain)) );
			
		$vr = new SoapClient($this->vertical_response_wsdl,array()); 
		$list_id = 0;
		try{
			$sid = $vr->login( array( 'username' => $_POST['api_user'],'password' => $_POST['api_pass'],
									'session_duration_minutes' => $this->vertical_response_ses_time)
						);
			$lists = $vr->enumerateLists( array( 'session_id' => $sid )); 
			foreach($lists as $l)
				if($l->name == $_POST['list_name'])
					$list_id = $l->id;
		} catch (SoapFault $e) {
			$this->ajax_reply(false, array('connect_problem'=>$e->faultstring ) );
		}
		if(!$list_id)
			$this->ajax_reply(false, array('connect_problem'=>__("Wrong list name!",$this->text_domain)) );
		
		$this->settings['vertical_response_api_user'] = $_POST['api_user'];
		$this->settings['vertical_response_api_pass'] = $_POST['api_pass'];
		$this->settings['vertical_response_list_name'] = $_POST['list_name'];
		$this->settings['vertical_response_list_id'] = $list_id;
		update_option($this->settings_name,$this->settings);
		
		$this->ajax_reply(true, array('list_name'=>$_POST['list_name'] ) );
	}
	
	function pass_to_vertical_response($b,&$success,&$error) {
		$success='';$error='';
		if(!$this->settings['vertical_response_api_user'])
			return false; // api is not configured
			
		$merge = $this->map_fields($b,'vertical_response');
		
		$member = array();
		foreach($merge as $k=>$v)
			$member[] = array( 'name'=>$k, 'value'=>$v );
		
		// api call 
		$vr = new SoapClient($this->vertical_response_wsdl,array()); 
		$success = true; $error ='';
		try{
			$sid = $vr->login( array( 'username' => $this->settings['vertical_response_api_user'],
										'password' => $this->settings['vertical_response_api_pass'],
										'session_duration_minutes' => $this->vertical_response_ses_time)
							);
			$vr->addListMember( array('session_id'  => $sid, 
										'list_member' => array('list_id'=>$this->settings['vertical_response_list_id'], 'member_data'=>$member) )
							);
		} catch (SoapFault $e) {
			$error = $e->faultstring;
			$success = false;
			echo $b->trans_num." ".$error;
		}
		return true;
	}
	
}
?>