<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.posts',
	function( $view ) {
		if ( 'post' === Admin::$request_method && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}

			// After this point we are safe to say that we have a good POST request
			$qty_min = absint( Filter::super( INPUT_POST, 'fakerpress_qty_min', FILTER_SANITIZE_NUMBER_INT ) );

			$qty_max = absint( Filter::super( INPUT_POST, 'fakerpress_qty_max', FILTER_SANITIZE_NUMBER_INT ) );

			$comment_status = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_comment_status', FILTER_SANITIZE_STRING ) ) );

			$post_author = array_intersect( get_users( array( 'fields' => 'ID' ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_author' ) ) ) );

			$min_date = Filter::super( INPUT_POST, 'fakerpress_min_date' );

			$max_date = Filter::super( INPUT_POST, 'fakerpress_max_date' );

			$post_types = array_intersect( get_post_types( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_post_types', FILTER_SANITIZE_STRING ) ) ) );

			$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_taxonomies', FILTER_SANITIZE_STRING ) ) ) );

			$post_content_use_html = Filter::super( INPUT_POST, 'fakerpress_post_content_use_html', FILTER_SANITIZE_STRING, 'off' ) === 'on';
			$post_content_html_tags = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_post_content_html_tags', FILTER_SANITIZE_STRING ) ) );

			$post_parents = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_post_parents', FILTER_SANITIZE_STRING ) ) );

			$featured_image_rate = absint( Filter::super( INPUT_POST, 'fakerpress_featured_image_rate', FILTER_SANITIZE_NUMBER_INT ) );

			$module = new Module\Post;

			if ( 0 === $qty_min ){
				return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'posts' ), 'error' );
			}

			if ( ! empty( $qty_min ) && ! empty( $qty_max ) ){
				$quantity = $module->faker->numberBetween( $qty_min, $qty_max );
			}

			if ( ! empty( $qty_min ) && empty( $qty_max ) ){
				$quantity = $qty_min;
			}

			$results = (object) array();

			for ( $i = 0; $i < $quantity; $i++ ) {
				if ( $module->faker->numberBetween( 0, 100 ) <= $featured_image_rate ){
					$attach_module = new Module\Attachment;
					$attach_module->param( 'attachment_url', array( 'placeholdit' ) );
					$attach_module->generate();
					$attachment_id = $attach_module->save();
					$module->meta( '_thumbnail_id', null, array( $attachment_id ) );
				}

				$module->param( 'tax_input', array( $taxonomies ) );
				$module->param( 'post_status', array( array( 'publish' ) ) );
				$module->param( 'post_date', array( $min_date, $max_date ) );
				$module->param( 'post_parent', array( $post_parents ) );
				$module->param( 'post_content', array( $post_content_use_html, array( 'elements' => $post_content_html_tags ) ) );
				$module->param( 'post_author', array( $post_author ) );
				$module->param( 'post_type', array( $post_types ) );
				$module->param( 'comment_status', array( $comment_status ) );

				$module->generate();

				$results->all[] = $module->save();
			}

			$results->success = array_filter( $results->all, 'absint' );

			if ( ! empty( $results->success ) ){
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