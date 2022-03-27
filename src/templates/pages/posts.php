<?php
namespace FakerPress;
use Carbon\Carbon;
use FakerPress\Admin\View\Abstract_View;
use FakerPress\Provider\HTML;

/**
 * @var Abstract_View $this The instance of the Template that we are using to build this view.
 */

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
		'id' => $post_type->name,
		'text' => $post_type->labels->name,
	];
}

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'post_types',
		'multiple' => true,
		'data-options' => $_json_post_types_output,
		'value' => 'post',
	],
	[
		'label' => __( 'Post Type', 'fakerpress' ),
		'description' => __( 'Sampling group of Post Types', 'fakerpress' ),
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
	'heading',
	[
		'title' => __( 'Post Content', 'fakerpress' ),
	],
	[]
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

$fields[] = new Field(
	'range',
	[
		'id' => 'content_size',
		'min' => 5,
		'max' => 15,
	],
	[
		'label' => __( 'Content Size', 'fakerpress' ),
		'description' => __( 'How many paragraphs we are going to generate of content.', 'fakerpress' ),
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

$fields[] = new Field(
	'dropdown',
	[
		'id' => 'images_origin',
		'multiple' => true,
		'value' => implode( ',', wp_list_pluck( Module\Attachment::get_providers(), 'id' ) ),
		'data-options' => Module\Attachment::get_providers(),
	],
	[
		'label' => __( 'Image Providers', 'fakerpress' ),
		'description' => __( 'Which image services will the generator use?', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'range',
	[
		'id' => 'excerpt_size',
		'min' => 1,
		'max' => 3,
	],
	[
		'label' => __( 'Excerpt Size', 'fakerpress' ),
		'description' => __( 'How many paragraphs we are going to generate of excerpt.', 'fakerpress' ),
	]
);


$fields[] = new Field(
	'taxonomy',
	[
		'id' => 'taxonomy',
	],
	[
		'label' => __( 'Taxonomy Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure the rules for the Taxonomy and Terms selected', 'fakerpress' ),
	]
);

$fields[] = new Field(
	'meta',
	[
		'id' => 'meta',
		'config' => [
			[
				'type'   => 'attachment',
				'name'   => '_thumbnail_id',
				'weight' => 75,
				'store'  => 'id',
			],
		],
	],
	[
		'label' => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated Posts', 'fakerpress' ),
	]
);


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
