<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; Constant Contact', $domain ) ?></h2>
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'iContact API Settings', $domain ) ?></legend>
        <a target=_blank href='https://constantcontact.mashery.com/apps/mykeys'> <?php _e( 'Click to create application and generate access token', $domain ) ?></a>
        <div style="position: relative">
        <br>
        <strong><?php _e( 'Application Key', $domain ) ?>: </strong><input id="constant_contact_app_key" size=50 type="text" value="<?php echo $this->settings['constant_contact_app_key']; ?>" /> <br>
        <br>
        <strong><?php _e( 'Access token', $domain ) ?>: </strong><input id="constant_contact_access_token" size=50 type="text" value="<?php echo $this->settings['constant_contact_access_token']; ?>" /> <br>
        <br>
        <strong><?php _e( 'List Name', $domain ) ?>:</strong><input id="constant_contact_list_name" size=50 type="text" value="<?php echo $this->settings['constant_contact_list_name']; ?>" /> <br>
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
        <legend style="font-weight:bold"><?php _e( 'Rezgo 2 Constant Contact Fields', $domain ) ?></legend>
        <?php _e( 'The following fields are available for import from the Rezgo booking notification. If you want to import field, add your corresponding Constant Contact field in the space provided. Use -> for complex structures, like addresses or notes', $domain ) ?>
		<a target=_blank href="http://developer.constantcontact.com/docs/contacts-api/contacts-resource.html">View API documentation</a>
        <?php 
			$system_name='Constant Contact'; $system_id='constant_contact';
			include dirname( __FILE__)."/mapping_form.php";
        ?>
    </fieldset>
    
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $( "#MC_submitKeys" ).click(function() {
		$('#MC_submitKey_failedDiv').hide();
		$('#MC_submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_keys_constant_contact','app_key':$('#constant_contact_app_key').val(), 
						'access_token':$('#constant_contact_access_token').val(), 'list_name':$('#constant_contact_list_name').val()
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