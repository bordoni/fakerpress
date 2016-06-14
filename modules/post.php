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

	/**
	 * Fetches all the FakerPress related Posts
	 * @return array IDs of the Posts
	 */
	public static function fetch( $overwrite = array() ) {
		$defaults = array(
			'post_type' => 'any',
			'post_status' => 'any',
			'nopaging' => true,
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => self::$flag,
					'value' => true,
					'type' => 'BINARY',
				),
			),
		);

		$args = wp_parse_args( $overwrite, $defaults );
		$query_posts = new \WP_Query( $args );

		return array_map( 'absint', $query_posts->posts );
	}

	/**
	 * Use this method to prevent excluding something that was not configured by FakerPress
	 *
	 * @param  array|int|\WP_Post $post The ID for the Post or the Object
	 * @return bool
	 */
	public static function delete( $post ) {
		if ( is_array( $post ) ) {
			$deleted = array();

			foreach ( $post as $id ) {
				$id = $id instanceof \WP_Post ? $id->ID : $id;

				if ( ! is_numeric( $id ) ) {
					continue;
				}

				$deleted[ $id ] = self::delete( $id );
			}

			return $deleted;
		}

		if ( is_numeric( $post ) ) {
			$post = \WP_Post::get_instance( $post );
		}

		if ( ! $post instanceof \WP_Post ) {
			return false;
		}

		$flag = (bool) get_post_meta( $post->ID, self::$flag, true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_post( $post->ID, true );
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

		// Fetch Comment Status
		$comment_status = Variable::super( $request, array( 'comment_status' ), FILTER_SANITIZE_STRING );
		$comment_status = array_map( 'trim', explode( ',', $comment_status ) );

		// Fetch Post Author
		$post_author = Variable::super( $request, array( 'author' ), FILTER_SANITIZE_STRING );
		$post_author = array_map( 'trim', explode( ',', $post_author ) );
		$post_author = array_intersect( get_users( array( 'fields' => 'ID' ) ), $post_author );

		// Fetch the dates
		$date = array(
			Variable::super( $request, array( 'interval_date', 'min' ), FILTER_SANITIZE_STRING ),
			Variable::super( $request, array( 'interval_date', 'max' ), FILTER_SANITIZE_STRING ),
		);

		// Fetch Post Types
		$post_types = Variable::super( $request, array( 'post_types' ), FILTER_SANITIZE_STRING );
		$post_types = array_map( 'trim', explode( ',', $post_types ) );
		$post_types = array_intersect( get_post_types( array( 'public' => true ) ), $post_types );

		// Fetch Post Content
		$post_content_use_html = Variable::super( $request, array( 'use_html' ), FILTER_SANITIZE_NUMBER_INT, 0 ) === 1;
		$post_content_html_tags = array_map( 'trim', explode( ',', Variable::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		// Fetch and clean Post Parents
		$post_parents = Variable::super( $request, array( 'post_parent' ), FILTER_SANITIZE_STRING );
		$post_parents = array_map( 'trim', explode( ',', $post_parents ) );

		$featured_image_rate = absint( Variable::super( $request, array( 'featured_image_rate' ), FILTER_SANITIZE_NUMBER_INT ) );
		$images_origin = array_map( 'trim', explode( ',', Variable::super( $request, array( 'images_origin' ), FILTER_SANITIZE_STRING ) ) );

		// Fetch Taxonomies
		$taxonomies_configuration = Variable::super( $request, array( 'taxonomy' ), FILTER_UNSAFE_RAW );

		// Fetch Metas It will be parsed later!
		$metas = Variable::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'post_title' );
			$this->set( 'post_status', 'publish' );
			$this->set( 'post_date', $date );
			$this->set( 'post_parent', $post_parents );
			$this->set( 'post_content', $post_content_use_html, array( 'elements' => $post_content_html_tags, 'sources' => $images_origin ) );
			$this->set( 'post_author', $post_author );
			$this->set( 'post_type', $post_types );
			$this->set( 'comment_status', $comment_status );
			$this->set( 'ping_status' );
			$this->set( 'tax_input', $taxonomies_configuration );

			$generated = $this->generate();
			$post_id = $generated->save();

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
