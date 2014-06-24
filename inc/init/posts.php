<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.posts',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$quantity = absint( Filter::super( INPUT_POST, 'fakerpress_qty', FILTER_SANITIZE_NUMBER_INT ) );

			$post_author = array_intersect( get_users( array( 'fields' => 'ID' ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_author' ) ) ) );

			$min_date = Filter::super( INPUT_POST, 'fakerpress_min_date' );

			$max_date = Filter::super( INPUT_POST, 'fakerpress_max_date' );
			
			$post_types = array_intersect( get_post_types( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_post_types', FILTER_SANITIZE_STRING ) ) ) );

			$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_taxonomies', FILTER_SANITIZE_STRING ) ) ) );


			if ( $quantity === 0 ){
				return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'posts' ), 'error' );
			}

			$faker = new Module\Post;

			$results = (object) array();

			for ( $i = 0; $i < $quantity; $i++ ) {
				$results->all[] = $faker->generate(
					array(
						'tax_input' => array( $taxonomies ),
						'post_status' => array( array( 'publish' ) ),
						'post_date' => array( $min_date, $max_date ),
						'post_type' => array( 'post' ),
						'post_author'   => array( $post_author ),
						'post_type' => array( $post_types ),
					)
				)->save();
			}

			$results->success = array_filter( $results->all, 'absint' );

			if ( count( $results->success ) !== 0 ){
				return Admin::add_message(
					sprintf(
						__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
						count( $results->success ),
						_n( 'post', 'posts', count( $results->success ), 'fakerpress' ),
						implode(
							', ',
							array_map(
								function ( $id ){
									return '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . absint( $id ) . '</a>';
								},
								$results->success
							)
						)
					)
				);
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
