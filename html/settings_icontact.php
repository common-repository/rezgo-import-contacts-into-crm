<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; iContact', $domain ) ?></h2>
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'iContact API Settings', $domain ) ?></legend>
        <a target=_blank href='https://app.icontact.com/icp/core/externallogin/'><?php _e( 'Click to manage api', $domain ) ?></a>
        <div style="position: relative">
        <br>
        <strong><?php _e( 'Application ID', $domain ) ?>: </strong><input id="icontact_app_id" size=50 type="text" value="<?php echo $this->settings['icontact_app_id']; ?>" /> <br>
        <br>
        <strong><?php _e( 'Username', $domain ) ?>: </strong><input id="icontact_api_user" size=20 type="text" value="<?php echo $this->settings['icontact_api_user']; ?>" /> 
        <strong><?php _e( 'Api Password', $domain ) ?>: </strong><input id="icontact_api_pass" size=20 type="text" value="<?php echo $this->settings['icontact_api_pass']; ?>" /> <br>
        <br>
        <strong><?php _e( 'List Name', $domain ) ?>:</strong><input id="icontact_list_name" size=50 type="text" value="<?php echo $this->settings['icontact_list_name']; ?>" /> <br>
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
        <legend style="font-weight:bold"><?php _e( 'Rezgo 2 iContact Fields', $domain ) ?></legend>
        <?php _e( 'The following fields are available for import from the Rezgo booking notification. If you want to import field, add your corresponding iContact field in the space provided. In order for this to work, you must have created these custom fields in your iContact account already.', $domain ) ?>
        <?php 
			$system_name='iContact'; $system_id='icontact';
			include dirname( __FILE__)."/mapping_form.php";
        ?>
    </fieldset>
    
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $( "#MC_submitKeys" ).click(function() {
		$('#MC_submitKey_failedDiv').hide();
		$('#MC_submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_keys_icontact','app_id':$('#icontact_app_id').val(), 'list_name':$('#icontact_list_name').val(),
						'api_user':$('#icontact_api_user').val(),'api_pass':$('#icontact_api_pass').val()
					}
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