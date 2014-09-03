<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.users',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$qty_min = absint( Filter::super( INPUT_POST, 'fakerpress_qty_min', FILTER_SANITIZE_NUMBER_INT ) );

			$qty_max = absint( Filter::super( INPUT_POST, 'fakerpress_qty_max', FILTER_SANITIZE_NUMBER_INT ) );

			$roles = array_intersect( array_keys( get_editable_roles() ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_roles', FILTER_SANITIZE_STRING ) ) ) );

			$faker = new Module\User;

			if ( $qty_min === 0 ){
				return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'posts' ), 'error' );
			}

			if ( !empty( $qty_min ) && !empty( $qty_max ) ){
				$quantity = $faker->numberBetween( $qty_min, $qty_max );
			}

			if ( !empty( $qty_min ) && empty( $qty_max ) ){
				$quantity = $qty_min;
			}

			$results = (object) array();

			for ( $i = 0; $i < $quantity; $i++ ) {
				$results->all[] = $faker->generate(
					array(
						'role' => array( $roles ),
					)
				)->save();
			}
			$results->success = array_filter( $results->all, 'absint' );

			if ( count( $results->success ) !== 0 ){
				return Admin::add_message(
					sprintf(
						__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
						count( $results->success ),
						_n( 'user', 'users', count( $results->success ), 'fakerpress' ),
						implode( ', ', $results->success )
					)
				);
			}
		}
	}
);

add_action(
	'init',
	function() {
		Admin::add_menu( 'users', __( 'Users', 'fakerpress' ), __( 'Users', 'fakerpress' ), 'manage_options', 15 );
	}
);
