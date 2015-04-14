<?php
namespace FakerPress;

$fields[] = new Field(
	'qty',
	array(
		'type' => 'range',
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many users should be generated, use both fields to get a randomized number of users within the given range.', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'use_html',
	array(
		'type' => 'boolean',
		'label' => __( 'Use HTML', 'fakerpress' ),
		'info' => __( 'Use HTML on your randomized user description?', 'fakerpress' ),
		'value' => 1,
	)
);

$_elements = array_merge( array( 'h3', 'h4', 'h5', 'h6', 'p' ) );
$fields[] = new Field(
	'html_tags',
	array(
		'type' => 'dropdown',
		'multiple' => true,
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the User Description.', 'fakerpress' ),
		'attributes' => array(
			'class' => 'field-select2-tags',
			'data-tags' => $_elements,
		),
		'value' => implode( ',', $_elements ),
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
	'roles',
	array(
		'type' => 'dropdown',
		'multiple' => true,
		'label' => __( 'Roles', 'fakerpress' ),
		'description' => __( 'Sampling roles to be used', 'fakerpress' ),
		'attributes' => array(
			'data-possibilities' => $_json_roles_output,
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
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>