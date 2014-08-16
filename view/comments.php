<?php // Mount the carbon values for dates
$_json_date_selection_output = array(
	array(
		'id' => 'today',
		'text' => esc_attr__( 'Today', 'fakerpress' ),
		'min' => Carbon::today(),
		'max' => Carbon::today(),
	),
	array(
		'id' => 'yesterday',
		'text' => esc_attr__( 'Yesterday', 'fakerpress' ),
		'min' => Carbon::yesterday(),
		'max' => Carbon::yesterday(),
	),
	array(
		'id' => 'tomorrow',
		'text' => esc_attr__( 'Tomorrow', 'fakerpress' ),
		'min' => Carbon::tomorrow(),
		'max' => Carbon::tomorrow(),
	),
	array(
		'id' => 'this week',
		'text' => esc_attr__( 'This week', 'fakerpress' ),
		'min' => Carbon::today()->subDays( 7 ),
		'max' => Carbon::today(),
	),
	array(
		'id' => 'this month',
		'text' => esc_attr__( 'This month', 'fakerpress' ),
		'min' => Carbon::today()->day( 1 ),
		'max' => Carbon::today(),
	),
	array(
		'id' => 'this year',
		'text' => esc_attr__( 'This year', 'fakerpress' ),
		'min' => Carbon::today()->day( 1 )->month( 1 ),
		'max' => Carbon::today(),
	),
	array(
		'id' => 'last 15 days',
		'text' => esc_attr__( 'Last 15 days', 'fakerpress' ),
		'min' => Carbon::today()->subDays( 15 ),
		'max' => Carbon::today(),
	),
	array(
		'id' => 'next 15 days',
		'text' => esc_attr__( 'Next 15 Days', 'fakerpress' ),
		'min' => Carbon::today(),
		'max' => Carbon::today()->addDays( 15 ),
	),
); ?>

<div class='wrap'>
	<h2><?php echo esc_attr( \FakerPress\Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( FakerPress\Plugin::$slug . '.request.' . FakerPress\Admin::$view->slug . ( isset( FakerPress\Admin::$view->action ) ? '.' . FakerPress\Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<tr>
					<th scope="row"><label for="fakerpress_qty"><?php _e( 'Quantity', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[qty]">
							<input style='width: 90px;' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 12', 'fakerpress' ); ?>' value='' name='fakerpress_qty' />
						</div>
						<p class="description"><?php _e( 'The amount of Comments you want to generate on this request', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_max_date"><?php _e( 'Date', 'fakerpress' ); ?></label></th>
					<td>
						<div class='fakerpress-range-group'>
							<div id="fakerpress-date-selection">
								<select class='field-date-selection' name='fakerpress_date_selection'>
									<?php foreach ($_json_date_selection_output as $option) { ?>
									<option data-min="<?php echo date( 'm/d/Y', strtotime( $option['min'] ) ); ?>" data-max="<?php echo date( 'm/d/Y', strtotime( $option['max'] ) ); ?>" value="<?php echo $option['text']; ?>"><?php echo $option['text']; ?></option>
									<?php } ?>
								</select>
							</div>
							<div id="fakerpress-min-date">
								<input style='width: 150px;' class='field-datepicker field-min-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_min_date' />
							</div>
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date"></div>
							<div id="fakerpress-max-date">
								<input style='width: 150px;' class='field-datepicker field-max-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_max_date' />
							</div>
						</div>
						<p class="description-date"><?php _e( 'Choose the range for the posts dates.', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>