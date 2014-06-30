<?php
namespace FakerPress;
?>
<div class='wrap'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<tr>
					<th scope="row"><label for="fakerpress_qty"><?php _e( 'Erase faked data', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[erase]">
							<input type='text' placeholder='<?php esc_attr_e( 'The cold never bothered me anyway!', 'fakerpress' ); ?>' value='' class='regular-text' name='fakerpress_erase_check' />
							<?php submit_button( __( 'Delete!', 'fakerpress' ), 'primary', 'fakerpress_erase_data', false ); ?>
						</div>
						<p class="description"><?php echo wp_kses( sprintf( esc_attr__( 'To erase all data generated type "%s".', 'fakerpress' ), '<b>Let it Go!</b>' ), array( 'b' => array() ) ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>

	</form>
</div>