<?php
namespace FakerPress;

$fields[] = new Field(
	'range',
	'qty',
	array(
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many terms should be generated, use both fields to get a randomized number of terms within the given range.', 'fakerpress' ),
	)
);

$taxonomies = get_taxonomies( array( 'public' => true ), 'object' );

$_json_taxonomies_output = array();
foreach ( $taxonomies as $key => $taxonomy ) {
	$_json_taxonomies_output[] = array(
		'id' => $taxonomy->name,
		'text' => $taxonomy->labels->name,
	);
}

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'taxonomies',
		'multiple' => true,
		'data-options' => $_json_taxonomies_output,
	),
	array(
		'label' => __( 'Taxonomies', 'fakerpress' ),
		'description' => __( 'Group of taxonomies that the terms will be created within', 'fakerpress' ),
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
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>