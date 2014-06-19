<?php
namespace FakerPress;

add_action(
	'fakerpress.view.start.posts',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$quantity = absint( Filter::super( INPUT_POST, 'fakerpress_qty', FILTER_SANITIZE_NUMBER_INT ) );

			$faker = new Module\Post;

			for ( $i = 0; $i < $quantity; $i++ ) {
				$faker->generate(
					array(
						'post_status' => array( array( 'publish' ) ),
						'post_date' => array( '-2 months', 'now' ),
						'post_type' => array( 'post' ),
					)
				)->save();
			}
		}
	}
);

add_action(
	'init',
	function() {
		Admin::add_menu( 'posts', __( 'Posts', 'fakerpress' ), __( 'Posts', 'fakerpress' ), 'manage_options', 5 );
	}
);
