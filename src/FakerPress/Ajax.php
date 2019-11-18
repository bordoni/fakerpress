<?php
namespace FakerPress;

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_' . Plugin::$slug . '.select2-WP_Query', [ __CLASS__, 'query_posts' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.search_authors', [ __CLASS__, 'search_authors' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.select2-search_terms', [ __CLASS__, 'search_terms' ] );
		add_action( 'wp_ajax_' . Plugin::$slug . '.module_generate', [ __CLASS__, 'module_generate' ] );
	}

	public static function module_generate( $request = null ) {
		/**
		 * Allows us to prevent `_encloseme` and `_pingme` meta when generating Posts
		 *
		 * @since  0.4.9
		 *
		 * @param  bool  $prevent_enclose_ping_meta
		 */
		$prevent_enclose_ping_meta = (bool) apply_filters( 'fakerpress.module.generate.prevent_enclose_ping_meta', true );

		// This will prevent us having `_encloseme` and `_pingme`
		if ( $prevent_enclose_ping_meta ) {
			define( 'WP_IMPORTING', true );
		}

		$response = (object) [
			'status' => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
		];

		if ( ( ! Admin::$is_ajax && is_null( $request ) ) || ! is_user_logged_in() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$view = fp_get_global_var( INPUT_POST, [ Plugin::$slug, 'view' ], FILTER_SANITIZE_STRING );
		$nonce_slug = Plugin::$slug . '.request.' . $view;

		if ( ! check_admin_referer( $nonce_slug ) ) {
			$response->message = __( 'Security fail, refresh the page and try again!', 'fakerpress' );
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		// Here we have a Secure Call
		$module_class_name = '\FakerPress\Module\\' . rtrim( ucfirst( $view ), 's' );
		$module = call_user_func_array( [ $module_class_name, 'instance' ], [] );

		$response->allowed = $module->get_amount_allowed();
		$response->offset = absint( fp_get_global_var( INPUT_POST, [ 'offset' ], FILTER_UNSAFE_RAW ) );
		$qty = $response->total = absint( fp_get_global_var( INPUT_POST, [ 'total' ], FILTER_UNSAFE_RAW ) );

		if ( ! $response->total ){
			$qty = fp_get_global_var( INPUT_POST, [ Plugin::$slug, 'qty' ], FILTER_UNSAFE_RAW );

			if ( is_array( $qty ) ) {
				$min = absint( $qty['min'] );
				$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
				$qty = $module->faker->numberBetween( $min, $max );
			}
			$response->total = $qty;
		}

		if ( $qty > $response->allowed ){
			$response->is_capped = true;
			$qty = $response->allowed;

			if ( $response->total < $response->offset + $response->allowed ) {
				$qty += $response->total - ( $response->offset + $response->allowed );
				$response->is_capped = false;
			}
		} else {
			$response->is_capped = false;
		}

		$results = $module->parse_request( $qty, fp_get_global_var( INPUT_POST, [ Plugin::$slug ], FILTER_UNSAFE_RAW ) );
		$response->offset += $qty;

		if ( is_string( $results ) ){
			$response->message = $results;
		} else {
			$response->status = true;
			$response->message = implode( ', ', array_map( [ $module, 'format_link' ], $results ) );
			$response->results = $results;
		}

		return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
	}

	public static function search_terms( $request = null ) {
		$response = (object) [
			'status' => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more' => true,
		];

		if ( ( ! Admin::$is_ajax && is_null( $request ) ) || ! is_user_logged_in() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) wp_parse_args(
			$request,
			[
				'search' => isset( $_POST['search'] ) ? $_POST['search'] : '',
				'post_type' => isset( $_POST['post_type'] ) ? $_POST['post_type'] : null,
				'taxonomies' => isset( $_POST['taxonomies'] ) ? $_POST['taxonomies'] : [],
				'exclude' => isset( $_POST['exclude'] ) ? $_POST['exclude'] : [],
				'page' => absint( isset( $_POST['page'] ) ? $_POST['page'] : 0 ),
				'page_limit' => absint( isset( $_POST['page_limit'] ) ? $_POST['page_limit'] : 10 ),
			]
		);

		if ( is_null( $request->post_type ) || empty( $request->post_type ) ){
			$request->post_type = get_post_types( [ 'public' => true ] );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		foreach ( $request->taxonomies as $taxonomy ) {
			$response->taxonomies[] = get_taxonomy( $taxonomy );
		}

		$args = [
			'hide_empty' => false,
			'number' => $request->page_limit,
			'offset' => $request->page_limit * ( $request->page - 1 ),
		];

		if ( ! empty( $request->search ) ){
			$args['search'] = $request->search;
		}

		if ( ! empty( $request->exclude ) ){
			$args['exclude'] = $request->exclude;
		}

		$response->args = $args;

		$terms = get_terms( (array) $request->taxonomies, $args );

		// Setting up Select2
		foreach ( $terms as $term ) {
			$response->results[] = [
				'id' => $term->term_id,
				'value' => $term->term_id,
				'name' => $term->name,
				'taxonomy' => $term->taxonomy,
			];
		}

		if ( empty( $response->results ) || count( $response->results ) < $request->page_limit ){
			$response->more = false;
		}

		return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
	}

	public static function query_posts( $request = null ) {
		$response = (object) [
			'status' => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more' => true,
		];

		if ( ( ! Admin::$is_ajax && is_null( $request ) ) || ! is_user_logged_in() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) $_POST;

		if ( isset( $request->query['post_type'] ) && ! is_array( $request->query['post_type'] ) ){
			$request->query['post_type'] = array_map( 'trim', (array) explode( ',', $request->query['post_type'] ) );
		}

		$query = new \WP_Query( $request->query );

		if ( ! $query->have_posts() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		foreach ( $query->posts as $k => $post ) {
			$query->posts[ $k ]->post_type = get_post_type_object( $post->post_type );
		}

		$response->results = $query->posts;

		if ( $query->max_num_pages >= $request->query['paged'] ){
			$response->more = false;
		}

		return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
	}

	public static function search_authors( $request = null ) {
		$response = (object) [
			'status' => false,
			'message' => __( 'Your request has failed', 'fakerpress' ),
			'results' => [],
			'more' => true,
		];

		if ( ( ! Admin::$is_ajax && is_null( $request ) ) || ! is_user_logged_in() ){
			return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
		}

		$request = (object) wp_parse_args(
			$request,
			[
				'search' => isset( $_POST['search'] ) ? $_POST['search'] : '',
				'page' => absint( isset( $_POST['page'] ) ? $_POST['page'] : 0 ),
				'page_limit' => absint( isset( $_POST['page_limit'] ) ? $_POST['page_limit'] : 10 ),
			]
		);

		$response->status  = true;
		$response->message = __( 'Request successful', 'fakerpress' );

		$query_args = [
			'orderby' => 'display_name',
			'offset'  => $request->page_limit * ( $request->page - 1 ),
			'number'  => $request->page_limit,
		];

		if ( ! empty( $request->search ) ){
			$query_args['search'] = "*{$request->search}*";
			$query_args['search_columns'] = [
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			];
		}

		$users = new \WP_User_Query( $query_args );

		foreach ( $users->results as $result ){
			$response->results[] = $result;
		}

		if ( empty( $response->results ) || count( $response->results ) < $request->page_limit ){
			$response->more = false;
		}

		return ( Admin::$is_ajax ? exit( json_encode( $response ) ) : $response );
	}
}