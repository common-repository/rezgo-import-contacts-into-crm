<?php if ( ! defined( 'ABSPATH' ) ) exit; /* Exit if accessed directly*/ ?>
<h2><?php _e( 'Rezgo to CRM Importer &gt; Log', $domain ) ?></h2>
<h3><?php _e( 'The following is a list of all activity for the importer', $domain ) ?></h3>
<center>
<br>
<table class="rezgo2crm_notifications_table">
		<thead>
			<tr>
				<th class="rezgo2crm_date"><span class="nobr"><?php _e( 'Date', $domain ); ?></span></th>
				<th class="rezgo2crm_trans_num"><span class="nobr"><?php _e( 'Booking #', $domain ); ?></span></th>
				<th class="rezgo2crm_client"><span class="nobr"><?php _e( 'Customer Name', $domain ); ?></span></th>
				<th class="rezgo2crm_email"><span class="nobr"><?php _e( 'Email', $domain ); ?></span></th>
				<th class="rezgo2crm_system"><span class="nobr"><?php _e( 'CRM', $domain ); ?></span></th>
				<th class="rezgo2crm_is_success"><span class="nobr"><?php _e( 'Status?', $domain ); ?></span></th>
				<th class="rezgo2crm_error"><span class="nobr"><?php _e( 'Message', $domain ); ?></span></th>
			</tr>
		</thead>

		<tbody><?php
			foreach ( $this->logs as $log) {
				?><tr class="log_record">
					<td class="rezgo2crm_date">
						<?php echo date_i18n( get_option( 'date_format' ), $log->booking_timestamp ); ?>
					</td>
					<td class="rezgo2crm_trans_num">
						<?php echo $log->trans_num?>
					</td>
					<td class="rezgo2crm_client">
						<?php echo $log->first_name?> <?php echo $log->last_name?>
					</td>
					<td class="rezgo2crm_email">
						<?php echo $log->email?>
					</td>
					<td class="rezgo2crm_system">
						<?php echo $log->system?>
					</td>
					<td class="rezgo2crm_is_success">
						<?php if($log->is_success): ?>
						<img src="<?php echo $this->plugin_base_url?>images/success.gif">
						<?php else: ?>
						<img src="<?php echo $this->plugin_base_url?>images/failure.png">
						<?php endif; ?>
					</td>
					<td class="rezgo2crm_error">
						<?php echo $log->error?>
					</td>
				</tr><?php
			}
		?></tbody>

	</table>

	<?php if($this->total_pages>1) { ?>
		<?php if($this->page>1) { ?>
			<a href="<?php echo $this->pagination_url."1"; ?>">&lt;&lt;</a>
		<?php } else { ?>
			1
		<?php } ?>
		&nbsp;
		
		<?php for($i=max($this->page-2,2);$i<min($this->page+3,$this->total_pages);$i++) { ?>
			<?php if($this->page==$i) { ?>
				<?php echo $i?>
			<?php } else { ?>
				<a href="<?php echo $this->pagination_url.$i; ?>"><?php echo $i?></a>
			<?php } ?>
			&nbsp;
		<?php } ?>
		
		<?php if($this->page!=$this->total_pages) { ?>
			<a href="<?php echo $this->pagination_url.$this->total_pages; ?>">&gt;&gt;</a>
		<?php } else { ?>
			<?php echo $this->page?>
		<?php } ?>
		
	<?php } ?>
	
<script type="text/javascript" >
jQuery(document).ready(function($) {
    
    
    // keys
    $( "#submitAddNotify" ).click(function() {
	window.location= "<?php echo $this->pagination_url?>&edit=0";
	return false;
    });	
    
    $( ".deleteAction" ).click(function() {
	return confirm("<?php _e( 'Are you sure?', $domain )?>");
    });	
    
});
</script>