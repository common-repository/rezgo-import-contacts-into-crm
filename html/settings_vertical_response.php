<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; Vertical Response', $domain ) ?></h2>
    <fieldset class="rezgo2crm_params">
        <legend style="font-weight:bold"><?php _e( 'Vertical Response API Settings', $domain ) ?></legend>
        <div style="position: relative">
        <br>
        <strong><?php _e( 'Username', $domain ) ?>: </strong><input id="vertical_response_api_user" size=20 type="text" value="<?php echo $this->settings['vertical_response_api_user']; ?>" /> 
        <strong><?php _e( 'Password', $domain ) ?>: </strong><input id="vertical_response_api_pass" size=20 type="text" value="<?php echo $this->settings['vertical_response_api_pass']; ?>" /> <br>
        <br>
        <strong><?php _e( 'List Name', $domain ) ?>:</strong><input id="vertical_response_list_name" size=50 type="text" value="<?php echo $this->settings['vertical_response_list_name']; ?>" /> <br>
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
        <legend style="font-weight:bold"><?php _e( 'Rezgo 2 Vertical Response Fields', $domain ) ?></legend>
        <?php _e( 'The following fields are available for import from the Rezgo booking notification. If you want to import field, add your corresponding Vertical Response field in the space provided. In order for this to work, you must have created these list fields in your Vertical Response account already.', $domain ) ?>
        <?php 
			$system_name='Vertical Response'; $system_id='vertical_response';
			include dirname( __FILE__)."/mapping_form.php";
        ?>
    </fieldset>
    
<script type="text/javascript" >
jQuery(document).ready(function($) {
    $( "#MC_submitKeys" ).click(function() {
		$('#MC_submitKey_failedDiv').hide();
		$('#MC_submitKey_successDiv').hide();
		var data = { action: 'rezgo_crm','method': 'ajax_set_keys_vertical_response','list_name':$('#vertical_response_list_name').val(),
						'api_user':$('#vertical_response_api_user').val(),'api_pass':$('#vertical_response_api_pass').val()
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