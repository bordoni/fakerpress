<?php
namespace FakerPress;

// Mount the carbon values for dates
$_json_date_selection_output = Dates::get_intervals();

?>

<div class='wrap'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<tr>
					<th scope="row"><label for="fakerpress_qty"><?php _e( 'Quantity', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[qty]" class='fakerpress_qty_range'>
							<input style='width: 90px;' class='qty-range-min' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 0', 'fakerpress' ); ?>' value='' name='fakerpress_qty_min' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 90px;' class='qty-range-max' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 10', 'fakerpress' ); ?>' value='' name='fakerpress_qty_max' disabled/>
						</div>
						<p class="description"><?php _e( 'The range of Comments you want to generate on this request', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_interval_date"><?php _e( 'Date', 'fakerpress' ); ?></label></th>
					<td>
						<div class='fakerpress-range-group'>
							<select id="fakerpress_interval_date" class='field-date-selection' data-placeholder='<?php esc_attr_e( 'Select an Interval', 'fakerpress' ); ?>' style="margin-right: 5px; margin-top: -4px;">
								<option></option>;
								<?php foreach ($_json_date_selection_output as $option) { ?>
								<option data-min="<?php echo date( 'm/d/Y', strtotime( $option['min'] ) ); ?>" data-max="<?php echo date( 'm/d/Y', strtotime( $option['max'] ) ); ?>" value="<?php echo $option['text']; ?>"><?php echo $option['text']; ?></option>
								<?php } ?>
							</select>

							<input style='width: 150px;' class='field-datepicker field-min-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_min_date' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 150px;' class='field-datepicker field-max-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_max_date' />
						</div>
						<p class="description-date description"><?php _e( 'Choose the range for the posts dates.', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>