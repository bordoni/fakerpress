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
						<p class="description"><?php _e( 'The amount of Users you want to generate on this request', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>