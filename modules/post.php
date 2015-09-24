<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
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

	public function do_save( $return_val, $data, $module ) {
		$post_id = wp_insert_post( $data );

		if ( ! is_numeric( $post_id ) ){
			return false;
		}

		// Flag the Object as FakerPress
		update_post_meta( $post_id, self::$flag, 1 );

		return $post_id;
	}

	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . absint( $id ) . '</a>';
	}

	public function parse_request( $qty, $request = array() ) {
		if ( is_null( $qty ) ) {
			$qty = Variable::super( INPUT_POST, array( Plugin::$slug, 'qty' ), FILTER_UNSAFE_RAW );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->faker->numberBetween( $min, $max );
		}

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of posts to fake...', 'fakerpress' );
		}

		$comment_status = array_map( 'trim', explode( ',', Variable::super( $request, array( 'comment_status' ), FILTER_SANITIZE_STRING ) ) );

		$post_author = array_intersect( get_users( array( 'fields' => 'ID' ) ), array_map( 'trim', explode( ',', Variable::super( $request, array( 'author' ) ) ) ) );

		$min_date = Variable::super( $request, array( 'interval_date', 'min' ) );
		$max_date = Variable::super( $request, array( 'interval_date', 'max' ) );

		$post_types = array_intersect( get_post_types( array( 'public' => true ) ), array_map( 'trim', explode( ',', Variable::super( $request, array( 'post_types' ), FILTER_SANITIZE_STRING ) ) ) );
		$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Variable::super( $request, array( 'taxonomies' ), FILTER_SANITIZE_STRING ) ) ) );

		$post_content_use_html = Variable::super( $request, array( 'use_html' ), FILTER_SANITIZE_NUMBER_INT, 0 ) === 1;
		$post_content_html_tags = array_map( 'trim', explode( ',', Variable::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$post_parents = array_map( 'trim', explode( ',', Variable::super( $request, array( 'post_parent' ), FILTER_SANITIZE_STRING ) ) );

		$featured_image_rate = absint( Variable::super( $request, array( 'featured_image_rate' ), FILTER_SANITIZE_NUMBER_INT ) );
		$images_origin = array_map( 'trim', explode( ',', Variable::super( $request, array( 'images_origin' ), FILTER_SANITIZE_STRING ) ) );

		$metas = Variable::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'post_title' );
			$this->set( 'post_status', 'publish' );
			$this->set( 'post_date', array( $min_date, $max_date ) );
			$this->set( 'post_parent', $post_parents );
			$this->set( 'post_content', $post_content_use_html, array( 'elements' => $post_content_html_tags ) );
			$this->set( 'post_author', $post_author );
			$this->set( 'post_type', $post_types );
			$this->set( 'comment_status', $comment_status );
			$this->set( 'ping_status' );
			$this->set( 'tax_input', $taxonomies );

			$post_id = $this->generate()->save();

			if ( $post_id && is_numeric( $post_id ) ){
				foreach ( $metas as $meta_index => $meta ) {
					Meta::instance()->object( $post_id )->generate( $meta['type'], $meta['name'], $meta )->save();
				}

				if ( $this->faker->numberBetween( 0, 100 ) <= $featured_image_rate ){
					// Generate the Attachment
					$attachment_id = Attachment::instance()
						->set( 'attachment_url', $this->faker->randomElement( $images_origin ) )
						->set( 'post_parent', $post_id, 1 )
						->generate()->save();

					Meta::instance()->object( $post_id )->generate( 'raw', '_thumbnail_id', array( 100, $attachment_id, 0 ) )->save();
				}
			}

			$results[] = $post_id;
		}

		$results = array_filter( (array) $results, 'absint' );

		return $results;
	}

	public function _action_parse_request( $view ) {
		if ( 'post' !== Admin::$request_method || empty( $_POST ) ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}

		// After this point we are safe to say that we have a good POST request
		$results = $this->parse_request( null, Variable::super( INPUT_POST, array( Plugin::$slug ), FILTER_UNSAFE_RAW ) );

		if ( ! empty( $results ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'post', 'posts', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( array( $this, 'format_link' ), $results ) )
				)
			);
		}
	}
}
