<?php
namespace FakerPress;

$fields[] = new Field(
	'range',
	'qty',
	array(
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many users should be generated, use both fields to get a randomized number of users within the given range.', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'checkbox',
	array(
		'id' => 'use_html',
		'options' => array(
			array(
				'text' => __( 'Use HTML on your randomized user description?', 'fakerpress' ),
				'value' => 1,
			),
		),
		'value' => 1,
	),
	array(
		'label' => __( 'Use HTML', 'fakerpress' ),
	)
);

$_elements = array_merge( array( 'h3', 'h4', 'h5', 'h6', 'p' ) );
$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'html_tags',
		'multiple' => true,
		'data-options' => $_elements,
		'data-tags' => true,
		'value' => implode( ',', $_elements ),
	),
	array(
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the User Description.', 'fakerpress' ),
	)
);

$roles = get_editable_roles();

$_json_roles_output = array();
foreach ( $roles as $role_name => $role_data ) {
	$_json_roles_output[] = array(
		'id' => $role_name,
		'text' => esc_attr( $role_data['name'] ),
	);
}

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'roles',
		'multiple' => true,
		'data-options' => $_json_roles_output,
	),
	array(
		'label' => __( 'Roles', 'fakerpress' ),
		'description' => __( 'Sampling roles to be used', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'meta',
	array(
		'id' => 'meta',
	),
	array(
		'label' => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated users', 'fakerpress' ),
	)
);
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