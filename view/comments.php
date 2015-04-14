<?php
namespace FakerPress;

$fields[] = new Field(
	'qty',
	array(
		'type' => 'range',
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many comments should be generated, use both fields to get a randomized number of comments within the given range.', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'use_html',
	array(
		'type' => 'boolean',
		'label' => __( 'Use HTML', 'fakerpress' ),
		'info' => __( 'Use HTML on your randomized comment content?', 'fakerpress' ),
		'value' => 1,
	)
);

$_elements = array_merge( \Faker\Provider\HTML::$sets['header'], \Faker\Provider\HTML::$sets['list'], \Faker\Provider\HTML::$sets['block'] );
$fields[] = new Field(
	'html_tags',
	array(
		'type' => 'dropdown',
		'multiple' => true,
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the Comment Content.', 'fakerpress' ),
		'attributes' => array(
			'class' => 'field-select2-tags',
			'data-tags' => $_elements,
		),
		'value' => implode( ',', $_elements ),
	)
);

$fields[] = new Field(
	'interval_date',
	array(
		'type' => 'interval',
		'label' => __( 'Date', 'fakerpress' ),
		'description' => __( 'Choose the range for the posts dates.', 'fakerpress' ),
	)
);

/*
<tr class='fk-field-container fk-field-dependent' data-fk-depends=".field-container-comment_content_use_html input" data-fk-condition='true'>
 */
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