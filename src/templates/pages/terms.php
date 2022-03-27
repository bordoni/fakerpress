<?php
namespace FakerPress;

$fields[] = new Field(
	'range',
	'qty',
	[
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many terms should be generated, use both fields to get a randomized number of terms within the given range.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'range',
    [
		'id' => 'size',
		'min' => 2,
		'max' => 5,
	],
	[
		'label' => __( 'Name Size', 'fakerpress' ),
		'description' => __( 'What is the size of the Term name', 'fakerpress' ),
	]
);

$taxonomies = get_taxonomies( [ 'public' => true ], 'object' );

$_json_taxonomies_output = [];
foreach ( $taxonomies as $key => $taxonomy ) {
	$_json_taxonomies_output[] = [
		'id' => $taxonomy->name,
		'text' => $taxonomy->labels->name,
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'taxonomies',
		'multiple' => true,
		'value' => 'category, post_tag',
		'data-options' => $_json_taxonomies_output,
	],
	[
		'label' => __( 'Taxonomies', 'fakerpress' ),
		'description' => __( 'Group of taxonomies that the terms will be created within', 'fakerpress' ),
	]
);

if ( version_compare( $GLOBALS['wp_version'], '4.4-beta', '>=' ) ) {
	$fields[] = new Field(
		'meta',
		[
			'id' => 'meta',
		],
		[
			'label' => __( 'Meta Field Rules', 'fakerpress' ),
			'description' => __( 'Use the fields below to configure a set of rules for your generated Terms', 'fakerpress' ),
		]
	);
}

?>
<div class='wrap'>
	<h2><?php echo esc_attr( $this->get_title() ); ?></h2>
	<form method='post' class='fp-module-generator'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $this::get_slug() ); ?>
		<input type="hidden" name="fakerpress[view]" value="<?php echo esc_attr( $this::get_slug() ); ?>">

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
