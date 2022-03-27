<?php

namespace FakerPress;

use FakerPress\Module\Factory;
use FakerPress\Admin\View\Factory as View_Factory;

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_' . Plugin::$slug . '.select2-WP_Query', [ __CLASS__, 'query_posts' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.search_authors', [ __CLASS__, 'search_authors' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.select2-search_terms', [ __CLASS__, 'search_terms' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.module_generate', [ __CLASS__, 'module_generate' ] );
	}

	public static function module_generate( $request = null ) {
		$response = (object) [
			'status'  => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
		];

		if ( ( ! is_ajax() && is_null( $request ) ) || ! is_user_logged_in() ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$view = get_request_var( [ Plugin::$slug, 'view' ] );
		if ( empty( $view ) ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$nonce_slug = Plugin::$slug . '.request.' . $view;

		if ( ! check_admin_referer( $nonce_slug ) ) {
			$response->message = __( 'Security fail, refresh the page and try again!', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		// Here we have a Secure Call
		$module = make( Factory::class )->get( $view );
		if ( empty( $module ) ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$permission_required = $module::get_permission_required();

		if ( ! current_user_can( $permission_required ) ) {
			$response->message = sprintf( __( 'Your user needs the "%s" permission to execute the generation for this module.', 'fakerpress' ), $permission_required );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$response->allowed = $module->get_amount_allowed();
		$response->offset  = absint( get_request_var( 'offset' ) );
		$qty               = $response->total = absint( get_request_var( 'total' ) );

		if ( ! $response->total ) {
			$qty = get_request_var( [ Plugin::$slug, 'qty' ] );

			if ( is_array( $qty ) ) {
				$min = absint( $qty['min'] );
				$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
				$qty = $module->get_faker()->numberBetween( $min, $max );
			}
			$response->total = $qty;
		}

		if ( $qty > $response->allowed ) {
			$response->is_capped = true;
			$qty                 = $response->allowed;

			if ( $response->total < $response->offset + $response->allowed ) {
				$qty                 += $response->total - ( $response->offset + $response->allowed );
				$response->is_capped = false;
			}
		} else {
			$response->is_capped = false;
		}

		$data = get_request_var( 'fakerpress', [] );

		$results          = $module->parse_request( $qty, $data );
		$response->offset += $qty;

		if ( is_string( $results ) ) {
			$response->message = $results;
		} else {
			$response->status  = true;
			$response->message = implode( ', ', array_map( [ make( View_Factory::class )->get( $view ), 'format_link' ], $results ) );
			$response->results = $results;
		}

		return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
	}

	public static function search_terms( $request = null ) {
		$response = (object) [
			'status'  => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more'    => true,
		];

		if ( ( ! is_ajax() && is_null( $request ) ) || ! is_user_logged_in() ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) wp_parse_args(
			$request,
			[
				'search'     => get_request_var( 'search', '' ),
				'post_type'  => get_request_var( 'post_type' ),
				'taxonomies' => get_request_var( 'taxonomies', [] ),
				'exclude'    => get_request_var( 'exclude', [] ),
				'page'       => absint( get_request_var( 'page', 0 ) ),
				'page_limit' => absint( get_request_var( 'page_limit', 10 ) ),
				'nonce'      => get_request_var( 'nonce' ),
			]
		);

		if ( ! wp_verify_nonce( $request->nonce, Plugin::$slug . '-select-search_terms' ) ) {
			$response->message = esc_attr__( 'Invalid nonce verification', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			$response->message = esc_attr__( 'Your user needs the "publish_posts" permissions to search for terms.', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		if ( empty( $request->post_type ) ) {
			$request->post_type = get_post_types( [ 'public' => true ] );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		foreach ( $request->taxonomies as $taxonomy ) {
			$response->taxonomies[] = get_taxonomy( $taxonomy );
		}

		$args = [
			'hide_empty' => false,
			'number'     => $request->page_limit,
			'offset'     => $request->page_limit * ( $request->page - 1 ),
		];

		if ( ! empty( $request->search ) ) {
			$args['search'] = $request->search;
		}

		if ( ! empty( $request->exclude ) ) {
			$args['exclude'] = $request->exclude;
		}

		$response->args = $args;

		$terms = get_terms( (array) $request->taxonomies, $args );

		// Setting up Select2
		foreach ( $terms as $term ) {
			$response->results[] = [
				'id'       => $term->term_id,
				'value'    => $term->term_id,
				'name'     => $term->name,
				'taxonomy' => $term->taxonomy,
			];
		}

		if ( empty( $response->results ) || count( $response->results ) < $request->page_limit ) {
			$response->more = false;
		}

		return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
	}

	public static function query_posts( $request = null ) {
		$response = (object) [
			'status'  => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more'    => true,
		];

		if ( ( ! is_ajax() && is_null( $request ) ) || ! is_user_logged_in() ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) $_POST;

		if ( empty( $request->nonce ) || ! wp_verify_nonce( $request->nonce, Plugin::$slug . '-select2-WP_Query' ) ) {
			$response->message = esc_attr__( 'Invalid nonce verification', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			$response->message = esc_attr__( 'Your user needs the "publish_posts" permissions to use WP_Query.', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		if ( isset( $request->query['post_type'] ) && ! is_array( $request->query['post_type'] ) ) {
			$request->query['post_type'] = array_map( 'trim', (array) explode( ',', $request->query['post_type'] ) );
		}

		$query = new \WP_Query( $request->query );

		if ( ! $query->have_posts() ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		foreach ( $query->posts as $k => $post ) {
			$query->posts[ $k ]->post_type = get_post_type_object( $post->post_type );
		}

		$response->results = $query->posts;

		if ( $query->max_num_pages >= $request->query['paged'] ) {
			$response->more = false;
		}

		return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
	}

	public static function search_authors( $request = null ) {
		$response = (object) [
			'status'  => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more'    => true,
		];

		if ( ( ! is_ajax() && is_null( $request ) ) || ! is_user_logged_in() ) {
			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) wp_parse_args(
			$request,
			[
				'search'     => get_request_var( 'search', '' ),
				'page'       => absint( get_request_var( 'page', 0 ) ),
				'page_limit' => absint( get_request_var( 'page_limit', 10 ) ),
				'nonce'      => get_request_var( 'nonce' ),
			]
		);

		if ( ! wp_verify_nonce( $request->nonce, Plugin::$slug . '-select2-search_authors' ) ) {
			$response->message = esc_attr__( 'Invalid nonce verification', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			$response->message = esc_attr__( 'Your user needs the "publish_posts" permissions to search for authors.', 'fakerpress' );

			return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		$query_args = [
			'orderby' => 'display_name',
			'offset'  => $request->page_limit * ( $request->page - 1 ),
			'number'  => $request->page_limit,
		];

		if ( ! empty( $request->search ) ) {
			$query_args['search']         = "*{$request->search}*";
			$query_args['search_columns'] = [
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			];
		}

		$users = new \WP_User_Query( $query_args );

		foreach ( $users->results as $result ) {
			$response->results[] = $result;
		}

		if ( empty( $response->results ) || count( $response->results ) < $request->page_limit ) {
			$response->more = false;
		}

		return ( is_ajax() ? exit( json_encode( $response ) ) : $response );
	}
}
