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

			$qty_min = absint( Filter::super( INPUT_POST, 'fakerpress_qty_min', FILTER_SANITIZE_NUMBER_INT ) );

			$qty_max = absint( Filter::super( INPUT_POST, 'fakerpress_qty_max', FILTER_SANITIZE_NUMBER_INT ) );

			$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, 'fakerpress_taxonomies', FILTER_SANITIZE_STRING ) ) ) );

			$faker = new Module\Term;

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
				$result = $faker->generate(
					array(
						'taxonomy' => array( $taxonomies )
					)
				)->save();

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

// We need o create a base class to lie all method related to AJAX
add_action(
	'wp_ajax_' . Plugin::$slug . '.search_terms',
	function ( $request = null ){
		$response = (object) array(
			'status' => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => array(),
			'more' => true,
		);

		if ( ( ! Admin::$is_ajax && is_null( $request ) ) || ! is_user_logged_in() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) wp_parse_args(
			$request,
			array(
				'search' => isset( $_POST['search'] ) ? $_POST['search'] : '',
				'post_type' => isset( $_POST['post_type'] ) ? $_POST['post_type'] : null,
				'page' => absint( isset( $_POST['page'] ) ? $_POST['page'] : 0 ),
				'page_limit' => absint( isset( $_POST['page_limit'] ) ? $_POST['page_limit'] : 10 ),
			)
		);

		if ( is_null( $request->post_type ) || empty( $request->post_type ) ){
			$request->post_type = get_post_types( array( 'public' => true ) );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		preg_match( '/@(\w+)/i', $request->search, $response->regex );

		if ( ! empty( $response->regex ) ){
			$request->search = array_filter( array_map( 'trim', explode( '|', str_replace( $response->regex[0], '|', $request->search ) ) ) );
			$request->search = reset( $request->search );
			$taxonomies      = $response->regex[1];
		} else {
			$taxonomies = get_object_taxonomies( $request->post_type );
		}
		$response->taxonomies = get_object_taxonomies( $request->post_type, 'objects' );

		$response->results = get_terms(
			(array) $taxonomies,
			array(
				'hide_empty' => false,
				'search' => $request->search,
				'number' => $request->page_limit,
				'offset' => $request->page_limit * ( $request->page - 1 ),
			)
		);

		if ( empty( $response->results ) || count( $response->results ) < $request->page_limit ){
			$response->more = false;
		}

		return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
	}
);