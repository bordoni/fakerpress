<?php
namespace FakerPress;
$fields[] = new Field(
	'text',
	array(
		'id' => 'erase_phrase',
		'placeholder' => 'The cold never bothered me anyway!',
	),
	array(
		'label' => __( 'Erase faked data', 'fakerpress' ),
		'description' => __( 'To erase all data generated type "<b>Let it Go!</b>".', 'fakerpress' ),
		'actions' => array(
			'delete' => __( 'Delete!', 'fakerpress' ),
		),
	)
);

$fields[] = new Field(
	'heading',
	array(
		'id' => 'heading-500px',
		'title' => __( 'API: <i>500px</i>', 'fakerpress' ),
		'description' => __( 'Setting up 500px API connection is fully optional.', 'fakerpress' )
	)
);

$fields[] = new Field(
	'text',
	array(
		'id' => '500px-key',
		'placeholder' => __( 'E.g.: fU3TlASxi2uL76TcP5PAd946fYGZTVsfle6v13No', 'fakerpress' ),
		'value' => Plugin::get( array( '500px', 'key' ) ),
	),
	array(
		'label' => __( 'Consumer Key', 'fakerpress' ),
		'description' => __( 'Application Consumer Key — <a href="https://500px.com/settings/applications" target="_blank">500px Applications</a>', 'fakerpress' ),
		'actions' => array(
			'save_500px' => __( 'Save', 'fakerpress' ),
		),
	)
);

?>
<div class='wrap'>
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