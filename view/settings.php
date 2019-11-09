<?php
namespace FakerPress;
$fields[] = new Field(
	'text',
	[
		'id' => 'erase_phrase',
		'placeholder' => 'The cold never bothered me anyway!',
	],
	[
		'label' => __( 'Erase faked data', 'fakerpress' ),
		'description' => __( 'To erase all data generated type "<b>Let it Go!</b>".', 'fakerpress' ),
		'actions' => [
			'delete' => __( 'Delete!', 'fakerpress' ),
		],
	]
);

?>
<div class='wrap'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php
				foreach ( $fields as $field ) {
					$field->output( true );
				}
				?>
			</tbody>
		</table>
	</form>
</div>