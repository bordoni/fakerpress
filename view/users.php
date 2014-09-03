<?php
namespace FakerPress;

$roles = get_editable_roles();

$_json_roles_output = array();
foreach ( $roles as $role_name => $role_data ) {
	$_json_roles_output[] = array(
		'id' => $role_name,
		'text' => esc_attr( $role_data['name'] ),
	);
}
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
						<p class="description"><?php _e( 'The range of Users you want to generate', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="fakerpress_roles"><?php _e( 'Roles', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[roles]">
							<input type='hidden' class='field-select2-simple' name='fakerpress_roles' data-possibilities='<?php echo json_encode( $_json_roles_output ); ?>' />
						</div>
						<p class="description"><?php _e( 'Sampling roles to be used', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>