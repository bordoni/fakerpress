<?php
namespace FakerPress;

add_action(
	'fakerpress.view.request.settings',
	function( $view ) {
		if ( Admin::$request_method === 'post' && ! empty( $_POST )  ) {
			$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

			if ( ! check_admin_referer( $nonce_slug ) ) {
				return false;
			}
			// After this point we are safe to say that we have a good POST request

			$erase_intention = is_string( Filter::super( INPUT_POST, 'fakerpress_erase_data', FILTER_SANITIZE_STRING ) );
			$erase_check     = in_array( strtolower( Filter::super( INPUT_POST, 'fakerpress_erase_check', FILTER_SANITIZE_STRING ) ), array( 'let it go', 'let it go!' ) );

			if ( $erase_intention ){
				if ( ! $erase_check ){
					return Admin::add_message( __( 'The verification to erase the data has failed, you have to let it go...', 'fakerpress' ), 'error' );
				}

				$refs = (object) array(
					'post' => array(),
					'term' => get_option( 'fakerpress.module_flag.term', array() ),
					'comment' => array(),
					'user' => array(),
				);

				$query_posts = new \WP_Query(
					array(
						'post_type' => 'any',
						'nopaging' => false,
						// @codingStandardsIgnoreStart | Still have to debug why this happens...
						'meta_query' => array(
						// @codingStandardsIgnoreEnd
							array(
								'key' => apply_filters( 'fakerpress.modules_flag', 'fakerpress_flag' ),
								'value' => true,
								'type' => 'BINARY',
							),
						),
					)
				);
				foreach ( $query_posts->posts as $post ){
					$refs->post[] = absint( $post->ID );
				}

				$query_comments = new \WP_Comment_Query;
				$query_comments = $query_comments->query(
					array(
						// @codingStandardsIgnoreStart | Still have to debug why this happens...
						'meta_query' => array(
						// @codingStandardsIgnoreEnd
							array(
								'key' => apply_filters( 'fakerpress.modules_flag', 'fakerpress_flag' ),
								'value' => true,
								'type' => 'BINARY',
							),
						),
					)
				);

				foreach ( $query_comments as $comment ){
					$refs->comment[] = absint( $comment->comment_ID );
				}

				$query_users = new \WP_User_Query(
					array(
						'fields' => 'ID',
						// @codingStandardsIgnoreStart | Still have to debug why this happens...
						'meta_query' => array(
						// @codingStandardsIgnoreEnd
							array(
								'key' => apply_filters( 'fakerpress.modules_flag', 'fakerpress_flag' ),
								'value' => true,
								'type' => 'BINARY',
							),
						),
					)
				);
				$refs->user = array_map( 'intval', $query_users->results );

				foreach ( $refs as $module => $ref ){
					switch ( $module ) {
						case 'post':
							foreach ( $ref as $post_id ){
								wp_delete_post( $post_id, true );
							}
							break;
						case 'comment':
							foreach ( $ref as $comment_id ){
								wp_delete_comment( $comment_id, true );
							}
							break;
						case 'term':
							foreach ( $ref as $taxonomy => $terms ){
								foreach ( $terms as $term ){
									wp_delete_term( $term, $taxonomy );
								}
							}
							delete_option( 'fakerpress.module_flag.term' );
							break;
						case 'user':
							foreach ( $ref as $user_id ){
								wp_delete_user( $user_id );
							}
							break;
					}
				}

				return Admin::add_message( __( 'All data is gone for good.', 'fakerpress' ), 'success' );
			}
		}
	}
);
