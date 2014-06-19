<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.terms',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$quantity = absint( Filter::super( INPUT_POST, 'fakerpress_qty', FILTER_SANITIZE_NUMBER_INT ) );

			if ( $quantity === 0 ){
				return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'terms' ), 'error' );
			}

			$faker = new Module\Term;

			$results = (object) array();

			for ( $i = 0; $i < $quantity; $i++ ) {
				$result = $faker->generate()->save();
				$results->all[] = $result['term_id'];
			}


			if ( count( $results->all ) !== 0 ){
				return Admin::add_message(
					sprintf(
						__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
						count( $results->all ),
						_n( 'term', 'terms', count( $results->all ), 'fakerpress' ),
						implode( ', ', $results->all )
					)
				);
			}
		}
	}
);

add_action(
	'init',
	function() {
		Admin::add_menu( 'terms', __( 'Terms', 'fakerpress' ), __( 'Terms', 'fakerpress' ), 'manage_options', 10 );
	}
);
