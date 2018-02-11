<?php
namespace FakerPress;
use Carbon\Carbon;

$fields[] = new Field(
	'range',
	array(
		'id' => 'qty',
	),
	array(
		'label' => __( 'Quantity', 'fakerpress' ),
		'description' => __( 'How many posts should be generated, use both fields to get a randomized number of posts within the given range.', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'interval',
	array(
		'id' => 'interval_date',
		'value' => 'yesterday',
	),
	array(
		'label' => __( 'Date', 'fakerpress' ),
		'description' => __( 'Choose the range for the posts dates.', 'fakerpress' ),
	)
);

// Mount the options for post_types
$post_types = get_post_types( array( 'public' => true ), 'object' );

// Exclude Attachments as we don't support images yet
if ( isset( $post_types['attachment'] ) ) {
	unset( $post_types['attachment'] );
}

$_json_post_types_output = array();
foreach ( $post_types as $key => $post_type ) {
	$_json_post_types_output[] = array(
		'hierarchical' => $post_type->hierarchical,
		'id' => $post_type->name,
		'text' => $post_type->labels->name,
	);
}

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'post_types',
		'multiple' => true,
		'data-options' => $_json_post_types_output,
		'value' => 'post',
	),
	array(
		'label' => __( 'Post Type', 'fakerpress' ),
		'description' => __( 'Sampling group of Post Types', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'post_parent',
		'multiple' => true,
		'data-source' => 'WP_Query',
	),
	array(
		'label' => __( 'Parents', 'fakerpress' ),
		'description' => __( 'What posts can be choosen as Parent to the ones created', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'comment_status',
		'multiple' => true,
		'value' => 'open',
		'data-options' => array(
			array(
				'id' => 'open',
				'text' => esc_attr__( 'Allow Comments', 'fakerpress' ),
			),
			array(
				'id' => 'closed',
				'text' => esc_attr__( 'Comments closed', 'fakerpress' ),
			),
		),
	),
	array(
		'label' => __( 'Comments Status', 'fakerpress' ),
		'description' => __( 'Sampling group of options for the comment status of the posts', 'fakerpress' ),
	)
);


// Mount the options for Users
$users = get_users(
	array(
		'blog_id' => $GLOBALS['blog_id'],
		'count_total' => false,
		'fields' => array( 'ID', 'display_name' ), // When you pass only one field it returns an array of the values
	)
);

$_json_users_output = array();
foreach ( $users as $user ) {
	$_json_users_output[] = array(
		'id' => $user->ID,
		'text' => esc_attr( $user->display_name ),
	);
}

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'author',
		'multiple' => true,
		'data-options' => $_json_users_output,
	),
	array(
		'label' => __( 'Author', 'fakerpress' ),
		'description' => __( 'Choose some users to be authors of posts generated.', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'heading',
	array(
		'title' => __( 'Post Content', 'fakerpress' ),
	),
	array()
);

$fields[] = new Field(
	'checkbox',
	array(
		'id' => 'use_html',
		'options' => array(
			array(
				'text' => __( 'Use HTML on your randomized post content?', 'fakerpress' ),
				'value' => 1,
			),
		),
		'value' => 1,
	),
	array(
		'label' => __( 'Use HTML', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'range',
	array(
		'id' => 'content_size',
		'min' => 5,
		'max' => 15,
	),
	array(
		'label' => __( 'Content Size', 'fakerpress' ),
		'description' => __( 'How many paragraphs we are going to generate of content.', 'fakerpress' ),
	)
);

$_elements = array_merge( \Faker\Provider\HTML::$sets['header'], \Faker\Provider\HTML::$sets['list'], \Faker\Provider\HTML::$sets['block'], \Faker\Provider\HTML::$sets['self_close'] );
$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'html_tags',
		'multiple' => true,
		'data-tags' => true,
		'data-options' => $_elements,
		'value' => implode( ',', $_elements ),
	),
	array(
		'label' => __( 'HTML tags', 'fakerpress' ),
		'description' => __( 'Select the group of tags that can be selected to print on the Post Content', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'dropdown',
	array(
		'id' => 'images_origin',
		'multiple' => true,
		'value' => implode( ',', wp_list_pluck( Module\Attachment::get_providers(), 'id' ) ),
		'data-options' => Module\Attachment::get_providers(),
	),
	array(
		'label' => __( 'Image Providers', 'fakerpress' ),
		'description' => __( 'Which image services will the generator use?', 'fakerpress' ),
	)
);


$fields[] = new Field(
	'taxonomy',
	array(
		'id' => 'taxonomy',
	),
	array(
		'label' => __( 'Taxonomy Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure the rules for the Taxonomy and Terms selected', 'fakerpress' ),
	)
);

$fields[] = new Field(
	'meta',
	array(
		'id' => 'meta',
		'config' => array(
			array(
				'type'   => 'attachment',
				'name'   => '_thumbnail_id',
				'weight' => 75,
				'store'  => 'id',
			),
		),
	),
	array(
		'label' => __( 'Meta Field Rules', 'fakerpress' ),
		'description' => __( 'Use the fields below to configure a set of rules for your generated Posts', 'fakerpress' ),
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