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

	public function do_save( $return_val, $params, $metas, $module ){
		$comment_id = wp_insert_comment( $params );

		if ( ! is_numeric( $comment_id ) ){
			return false;
		}

		foreach ( $metas as $key => $value ) {
			update_comment_meta( $comment_id, $key, $value );
		}

		return $comment_id;
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
		$meta_module = Meta::instance();

		$qty_min = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'min' ), FILTER_SANITIZE_NUMBER_INT ) );
		$qty_max = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'max' ), FILTER_SANITIZE_NUMBER_INT ) );

		$comment_content_use_html = Filter::super( INPUT_POST, array( 'fakerpress', 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$comment_content_html_tags = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$min_date = Filter::super( INPUT_POST, array( 'fakerpress', 'interval_date', 'min' ) );
		$max_date = Filter::super( INPUT_POST, array( 'fakerpress', 'interval_date', 'max' ) );
		$metas = Filter::super( INPUT_POST, array( 'fakerpress', 'meta' ), FILTER_UNSAFE_RAW );

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
			$results->all[] = $comment_id;
		}
		$results->success = array_filter( $results->all, 'absint' );

		if ( ! empty( $results->success ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results->success ),
					_n( 'comment', 'comments', count( $results->success ), 'fakerpress' ),
					implode( ', ', $results->success )
				)
			);
		}
	}
}
