<?php
namespace FakerPress;
use Carbon\Carbon;
use FakerPress\Provider\HTML;

// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}

$fields[] = new Field(
	'range',
	[
		'id' => 'qty',
	],
	[
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many posts should be generated, use both fields to get a randomized number of posts within the given range.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'interval',
	[
		'id' => 'interval_date',
		'value' => 'yesterday',
	],
	[
		'label' => __( 'Date', 'fakerpress' ),
		'description' => __( 'Choose the range for the posts dates.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'post_parent',
		'multiple' => true,
		'data-source' => 'WP_Query',
	],
	[
		'label' => __( 'Parents', 'fakerpress' ),
		'description' => __( 'What posts can be choosen as Parent to the ones created', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'comment_status',
		'multiple' => true,
		'value' => 'open',
		'data-options' => [
			[
				'id' => 'open',
				'text' => esc_attr__( 'Allow Comments', 'fakerpress' ),
			],
			[
				'id' => 'closed',
				'text' => esc_attr__( 'Comments closed', 'fakerpress' ),
			],
		],
	],
	[
		'label' => __( 'Comments Status', 'fakerpress' ),
		'description' => __( 'Sampling group of options for the comment status of the posts', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'checkbox',
	[
		'id' => 'use_html',
		'options' => [
			[
				'text' => __( 'Use HTML on your randomized post content?', 'fakerpress' ),
				'value' => 1,
			],
		],
		'value' => 1,
	],
	[
		'label' => __( 'Use HTML', 'fakerpress' ),
	]
);

$_elements = array_merge( HTML::$sets['header'], HTML::$sets['list'], HTML::$sets['block'], HTML::$sets['self_close'] );
$fields[] = new Field(
	'dropdown',
	[
		'id' => 'html_tags',
		'multiple' => true,
		'data-tags' => true,
		'data-options' => $_elements,
		'value' => implode( ',', $_elements ),
	],
	[
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the Post Content', 'fakerpress' ),
	]
);

/*
This comes back as a Meta Field Template
$fields[] = new Field(
	'number',
	[
		'id' => 'featured_image_rate',
		'placeholder' => __( 'e.g.: 75', 'fakerpress' ),
		'min' => 0,
		'max' => 100,
		'value' => 75,
	],
	[
		'label' => __( 'Featured Image Rate', 'fakerpress' ),
		'description' => __( 'Percentage of the posts created that will have an Featured Image', 'fakerpress' ),
	]
);

$_image_providers[] = [
	'id' => 'placeholdit',
	'text' => 'Placehold.it',
];

$_image_providers[] = [
	'id' => 'lorempicsum',
	'text' => 'Lorem Picsum',
];

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'images_origin',
		'multiple' => true,
		'value' => implode( ',', wp_list_pluck( $_image_providers, 'id' ) ),
		'data-options' => $_image_providers,
	],
	[
		'label' => __( 'Image Providers', 'fakerpress' ),
		'description' => __( 'Which image services will the generator use?', 'fakerpress' ),
	]
);
*/

// Mount the options for Users
$users = get_users(
	[
		'blog_id' => $GLOBALS['blog_id'],
		'count_total' => false,
		'fields' => [ 'ID', 'display_name' ], // When you pass only one field it returns an array of the values
	]
);

$_json_users_output = [];
foreach ( $users as $user ) {
	$_json_users_output[] = [
		'id' => $user->ID,
		'text' => esc_attr( $user->display_name ),
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'author',
		'multiple' => true,
		'data-options' => $_json_users_output,
	],
	[
		'label' => __( 'Author', 'fakerpress' ),
		'description' => __( 'Choose some users to be authors of posts generated.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'meta',
	[
		'id' => 'meta',
		'duplicate' => true,
	],
	[
		'label' => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated Posts', 'fakerpress' ),
	]
);


?>
<div class='wrap'>
	<h2><?php echo esc_attr( $view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $view->slug . ( isset( $view->action ) ? '.' . $view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) { $field->output( true ); } ?>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>