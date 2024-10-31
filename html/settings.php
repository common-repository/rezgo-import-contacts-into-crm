<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; Settings', $domain ) ?></h2>

<form method="post" action="" id="rezgo2crm_settings">
	<div class="field_frame">
    <fieldset>
        <legend><?php _e( 'Rezgo API Settings', $domain ) ?></legend>
        
        <div class="field_contents">
        
        <dl>
          <dt><label for="account_cid"><?php _e( 'Account CID', $domain ) ?></label></dt>
          <dd><input id="account_cid" size=10 type="text" value="<?php echo $this->settings['rezgo_account_cid']; ?>" /></dd>
          <dt><label for="api_key"><?php _e( 'API key', $domain ) ?></label></dt>
          <dd><input id="api_key" size=20 type="text" value="<?php echo $this->settings['rezgo_api_key']; ?>" /></dd>
        </dl>

        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id='submitKeys' />
        
        <div id="submitKey_successDiv">
          <div class="submitKey_Icon">
          <img src="<?php echo $this->plugin_base_url?>images/success.gif"><br>
          <?php _e( 'Connected', $domain ) ?>
          </div>
          <div class="submitKey_Msg">
          <?php _e( 'The API Connection is working', $domain ) ?><br><br>
          <a id="company_website" href="" target=_blank></a>
          </div>
        </div>
        <div id="submitKey_failedDiv">
          <div class="submitKey_Icon">
          <img src="<?php echo $this->plugin_base_url?>images/failure.png"><br>
          <?php _e( 'Failed', $domain ) ?>
          </div>
          <div class="submitKey_Msg">
          <?php _e( 'The API Connection is NOT working', $domain ) ?><br><br>
          <div id="connect_problem"></div>
          </div>
        </div>
        
      </div>
        
    </fieldset>
   </div>
    
    <div class="field_frame">

      <fieldset>
        <legend><?php _e( 'Active systems', $domain ) ?></legend>
        <div class="field_contents">
					<?php foreach($this->all_systems as $system_name=>$system_id) { ?>
          <input class='rezgo_active_system' type=checkbox id='<?php echo $system_id; ?>' value=1 <?php checked(in_array($system_id,$this->settings['active_systems']))?>
          <strong>&nbsp;&nbsp;<?php echo $system_name; ?></strong><br /> 
          <?php } ?>
        </div>
      </fieldset>
      
    </div>
    
		<div class="field_frame">
      <fieldset>
        <legend><?php _e( 'Rezgo Webhook Endpoint', $domain ) ?></legend>
        <div class="field_contents">
					<?php _e( 'In order for the plugin to send notifications, it requires that Rezgo send notifications to it. The following URL is the Webhook Endpoint. Copy and paste this URL into your Rezgo Webhook Endpoint settings', $domain ) ?>
          <br><br>
          <strong><?php echo home_url("/?{$this->hook_var}=true"); ?></strong>
        </div>
      </fieldset>
    </div>
    
</form>

<script type="text/javascript" >
jQuery(document).ready(function($) {

// Rezgo keys
    $( "#submitKeys" ).click(function() {
		$('#submitKey_failedDiv').hide();
		$('#submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_rezgo_keys','account_cid':$('#account_cid').val(), 'api_key':$('#api_key').val()}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				$('#submitKey_successDiv').show();
				$('#company_website').text(response.company_website);
				$('#company_website').attr('href',response.company_website);
			}
			else
			{
				$('#submitKey_failedDiv').show();
				$('#connect_problem').html(response.connect_problem);
			}
		}
		,"json"
		);
		return false;
    });	

//switch 
    $( ".rezgo_active_system" ).click(function() {
		var data = { action: 'rezgo_crm','method': 'ajax_set_system_state','system':this.id, 'state':$(this).is(':checked')}
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				location.reload()
			}
		}
		,"json"
		);
		return true;
    });	

});
</script>