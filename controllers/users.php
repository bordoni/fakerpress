<?php
namespace FakerPress\Control;

class Users {
	protected static $lock = false;

	protected static $view = 'users';

	protected static $method = null;

	protected static $is_ajax = false;

	public function __construct(){
		// The controllers should'nt be initialized twice, never
		if ( self::$lock === true ){
			return;
		}

		// Turn the lock on
		self::$lock = true;

		self::$method = strtolower( \FakerPress\Filter::super( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) );

		self::$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		add_action( 'fakerpress.view.start.' . self::$view, array( &$this, 'process' ) );
	}

	public function process( $view ){
		if ( self::$method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = \FakerPress\Plugin::$slug . '.request.' . \FakerPress\Admin::$view->slug . ( isset( \FakerPress\Admin::$view->action ) ? '.' . \FakerPress\Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$quantity = absint( \FakerPress\Filter::super( INPUT_POST, 'fakerpress_qty', FILTER_SANITIZE_NUMBER_INT ) );

			$faker = new \FakerPress\Module\User;

			for ( $i = 0; $i < $quantity; $i++ ) {
				$faker->generate(
					array(
						// 'post_status' => array( array( 'publish' ) ),
						// 'post_date' => array( '-2 months', 'now' ),
						// 'post_type' => array( 'post' ),
					)
				);

				$faker->save();
			}
		}
	}
}