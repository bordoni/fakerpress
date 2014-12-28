<?php
namespace FakerPress;

$roles = get_editable_roles();

$_elements = array_merge( array( 'h3', 'h4', 'h5', 'h6', 'p' ) );

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
							<input style='width: 90px;' class='qty-range-min' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 1', 'fakerpress' ); ?>' value='' name='fakerpress_qty_min' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 90px;' class='qty-range-max' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 10', 'fakerpress' ); ?>' value='' name='fakerpress_qty_max' disabled/>
						</div>
						<p class="description"><?php _e( 'How many users should be generated, use both fields to get a randomized number of users within the given range.', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container field-container-description_use_html'>
					<th scope="row"><label for="fakerpress_description_use_html"><?php _e( 'Use HTML', 'fakerpress' ); ?></label></th>
					<td>
						<input type='checkbox' style="margin-top: -3px;" name='fakerpress_description_use_html' checked />
						<p style='display: inline-block;' class="description"><?php _e( 'Use HTML on your randomized User Description?', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container fk-field-dependent' data-fk-depends=".field-container-description_use_html input" data-fk-condition='true'>
					<th scope="row"><label for="fakerpress_description_html_tags"><?php _e( 'HTML tags', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[description_html_tags]">
							<input type='hidden' class='field-select2-tags' name='fakerpress_description_html_tags' value='<?php echo esc_attr( implode( ',', $_elements ) ); ?>' data-tags='<?php echo json_encode( $_elements ); ?>' />
						</div>
						<p class="description"><?php _e( 'Select the group of tags that can be selected to print on the User Description.', 'fakerpress' ); ?></p>
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