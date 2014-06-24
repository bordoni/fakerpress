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

			$quantity = absint( Filter::super( INPUT_POST, 'fakerpress_qty', FILTER_SANITIZE_NUMBER_INT ) );

			$min_date = Filter::super( INPUT_POST, 'fakerpress_min_date' );

			$max_date = Filter::super( INPUT_POST, 'fakerpress_max_date' );

			if ( $quantity === 0 ){
				return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'comments' ), 'error' );
			}

			$faker = new Module\Comment;

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
