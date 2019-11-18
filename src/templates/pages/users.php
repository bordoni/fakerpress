<?php
namespace FakerPress;

// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}

$fields[] = new Field(
	'range',
	'qty',
	[
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many users should be generated, use both fields to get a randomized number of users within the given range.', 'fakerpress' ),
	]
);

$roles = get_editable_roles();

$_json_roles_output = [];
foreach ( $roles as $role_name => $role_data ) {
	$_json_roles_output[] = [
		'id' => $role_name,
		'text' => esc_attr( $role_data['name'] ),
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'roles',
		'multiple' => true,
		'data-options' => $_json_roles_output,
	],
	[
		'label' => __( 'Roles', 'fakerpress' ),
		'description' => __( 'Sampling roles to be used', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'heading',
	[
		'title' => __( 'User Description', 'fakerpress' ),
	],
	[]
);

$fields[] = new Field(
	'range',
	[
		'id' => 'description_size',
		'min' => 1,
		'max' => 5,
	],
	[
		'label' => __( 'Description Size', 'fakerpress' ),
		'description' => __( 'How many paragraphs we are going to generate of description.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'checkbox',
	[
		'id' => 'use_html',
		'options' => [
			[
				'text' => __( 'Use HTML on your randomized user description?', 'fakerpress' ),
				'value' => 1,
			],
		],
		'value' => 1,
	],
	[
		'label' => __( 'Use HTML', 'fakerpress' ),
	]
);

$_elements = array_merge( [ 'h3', 'h4', 'h5', 'h6', 'p' ] );
$fields[] = new Field(
	'dropdown',
	[
		'id' => 'html_tags',
		'multiple' => true,
		'data-options' => $_elements,
		'data-tags' => true,
		'value' => implode( ',', $_elements ),
	],
	[
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the User Description.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'meta',
	[
		'id' => 'meta',
	],
	[
		'label' => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated users', 'fakerpress' ),
	]
);
?>
<div class='wrap'>
	<h2><?php echo esc_attr( $view->title ); ?></h2>

	<form method='post' class='fp-module-generator'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $view->slug . ( isset( $view->action ) ? '.' . $view->action : '' ) ); ?>
		<input type="hidden" name="fakerpress[view]" value="<?php echo esc_attr( $view->slug ); ?>">

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