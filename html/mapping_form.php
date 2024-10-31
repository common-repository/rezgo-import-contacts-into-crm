<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
    <br><br>
    <form name="map_fields_form" id="map_fields_form">
        <table class=rezgo_map_fields>
			<tr><th ><?php _e("Rezgo",$domain)?></th><th><?php echo $system_name;?></th></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Billing Email', $domain ) ?></td><td><input type=text name=mapping[email_address] value="<?php echo $this->mapping[$system_id]['email_address']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Billing First Name', $domain ) ?></td><td><input type=text name=mapping[first_name] value="<?php echo $this->mapping[$system_id]['first_name']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Billing Last Name', $domain ) ?></td><td><input type=text name=mapping[last_name] value="<?php echo $this->mapping[$system_id]['last_name']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Billing Address 1', $domain ) ?></td><td><input type=text name=mapping[address_1] value="<?php echo $this->mapping[$system_id]['address_1']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Billing Address 2', $domain ) ?></td><td><input type=text name=mapping[address_2] value="<?php echo $this->mapping[$system_id]['address_2']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Billing City', $domain ) ?></td><td><input type=text name=mapping[city] value="<?php echo $this->mapping[$system_id]['city']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Billing State/Prov', $domain ) ?></td><td><input type=text name=mapping[stateprov] value="<?php echo $this->mapping[$system_id]['stateprov']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Billing Postal', $domain ) ?></td><td><input type=text name=mapping[postal_code] value="<?php echo $this->mapping[$system_id]['postal_code']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Billing Country', $domain ) ?></td><td><input type=text name=mapping[country] value="<?php echo $this->mapping[$system_id]['country']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Billing Phone', $domain ) ?></td><td><input type=text name=mapping[phone_number] value="<?php echo $this->mapping[$system_id]['phone_number']?>"></td></tr>
        </table>
        <span id=rezgo_map_fields_spacer >&nbsp;</span>
        <table class=rezgo_map_fields>
			<tr><th ><?php _e("Rezgo",$domain)?></th><th><?php echo $system_name;?></th></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Transaction Number', $domain ) ?></td><td><input type=text name=mapping[trans_num] value="<?php echo $this->mapping[$system_id]['trans_num']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Tour Name', $domain ) ?></td><td><input type=text name=mapping[tour_name] value="<?php echo $this->mapping[$system_id]['tour_name']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Tour Option', $domain ) ?></td><td><input type=text name=mapping[option_name] value="<?php echo $this->mapping[$system_id]['option_name']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Tour SKU', $domain ) ?></td><td><input type=text name=mapping[item_id] value="<?php echo $this->mapping[$system_id]['item_id']?>"></td></tr>
			<tr  class="mapping_field_odd"><td><?php _e( 'Booked For Date', $domain ) ?></td><td><input type=text name=mapping[date] value="<?php echo $this->mapping[$system_id]['date']?>"></td></tr>
			<tr  class="mapping_field_even"><td><?php _e( 'Booked On Date', $domain ) ?></td><td><input type=text name=mapping[date_purchased] value="<?php echo $this->mapping[$system_id]['date_purchased']?>"></td></tr>
        </table>
        <br>
		<div style="height:20px;clear:both"></div>
		<div style="text-align:center;">
			<button class="button-primary" id='submitFields' name='submitFields' /><?php _e( 'Save Fields', $domain ) ?></button>
		</div>
    </form>

<script type="text/javascript" >
jQuery(document).ready(function($) {
    $( "#submitFields" ).click(function() {
   		var data = 'action=rezgo_crm&method=ajax_set_fields&system=<?php echo $system_id;?>&'+$('#map_fields_form :input' ).serialize();
		$.post(ajaxurl, data, function(response) {
			alert(response.message)
		}
		,"json"
		);
		return false;
    });	
});
</script>    