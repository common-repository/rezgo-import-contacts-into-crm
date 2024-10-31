<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; ZohoCRM', $domain ) ?></h2>
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'ZohoCRM API Settings', $domain ) ?></legend>
        <div style="position: relative">
        <a target=_blank href='https://accounts.zoho.com/apiauthtoken/create?SCOPE=ZohoCRM/crmapi'><?php _e( 'Click to generate token', $domain ) ?></a><br>
        <br>
        <strong><?php _e( 'AUTHTOKEN', $domain ) ?>: </strong><input id="zohocrm_auth_token" size=50 type="text" value="<?php echo $this->settings['zohocrm_auth_token']; ?>" /> <br>
        <br><br>
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id='MC_submitKeys' />
         <div id="MC_submitKey_successDiv">
	      <div class="submitKey_Icon">
		  <img src="<?php echo $this->plugin_base_url?>images/success.gif"><br>
		  <?php _e( 'Connected', $domain ) ?>
	      </div>
	      <div class="submitKey_Msg">
		  <?php _e( 'The API Connection is working', $domain ) ?><br><br>
	      </div>
         </div>
         <div id="MC_submitKey_failedDiv">
	      <div class="submitKey_Icon">
		  <img src="<?php echo $this->plugin_base_url?>images/failure.png"><br>
		  <?php _e( 'Failed', $domain ) ?>
	      </div>
	      <div class="submitKey_Msg">
		  <?php _e( 'The API Connection is NOT working', $domain ) ?><br><br>
		  <div id="MC_connect_problem"></div>
	      </span>
         </div>
        </div>
    </fieldset>
    
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'Rezgo 2 ZohoCRM Fields', $domain ) ?></legend>
        <?php _e( 'The following fields are available for import from the Rezgo booking notification. If you want to import field, add your corresponding ZohoCRM field in the space provided. In order for this to work, you must have created these custom fields in your ZohoCRM Leads already. Company and Last name are mandatory fields', $domain ) ?>
        <?php 
			$system_name='ZohoCRM'; $system_id='zohocrm';
			include dirname( __FILE__)."/mapping_form.php";
        ?>
    </fieldset>

<script type="text/javascript" >
jQuery(document).ready(function($) {
    $( "#MC_submitKeys" ).click(function() {
		$('#MC_submitKey_failedDiv').hide();
		$('#MC_submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_keys_zohocrm','auth_token':$('#zohocrm_auth_token').val() }
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				$('#MC_submitKey_successDiv').show();
			}
			else
			{
				$('#MC_submitKey_failedDiv').show();
				$('#MC_connect_problem').html(response.connect_problem);
			}
		}
		,"json"
		);
		return false;
    });	
});
</script>    