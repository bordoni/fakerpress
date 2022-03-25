<?php
namespace FakerPress;

use FakerPress\Provider\HTML;

global $wpdb;

// Mount the options for post_types
$comment_types = $wpdb->get_col( "SELECT `comment_type` FROM $wpdb->comments GROUP BY `comment_type`" );

// Default is the Empty value
$_json_comment_types_output = [
	[
		'id'   => 'default',
		'text' => esc_attr__( 'default', 'fakerpress' ),
	],
];

foreach ( $comment_types as $comment ) {
	// Skip the Default Option
	if ( empty( $comment ) ) {
		continue;
	}

	$_json_comment_types_output[] = [
		'id'   => $comment,
		'text' => $comment,
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id'           => 'type',
		'multiple'     => true,
		'data-options' => $_json_comment_types_output,
		'data-tags'    => true,
		'value'        => 'default',
	],
	[
		'label'       => __( 'Type', 'fakerpress' ),
		'description' => __( 'Which type of comment are you going to generate?', 'fakerpress' ),
	]
);

// Mount the options for post_types
$post_types = get_post_types( [ 'public' => true ], 'object' );

// Exclude Attachments as we don't support images yet
if ( isset( $post_types['attachment'] ) ) {
	unset( $post_types['attachment'] );
}

$_json_post_types_output = [];
foreach ( $post_types as $key => $post_type ) {
	$_json_post_types_output[] = [
		'hierarchical' => $post_type->hierarchical,
		'id'           => $post_type->name,
		'text'         => $post_type->labels->name,
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id'           => 'post_types',
		'multiple'     => true,
		'data-options' => $_json_post_types_output,
		'value'        => 'post',
	],
	[
		'label'       => __( 'Post Type', 'fakerpress' ),
		'description' => __( 'Group of Post Types that the comment can be generate for', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'range',
	'qty',
	[
		'label'       => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many comments should be generated, use both fields to get a randomized number of comments within the given range.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'interval',
	'interval_date',
	[
		'label'       => __( 'Date', 'fakerpress' ),
		'description' => __( 'Choose the range for the posts dates.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'heading',
	[
		'title' => __( 'Comment Content', 'fakerpress' ),
	],
	[]
);

$fields[] = new Field(
	'range',
	[
		'id'  => 'content_size',
		'min' => 1,
		'max' => 5,
	],
	[
		'label'       => __( 'Content Size', 'fakerpress' ),
		'description' => __( 'How many paragraphs we are going to generate of content.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'checkbox',
	[
		'id'      => 'use_html',
		'value'   => 1,
		'options' => [
			[
				'text'  => __( 'Use HTML on your randomized comment content?', 'fakerpress' ),
				'value' => 1,
			],
		],
	],
	[
		'label' => __( 'Use HTML', 'fakerpress' ),
	]
);

$_elements = array_merge( HTML::$sets['header'], HTML::$sets['list'], HTML::$sets['block'] );
$fields[]  = new Field(
	'dropdown',
	[
		'id'           => 'html_tags',
		'multiple'     => true,
		'data-options' => $_elements,
		'data-tags'    => true,
		'value'        => implode( ',', $_elements ),
	],
	[
		'label'       => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the Comment Content.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'meta',
	[
		'id' => 'meta',
	],
	[
		'label'       => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated Comments', 'fakerpress' ),
	]
);
/*
<tr class='fk-field-container fk-field-dependent' data-fk-depends=".field-container-comment_content_use_html input" data-fk-condition='true'>
 */
?>

<div class='wrap'>
	<h2><?php echo esc_attr( $this->get_title() ); ?></h2>

	<form method='post' class='fp-module-generator'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $this::get_slug() ); ?>
		<input type="hidden" name="fakerpress[view]" value="<?php echo esc_attr( $this::get_slug() ); ?>">

		<table class="form-table" style="display: table;">
			<tbody>
			<?php foreach ( $fields as $field ) {
				$field->output( true );
			} ?>
			</tbody>
		</table>
		<div class="fp-submit">
			<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary', null, false ); ?>
			<span class="spinner"></span>
			<div class="fp-response"></div>
		</div>
	</form>
</div>
