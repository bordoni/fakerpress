<?php
namespace FakerPress;
use FakerPress\Module\Attachment;

$fields[] = new Field(
	'range',
	[
		'id' => 'qty',
	],
	[
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many attachments should be generated, use both fields to get a randomized number of attachments within the given range.', 'fakerpress' ),
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

// Image Provider Selection
$_image_providers = Attachment::get_providers();

// Ensure we have the proper format for the dropdown
if ( empty( $_image_providers ) ) {
	$_image_providers = [
		[
			'id'   => 'placeholder',
			'text' => esc_attr__( 'Placehold.co', 'fakerpress' ),
		],
		[
			'id'   => 'lorempicsum',
			'text' => esc_attr__( 'Lorem Picsum', 'fakerpress' ),
		],
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'provider',
		'value' => 'placeholder',
		'options' => $_image_providers,
	],
	[
		'label' => __( 'Image Provider', 'fakerpress' ),
		'description' => __( 'Choose which image service to use for generating attachments.', 'fakerpress' ),
	]
);

// Image Dimensions
$fields[] = new Field(
	'range',
	[
		'id' => 'width',
		'min' => 50,
		'max' => 3000,
		'value' => [ 200, 1200 ],
	],
	[
		'label' => __( 'Width', 'fakerpress' ),
		'description' => __( 'Image width range in pixels.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'range',
	[
		'id' => 'height',
		'min' => 50,
		'max' => 3000,
		'value' => [ 0, 0 ],
	],
	[
		'label' => __( 'Height', 'fakerpress' ),
		'description' => __( 'Image height range in pixels. Leave at 0 to use aspect ratio instead.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'number',
	[
		'id' => 'aspect_ratio',
		'value' => 1.5,
		'min' => 0.1,
		'max' => 10,
		'step' => 0.1,
	],
	[
		'label' => __( 'Aspect Ratio', 'fakerpress' ),
		'description' => __( 'Width/Height ratio (e.g., 1.5 for 3:2, 1.77 for 16:9). Only used when height is 0.', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'post_parent',
		'multiple' => true,
		'data-source' => 'WP_Query',
		'data-nonce' => wp_create_nonce( Plugin::$slug . '-select2-WP_Query' ),
	],
	[
		'label' => __( 'Parent Posts', 'fakerpress' ),
		'description' => __( 'Attach generated images to specific posts.', 'fakerpress' ),
	]
);

// Content Generation Options
$fields[] = new Field(
	'checkbox',
	[
		'id' => 'generate_alt_text',
		'options' => [
			[
				'text' => __( 'Generate alt text for accessibility', 'fakerpress' ),
				'value' => 1,
			],
		],
		'value' => 1,
	],
	[
		'label' => __( 'Alt Text', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'checkbox',
	[
		'id' => 'generate_caption',
		'options' => [
			[
				'text' => __( 'Generate image captions', 'fakerpress' ),
				'value' => 1,
			],
		],
		'value' => 1,
	],
	[
		'label' => __( 'Caption', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'checkbox',
	[
		'id' => 'generate_description',
		'options' => [
			[
				'text' => __( 'Generate image descriptions', 'fakerpress' ),
				'value' => 1,
			],
		],
		'value' => 1,
	],
	[
		'label' => __( 'Description', 'fakerpress' ),
	]
);


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
		'description' => __( 'Choose users to be owners of generated attachments.', 'fakerpress' ),
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
		'description' => __( 'Use the fields below to configure a set of rules for your generated Attachments', 'fakerpress' ),
	]
);

?>
<div class='wrap'>
	<h2><?php echo esc_attr( $this->get_title() ); ?></h2>

	<form method='post' class='fp-module-generator' data-endpoint='attachments'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $this::get_slug() ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) { $field->output( true ); } ?>

			</tbody>
		</table>
		<div class="fp-submit">
			<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
			<span class="spinner"></span>
			<div class="fp-response"></div>
		</div>
	</form>
</div>
