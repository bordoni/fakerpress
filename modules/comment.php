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

	public function do_save( $return_val, $data, $module ) {
		$comment_id = wp_insert_comment( $data );

		if ( ! is_numeric( $comment_id ) ){
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

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of comments to fake...', 'fakerpress' );
		}

		$comment_content_use_html = Variable::super( $request, array( 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$comment_content_html_tags = array_map( 'trim', explode( ',', Variable::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$min_date = Variable::super( $request, array( 'interval_date', 'min' ) );
		$max_date = Variable::super( $request, array( 'interval_date', 'max' ) );
		$metas = Variable::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'comment_date', $min_date, $max_date );
			$this->set( 'comment_content', $comment_content_use_html, array( 'elements' => $comment_content_html_tags ) );
			$this->set( 'user_id', 0 );

			$this->set( 'comment_author' );
			$this->set( 'comment_parent' );
			$this->set( 'comment_author_IP' );
			$this->set( 'comment_agent' );
			$this->set( 'comment_approved' );
			$this->set( 'comment_post_ID' );
			$this->set( 'comment_author_email' );
			$this->set( 'comment_author_url' );

			$comment_id = $this->generate()->save();

			if ( $comment_id && is_numeric( $comment_id ) ){
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
