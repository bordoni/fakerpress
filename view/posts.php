<?php

$users = get_users(
				array(
					'blog_id' => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields' => array( 
									'ID', 
									'display_name' 
								), // When you pass only one field it returns an array of the values
				)
			);

$_json_users_output = array();
foreach ( $users as $user ) {
	$_json_users_output[] = array(
		'id' => $user->ID,
		'text' => esc_attr( $user->display_name ),
	);
}

$types = get_post_types( array(), 'names' );

$_json_types_output = array();
foreach ( $types as $type ) {
	$_json_types_output[] = array(
		'id' => $type,
		'text' => $type,
	);
}
?>

<div class='wrap'>
	<h2><?php echo esc_attr( FakerPress\Admin::$view->title ); ?></h2>

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
						<p class="description"><?php _e( 'The amount of Posts you want to generate on this request', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_max_date"><?php _e( 'Date', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[min_date]">
							<input style='width: 150px;' class='field-datepicker' type='text' max='25' min='1' placeholder='<?php esc_attr_e( 'dd/mm/aaaa', 'fakerpress' ); ?>' value='' name='fakerpress_min_date' />
						</div>
						<p class="description"><?php _e( 'Min Date', 'fakerpress' ); ?></p>
					</td>
					<td>
						<div id="fakerpress[max_date]">
							<input style='width: 150px;' class='field-datepicker' type='text' max='25' min='1' placeholder='<?php esc_attr_e( 'dd/mm/aaaa', 'fakerpress' ); ?>' value='' name='fakerpress_max_date' />
						</div>
						<p class="description"><?php _e( 'Max Date', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_author"><?php _e( 'Author', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[author]">
							<input type='hidden' class='field-select2-simple' name='fakerpress_author' data-possibilities='<?php echo json_encode( $_json_users_output ); ?>' />
						</div>
						<p class="description"><?php _e( 'Choose some users to be authors of posts generated.', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>