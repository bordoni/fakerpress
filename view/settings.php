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

?>
<div class='wrap __fakerpress'>
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