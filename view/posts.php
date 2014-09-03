<?php
namespace FakerPress;

use Carbon\Carbon;

// Mounte the options for Users
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

$_elements = array_merge( \Faker\Provider\HTML::$sets['header'], \Faker\Provider\HTML::$sets['list'], \Faker\Provider\HTML::$sets['block'], \Faker\Provider\HTML::$sets['self_close'] );

// Mount the options for taxonomies
$taxonomies = get_taxonomies( array( 'public' => true ), 'object' );

$_json_taxonomies_output = array();
foreach ( $taxonomies as $key => $taxonomy ) {
	$_json_taxonomies_output[] = array(
		'id' => $taxonomy->name,
		'text' => $taxonomy->labels->name,
	);
}

// Mount the options for post_types
$post_types = get_post_types( array( 'public' => true ), 'object' );

// Exclude Attachments as we don't support images yet
if ( isset( $post_types['attachment'] ) ){
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

// Mount the carbon values for dates
$_json_date_selection_output = Dates::get_intervals();

// Mount the options for the `comment_status`
$_json_comment_status_output = array(
	array(
		'id' => 'open',
		'text' => esc_attr__( 'Allow Comments', 'fakerpress' ),
	),
	array(
		'id' => 'closed',
		'text' => esc_attr__( 'Comments closed', 'fakerpress' ),
	),
);
?>
<div class='wrap'>
	<h2><?php echo esc_attr( Admin::$view->title ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' ) ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<tr class='fk-field-container'>
					<th scope="row"><label for="fakerpress_qty"><?php _e( 'Quantity', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[qty]" class='fakerpress_qty_range'>
							<input style='width: 90px;' class='qty-range-min' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 0', 'fakerpress' ); ?>' value='' name='fakerpress_qty_min' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 90px;' class='qty-range-max' type='number' max='25' min='1' placeholder='<?php esc_attr_e( 'e.g.: 10', 'fakerpress' ); ?>' value='' name='fakerpress_qty_max' disabled/>
						</div>
						<p class="description"><?php _e( 'The range of Posts you want to generate', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container field-container-post_type'>
					<th scope="row"><label for="fakerpress_post_types"><?php _e( 'Post Type', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[post_types]">
							<input type='hidden' class='field-select2-simple field-post_type' name='fakerpress_post_types' data-possibilities='<?php echo json_encode( $_json_post_types_output ); ?>' />
						</div>
						<p class="description"><?php _e( 'Sampling group of Post Types', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container field-container-post_parent'>
					<th scope="row"><label for="fakerpress_post_parents"><?php _e( 'Parents', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[post_parents]">
							<input type='hidden' class='field-select2-posts field-post_parent' name='fakerpress_post_parents' />
						</div>
						<p class="description"><?php _e( 'What posts can be choosen as Parent to the ones created', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container'>
					<th scope="row"><label for="fakerpress_comment_status"><?php _e( 'Comments Status', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[comment_status]">
							<input type='hidden' class='field-select2-simple' name='fakerpress_comment_status' data-possibilities='<?php echo json_encode( $_json_comment_status_output ); ?>' />
						</div>
						<p class="description"><?php _e( 'Sampling group of options for the comment status of the posts', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container field-container-post_content_use_html'>
					<th scope="row"><label for="fakerpress_post_content_use_html"><?php _e( 'Use HTML', 'fakerpress' ); ?></label></th>
					<td>
						<input type='checkbox' style="margin-top: -3px;" name='fakerpress_post_content_use_html' checked />
						<p style='display: inline-block;' class="description"><?php _e( 'Use HTML on your randomized post content?', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container fk-field-dependent' data-fk-depends=".field-container-post_content_use_html input" data-fk-condition='true'>
					<th scope="row"><label for="fakerpress_post_content_html_tags"><?php _e( 'HTML tags', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[post_content_html_tags]">
							<input type='hidden' class='field-select2-tags' name='fakerpress_post_content_html_tags' value='<?php echo implode( ',', $_elements ); ?>' data-tags='<?php echo json_encode( $_elements ); ?>' />
						</div>
						<p class="description"><?php _e( 'Select the group of tags that can be selected to print on the Post Content', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container'>
					<th scope="row"><label for="fakerpress_taxonomies"><?php _e( 'Taxonomies', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[taxonomies]">
							<input type='hidden' class='field-select2-simple' name='fakerpress_taxonomies' data-possibilities='<?php echo json_encode( $_json_taxonomies_output ); ?>' />
						</div>
						<p class="description"><?php _e( 'From which taxonomies the related terms should be selected', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container'>
					<th scope="row"><label for="fakerpress_interval_date"><?php _e( 'Date', 'fakerpress' ); ?></label></th>
					<td>
						<div class='fakerpress-range-group'>
							<select id="fakerpress_interval_date" class='field-date-selection' data-placeholder='<?php esc_attr_e( 'Select an Interval', 'fakerpress' ); ?>' style="margin-right: 5px; margin-top: -4px;">
								<option></option>;
								<?php foreach ($_json_date_selection_output as $option) { ?>
								<option data-min="<?php echo date( 'm/d/Y', strtotime( $option['min'] ) ); ?>" data-max="<?php echo date( 'm/d/Y', strtotime( $option['max'] ) ); ?>" value="<?php echo $option['text']; ?>"><?php echo $option['text']; ?></option>
								<?php } ?>
							</select>

							<input style='width: 150px;' class='field-datepicker field-min-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_min_date' />
							<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>
							<input style='width: 150px;' class='field-datepicker field-max-date' type='text' placeholder='<?php esc_attr_e( 'mm/dd/yyyy', 'fakerpress' ); ?>' value='' name='fakerpress_max_date' />
						</div>
						<p class="description-date description"><?php _e( 'Choose the range for the posts dates.', 'fakerpress' ); ?></p>
					</td>
				</tr>
				<tr class='fk-field-container'>
					<th scope="row"><label for="fakerpress_author"><?php _e( 'Author', 'fakerpress' ); ?></label></th>
					<td>
						<div id="fakerpress[author]">
							<input type='hidden' class='field-select2-simple field-select2-author' name='fakerpress_authors'/>
						</div>
						<p class="description"><?php _e( 'Choose some users to be authors of posts generated.', 'fakerpress' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Generate', 'fakerpress' ), 'primary' ); ?>
	</form>
</div>