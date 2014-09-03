<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.comments',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$qty_min = absint( Filter::super( INPUT_POST, 'fakerpress_qty_min', FILTER_SANITIZE_NUMBER_INT ) );

			$qty_max = absint( Filter::super( INPUT_POST, 'fakerpress_qty_max', FILTER_SANITIZE_NUMBER_INT ) );

			$min_date = Filter::super( INPUT_POST, 'fakerpress_min_date' );

			$max_date = Filter::super( INPUT_POST, 'fakerpress_max_date' );
			
			$faker = new Module\Comment;

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
						'comment_date' => array( $min_date, $max_date ),
						'user_id' => array( 0 ),
					)
				)->save();
			}
			$results->success = array_filter( $results->all, 'absint' );

			if ( count( $results->success ) !== 0 ){
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
);

add_action(
	'init',
	function() {
		Admin::add_menu( 'comments', __( 'Comments', 'fakerpress' ), __( 'Comments', 'fakerpress' ), 'manage_options', 25 );
	}
);
