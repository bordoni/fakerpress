<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Filter;
use FakerPress\Plugin;


class Post extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_Post';

	public function init() {
		$this->page = (object) array(
			'menu' => esc_attr__( 'Posts', 'fakerpress' ),
			'title' => esc_attr__( 'Generate Posts', 'fakerpress' ),
			'view' => 'posts',
		);

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$post_id = wp_insert_post( $params );

		if ( ! is_numeric( $post_id ) ){
			return false;
		}

		foreach ( $metas as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return $post_id;
	}

	public function _action_parse_request( $view ){
		if ( 'post' !== Admin::$request_method || empty( $_POST ) ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}

		// After this point we are safe to say that we have a good POST request
		$qty_min = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'min' ), FILTER_SANITIZE_NUMBER_INT ) );
		$qty_max = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'max' ), FILTER_SANITIZE_NUMBER_INT ) );

		$comment_status = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'comment_status' ), FILTER_SANITIZE_STRING ) ) );

		$post_author = array_intersect( get_users( array( 'fields' => 'ID' ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'author' ) ) ) ) );

		$min_date = Filter::super( INPUT_POST, array( 'fakerpress', 'interval_date', 'min' ) );
		$max_date = Filter::super( INPUT_POST, array( 'fakerpress', 'interval_date', 'max' ) );

		$post_types = array_intersect( get_post_types( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'post_types' ), FILTER_SANITIZE_STRING ) ) ) );
		$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'taxonomies' ), FILTER_SANITIZE_STRING ) ) ) );

		$post_content_use_html = Filter::super( INPUT_POST, array( 'fakerpress', 'use_html' ), FILTER_SANITIZE_NUMBER_INT, 0 ) === 1;
		$post_content_html_tags = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$post_parents = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'post_parent' ), FILTER_SANITIZE_STRING ) ) );

		$featured_image_rate = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'featured_image_rate' ), FILTER_SANITIZE_NUMBER_INT ) );
		$images_origin = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'images_origin' ), FILTER_SANITIZE_STRING ) ) );

		$metas = Filter::super( INPUT_POST, array( 'fakerpress', 'meta' ), FILTER_UNSAFE_RAW );

		$attach_module = Attachment::instance();
		$meta_module = Meta::instance();

		if ( 0 === $qty_min ){
			return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'posts' ), 'error' );
		}

		if ( ! empty( $qty_min ) && ! empty( $qty_max ) ){
			$quantity = $this->faker->numberBetween( $qty_min, $qty_max );
		}

		if ( ! empty( $qty_min ) && empty( $qty_max ) ){
			$quantity = $qty_min;
		}

		$results = (object) array();

		for ( $i = 0; $i < $quantity; $i++ ) {
			if ( $this->faker->numberBetween( 0, 100 ) <= $featured_image_rate ){
				$attach_module->param( 'attachment_url', $this->faker->randomElement( $images_origin ) );
				$attach_module->generate();
				$attachment_id = $attach_module->save();
				$this->meta( '_thumbnail_id', null, $attachment_id );
			}

			$this->param( 'tax_input', $taxonomies );
			$this->param( 'post_status', 'publish' );
			$this->param( 'post_date', array( $min_date, $max_date ) );
			$this->param( 'post_parent', $post_parents );
			$this->param( 'post_content', $post_content_use_html, array( 'elements' => $post_content_html_tags ) );
			$this->param( 'post_author', $post_author );
			$this->param( 'post_type', $post_types );
			$this->param( 'comment_status', $comment_status );

			$this->generate();
			$post_id = $this->save();

			if ( $post_id && is_numeric( $post_id ) ){
				foreach ( $metas as $meta_index => $meta ) {
					$meta_module->object( $post_id )->build( $meta['type'], $meta['name'], $meta )->save();
				}
			}

			$results->all[] = $post_id;
		}

		$results->success = array_filter( $results->all, 'absint' );

		if ( ! empty( $results->success ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results->success ),
					_n( 'post', 'posts', count( $results->success ), 'fakerpress' ),
					implode(
						', ',
						array_map(
							function ( $id ){
								return '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . absint( $id ) . '</a>';
							},
							$results->success
						)
					)
				)
			);
		}
	}
}
