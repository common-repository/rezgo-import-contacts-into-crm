<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; MailChimp', $domain ) ?></h2>
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'MailChimp API Settings', $domain ) ?></legend>
        <div style="position: relative">
        <br>
        <strong><?php _e( 'API key', $domain ) ?>: </strong><input id="mc_api_key" size=50 type="text" value="<?php echo $this->settings['mailchimp_api_key']; ?>" /> <br>
        <br>
        <strong><?php _e( 'List ID', $domain ) ?>:</strong><input id="mc_list_id" size=20 type="text" value="<?php echo $this->settings['mailchimp_list_id']; ?>" /> <br>
        <br>
        <input id="mc_no_double_optin" value=1 type="checkbox" "<?php checked($this->settings['mailchimp_no_double_optin'],1); ?>" /> &nbsp;<strong><?php _e( 'Disable Double Opt-in', $domain ) ?>:</strong>
        <br><br>
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', $domain ) ?>" id='MC_submitKeys' />
         <div id="MC_submitKey_successDiv">
	      <div class="submitKey_Icon">
		  <img src="<?php echo $this->plugin_base_url?>images/success.gif"><br>
		  <?php _e( 'Connected', $domain ) ?>
	      </div>
	      <div class="submitKey_Msg">
		  <?php _e( 'The API Connection is working', $domain ) ?><br><br>
		   <?php _e( 'Assigned to list ', $domain ) ?> <span id="MC_list_name"></span>
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
        <legend style="font-weight:bold"><?php _e( 'Rezgo 2 MailChimp Fields', $domain ) ?></legend>
        <?php _e( 'The following fields are available for import from the Rezgo booking notification. If you want to import field, add your corresponding MailChimp field in the space provided. In order for this to work, you must have created these merge fields in your MailChimp list settings already.', $domain ) ?>
        <?php 
			$system_name='MailChimp'; $system_id='mailchimp';
			include dirname( __FILE__)."/mapping_form.php";
        ?>
    </fieldset>

<script type="text/javascript" >
jQuery(document).ready(function($) {
// mailchimp keys
    $( "#MC_submitKeys" ).click(function() {
		$('#MC_submitKey_failedDiv').hide();
		$('#MC_submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_keys_mailchimp','api_key':$('#mc_api_key').val(), 'list_id':$('#mc_list_id').val(),'mc_no_double_optin':$('#mc_no_double_optin').is(":checked") }
		$.post(ajaxurl, data, function(response) {
			if(response.result=='success')
			{
				$('#MC_submitKey_successDiv').show();
				$('#MC_list_name').text(response.list_name);
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