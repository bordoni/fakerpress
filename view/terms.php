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

$fields[] = new Field(
	'range',
	'size',
	array(
		'label' => __( 'Name Size', 'fakerpress' ),
		'description' => __( 'What is the size of the Term name', 'fakerpress' ),
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

if ( version_compare( $GLOBALS['wp_version'], '4.4-beta', '>=' ) ) {
	$fields[] = new Field(
		'meta',
		array(
			'id' => 'meta',
		),
		array(
			'label' => __( 'Meta Field Rules', 'fakerpress' ),
			'description' => __( 'Use the fields below to configure a set of rules for your generated Terms', 'fakerpress' ),
		)
	);
}

?>
<div class='wrap'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>
	<form method='post' class='fp-module-generator'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<input type="hidden" name="fakerpress[view]" value="<?php echo esc_attr( Admin::$view->slug ); ?>">

		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) { $field->output( true ); } ?>
			</tbody>
		</table>
		<div class="fp-submit">
			<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary', null, false ); ?>
			<span class="spinner"></span>
			<div class="fp-response"></div>
		</div>
	</form>
</div>