<?php
namespace FakerPress;
use FakerPress\Fields;

// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}

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
	<h2><?php echo esc_attr( $view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $view->slug . ( isset( $view->action ) ? '.' . $view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) : ?>
					<?php $field->output( true ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</form>
</div>