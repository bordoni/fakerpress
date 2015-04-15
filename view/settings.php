<?php
namespace FakerPress;
$fields[] = new Field(
	'erase_phrase',
	array(
		'type' => 'text',
		'label' => __( 'Erase faked data', 'fakerpress' ),
		'description' => __( 'To erase all data generated type "<b>Let it Go!</b>".', 'fakerpress' ),
		'attributes' => array(
			'placeholder' => 'The cold never bothered me anyway!',
		),
		'actions' => array(
			'delete' => __( 'Delete!', 'fakerpress' ),
		),
	)
);

$fields[] = new Field(
	'heading-500px',
	array(
		'type' => 'heading',
		'label' => __( 'API: <i>500px', 'fakerpress' ),
		'actions' => array(
			'delete' => __( 'Delete!', 'fakerpress' ),
		),
	)
);
$fields[] = new Field(
	'500px-key',
	array(
		'type' => 'text',
		'label' => __( 'Customer Key', 'fakerpress' ),
		'description' => __( 'Application Customer Key — <a href="https://500px.com/settings/applications" target="_blank">500px Applications</a>', 'fakerpress' ),
		'attributes' => array(
			'placeholder' => __( 'E.g.: fU3TlASxi2uL76TcP5PAd946fYGZTVsfle6v13No', 'fakerpress' ),
			'value' => Plugin::get( array( '500px', 'key' ) ),
		),
		'actions' => array(
			'save_500px' => __( 'Save', 'fakerpress' ),
		),
	)
);

?>
<div class='wrap __fakerpress'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) { $field->output( true ); } ?>
			</tbody>
		</table>
	</form>
</div>