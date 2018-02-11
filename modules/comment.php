<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
use FakerPress\Plugin;
use FakerPress\Utils;

class Comment extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_Comment';

	public function init() {
		$this->page = (object) array(
			'menu' => esc_attr__( 'Comments', 'fakerpress' ),
			'title' => esc_attr__( 'Generate Comments', 'fakerpress' ),
			'view' => 'comments',
		);

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 3 );
	}

	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_comment_link( $id ) ) . '">' . absint( $id ) . '</a>';
	}

	/**
	 * Fetches all the FakerPress related comments
	 * @return array IDs of the comments
	 */
	public static function fetch() {
		$comments = array();

		$query_comments = new \WP_Comment_Query;
		$query_comments = $query_comments->query(
			array(
				'meta_query' => array(
					array(
						'key' => self::$flag,
						'value' => true,
						'type' => 'BINARY',
					),
				),
			)
		);

		foreach ( $query_comments as $comment ) {
			$comments[] = absint( $comment->comment_ID );
		}

		return $comments;
	}

	/**
	 * Use this method to prevent excluding something that was not configured by FakerPress
	 *
	 * @param  array|int|\WP_Comment $comment The ID for the Post or the Object
	 * @return bool
	 */
	public static function delete( $comment ) {
		if ( is_array( $comment ) ) {
			$deleted = array();

			foreach ( $comment as $id ) {
				$id = $id instanceof \WP_Comment ? $id->comment_ID : $id;

				if ( ! is_numeric( $id ) ) {
					continue;
				}

				$deleted[ $id ] = self::delete( $id );
			}

			return $deleted;
		}

		if ( is_numeric( $comment ) ) {
			$comment = \WP_Comment::get_instance( $comment );
		}

		if ( ! $comment instanceof \WP_Comment ) {
			return false;
		}

		$flag = (bool) get_comment_meta( $comment->comment_ID, self::$flag, true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_comment( $comment->comment_ID, true );
	}

	public function do_save( $return_val, $data, $module ) {
		$comment_id = wp_insert_comment( $data );

		if ( ! is_numeric( $comment_id ) ) {
			return false;
		}

		// Flag the Object as FakerPress
		update_post_meta( $comment_id, self::$flag, 1 );

		return $comment_id;
	}

	public function parse_request( $qty, $request = array() ) {
		if ( is_null( $qty ) ) {
			$qty = Utils::instance()->get_qty_from_range( Variable::super( INPUT_POST, array( Plugin::$slug, 'qty' ), FILTER_UNSAFE_RAW ) );
		}

		if ( 0 === $qty ) {
			return esc_attr__( 'Zero is not a good number of comments to fake...', 'fakerpress' );
		}

		$comment_content_size = Variable::super( $request, array( 'content_size' ), FILTER_UNSAFE_RAW, array( 1, 5 ) );
		$comment_content_use_html = Utils::instance()->is_truthy( Variable::super( $request, array( 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) );
		$comment_content_html_tags = array_map( 'trim', explode( ',', Variable::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );
		$comment_type = array_map( 'trim', explode( ',', Variable::super( $request, array( 'type' ), FILTER_SANITIZE_STRING ) ) );
		$post_types = array_map( 'trim', explode( ',', Variable::super( $request, array( 'post_types' ), FILTER_SANITIZE_STRING ) ) );

		$min_date = Variable::super( $request, array( 'interval_date', 'min' ) );
		$max_date = Variable::super( $request, array( 'interval_date', 'max' ) );
		$metas = Variable::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'comment_date', $min_date, $max_date );
			$this->set(
				'comment_content',
				$comment_content_use_html,
				array(
					'qty' => $comment_content_size,
					'elements' => $comment_content_html_tags,
				)
			);
			$this->set( 'user_id', 0 );
			$this->set( 'comment_type', $comment_type );

			$this->set( 'comment_author' );
			$this->set( 'comment_parent' );
			$this->set( 'comment_author_IP' );
			$this->set( 'comment_agent' );
			$this->set( 'comment_approved' );
			$this->set( 'comment_post_ID', null, array( 'post_type' => $post_types ) );
			$this->set( 'comment_author_email' );
			$this->set( 'comment_author_url' );

			$comment_id = $this->generate()->save();

			if ( $comment_id && is_numeric( $comment_id ) ) {
				foreach ( $metas as $meta_index => $meta ) {
					Meta::instance()->object( $comment_id, 'comment' )->generate( $meta['type'], $meta['name'], $meta )->save();
				}
			}
			$results[] = $comment_id;
		}
		$results = array_filter( $results, 'absint' );
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
					_n( 'comment', 'comments', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( array( $this, 'format_link' ), $results ) )
				)
			);
		}
	}
}
