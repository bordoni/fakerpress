<?php
namespace FakerPress;

// Mount the carbon values for dates
$_json_date_selection_output = Dates::get_intervals();

$_elements = array_merge( \Faker\Provider\HTML::$sets['header'], \Faker\Provider\HTML::$sets['list'], \Faker\Provider\HTML::$sets['block'] );

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
							<input style='width: 90px;' class='qty-range-min' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 1', 'fakerpress' ); ?>' value='' name='fakerpress_qty_min' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 90px;' class='qty-range-max' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 10', 'fakerpress' ); ?>' value='' name='fakerpress_qty_max' disabled/>
						</div>
						<p class="description"><?php _e( 'How many comments should be generated, use both fields to get a randomized number of comments within the given range.', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container field-container-comment_content_use_html'>
					<th scope="row"><label for="fakerpress_comment_content_use_html"><?php _e( 'Use HTML', 'fakerpress' ); ?></label></th>
					<td>
						<input type='checkbox' style="margin-top: -3px;" name='fakerpress_comment_content_use_html' checked />
						<p style='display: inline-block;' class="description"><?php _e( 'Use HTML on your randomized comment content?', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container fk-field-dependent' data-fk-depends=".field-container-comment_content_use_html input" data-fk-condition='true'>
					<th scope="row"><label for="fakerpress_comment_content_html_tags"><?php _e( 'HTML tags', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[comment_content_html_tags]">
							<input type='hidden' class='field-select2-tags' name='fakerpress_comment_content_html_tags' value='<?php echo esc_attr( implode( ',', $_elements ) ); ?>' data-tags='<?php echo json_encode( $_elements ); ?>' />
						</div>
						<p class="description"><?php _e( 'Select the group of tags that can be selected to print on the Comment Content.', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_interval_date"><?php _e( 'Date', 'fakerpress' ); ?></label></th>
					<td>
						<div class='fakerpress-range-group'>
							<select id="fakerpress_interval_date" class='field-date-selection' data-placeholder='<?php esc_attr_e( 'Select an Interval', 'fakerpress' ); ?>' style="margin-right: 5px; margin-top: -4px;">
								<option></option>;
								<?php foreach ( $_json_date_selection_output as $option ) { ?>
								<option data-min="<?php echo esc_attr( date( 'm/d/Y', strtotime( $option['min'] ) ) ); ?>" data-max="<?php echo esc_attr( date( 'm/d/Y', strtotime( $option['max'] ) ) ); ?>" value="<?php echo esc_attr( $option['text'] ); ?>"><?php echo esc_attr( $option['text'] ); ?></option>
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