<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Filter;
use FakerPress\Plugin;


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

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_comment_link( $id ) ) . '">' . absint( $id ) . '</a>';
	}

	public function do_save( $return_val, $params, $metas, $module ) {
		$comment_id = wp_insert_comment( $params );

		if ( ! is_numeric( $comment_id ) ){
			return false;
		}

		foreach ( $metas as $key => $value ) {
			update_comment_meta( $comment_id, $key, $value );
		}

		return $comment_id;
	}

	public function parse_request( $qty, $request = array() ) {
		if ( is_null( $qty ) ) {
			$qty = Filter::super( INPUT_POST, array( Plugin::$slug, 'qty' ), FILTER_UNSAFE_RAW );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->faker->numberBetween( $min, $max );
		}

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of comments to fake...', 'fakerpress' );
		}

		$meta_module = Meta::instance();

		$comment_content_use_html = Filter::super( $request, array( 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$comment_content_html_tags = array_map( 'trim', explode( ',', Filter::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$min_date = Filter::super( $request, array( 'interval_date', 'min' ) );
		$max_date = Filter::super( $request, array( 'interval_date', 'max' ) );
		$metas = Filter::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $quantity; $i++ ) {
			$this->param( 'comment_date', $min_date, $max_date );
			$this->param( 'comment_content', $comment_content_use_html, array( 'elements' => $comment_content_html_tags ) );
			$this->param( 'user_id', 0 );

			$this->generate();

			$comment_id = $this->save();

			if ( $comment_id && is_numeric( $comment_id ) ){
				foreach ( $metas as $meta_index => $meta ) {
					$meta_module->object( $comment_id, 'comment' )->build( $meta['type'], $meta['name'], $meta )->save();
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
		$results = $this->parse_request( null, Filter::super( INPUT_POST, array( Plugin::$slug ), FILTER_UNSAFE_RAW ) );

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
