<?php
/**
 * Terms generation endpoint for FakerPress REST API.
 *
 * Handles fake term generation via REST API.
 *
 * @since TBD
 * @package FakerPress
 */

namespace FakerPress\REST\Endpoints;

use FakerPress\REST\Abstract_Endpoint;
use FakerPress\REST\Traits\Handles_Batching;
use FakerPress\Module\Factory;
use FakerPress\Admin\View\Factory as View_Factory;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

use function FakerPress\make;

/**
 * Class Terms
 *
 * Endpoint for generating fake terms.
 *
 * @since 0.9.0
 */
class Terms extends Abstract_Endpoint {
	use Handles_Batching;

	/**
	 * The base route for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected string $base_route = '/terms';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var ?string
	 */
	protected ?string $permission_required = 'manage_categories';

	/**
	 * Get the routes configuration for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	protected function get_routes() {
		return [
			'/generate' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'generate_terms' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => $this->get_endpoint_args(),
				'summary'             => 'Generate fake terms',
				'description'         => 'Generates fake taxonomy terms with customizable parameters.',
			],
			'/search'   => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_terms' ],
				'permission_callback' => [ $this, 'check_term_search_permission' ],
				'args'                => [
					'search'     => [
						'description'       => __( 'Text to search terms by.', 'fakerpress' ),
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					],
					'taxonomies' => [
						'description' => __( 'Taxonomies to scope the search to.', 'fakerpress' ),
						'type'        => 'array',
						'items'       => [ 'type' => 'string' ],
						'default'     => [],
					],
					'exclude'    => [
						'description' => __( 'Term IDs to exclude from the results.', 'fakerpress' ),
						'type'        => 'array',
						'items'       => [ 'type' => 'integer' ],
						'default'     => [],
					],
					'page'       => [
						'description' => __( 'Page of results to return.', 'fakerpress' ),
						'type'        => 'integer',
						'default'     => 1,
						'minimum'     => 1,
					],
					'per_page'   => [
						'description' => __( 'Number of results to return per page.', 'fakerpress' ),
						'type'        => 'integer',
						'default'     => 10,
						'minimum'     => 1,
					],
				],
				'summary'             => 'Search taxonomy terms',
				'description'         => 'Searches existing taxonomy terms for the term autocomplete in the post generator.',
			],
		];
	}

	/**
	 * Search existing taxonomy terms.
	 *
	 * Powers the term autocomplete used by the Taxonomy Field Rules in the post generator.
	 *
	 * @since 0.9.1
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function search_terms( $request ) {
		$search     = (string) $request->get_param( 'search' );
		$taxonomies = array_values( array_filter( array_map( 'sanitize_key', (array) $request->get_param( 'taxonomies' ) ) ) );
		$exclude    = array_values( array_filter( array_map( 'absint', (array) $request->get_param( 'exclude' ) ) ) );
		$page       = max( 1, (int) $request->get_param( 'page' ) );
		$per_page   = max( 1, (int) $request->get_param( 'per_page' ) );

		// Default to all public taxonomies when none are provided.
		if ( empty( $taxonomies ) ) {
			$taxonomies = get_taxonomies( [ 'public' => true ], 'names' );
		}

		$args = [
			'taxonomy'   => $taxonomies,
			'hide_empty' => false,
			'number'     => $per_page,
			'offset'     => $per_page * ( $page - 1 ),
			'search'     => $search,
		];

		if ( ! empty( $exclude ) ) {
			$args['exclude'] = $exclude;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return $this->error_response( $terms->get_error_message(), 'term_search_failed', 400 );
		}

		$results = array_map(
			static function ( $term ) {
				return [
					'id'       => $term->term_id,
					'value'    => $term->term_id,
					'name'     => $term->name,
					'taxonomy' => $term->taxonomy,
				];
			},
			$terms
		);

		return $this->success_response( [ 'results' => $results ] );
	}

	/**
	 * Permission check for the term search route.
	 *
	 * Mirrors the post generator page capability so post authors are able to search
	 * for terms, rather than the broader `manage_categories` used for term generation.
	 *
	 * @since 0.9.1
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return bool|WP_Error
	 */
	public function check_term_search_permission( $request ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'rest_not_logged_in', __( 'You must be logged in to search terms.', 'fakerpress' ), [ 'status' => 401 ] );
		}

		return current_user_can( 'publish_posts' );
	}

	/**
	 * Generate fake terms.
	 *
	 * @since 0.9.0
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function generate_terms( $request ) {
		$start_time = microtime( true );

		// Validate the request.
		$validation = $this->validate_request( $request );
		if ( is_wp_error( $validation ) ) {
			return $this->error_response(
				$validation->get_error_message(),
				$validation->get_error_code(),
				400
			);
		}

		// Sanitize the request data.
		$params = $this->sanitize_request( $request );

		// Translate REST params to module format.
		// The admin form posts `taxonomies` (plural, the same key the module reads); the public
		// REST schema also exposes a singular `taxonomy` alias. Accept whichever arrived and
		// normalise to the comma-joined string the Term module expects. Preferring the plural
		// value stops the singular alias's default from silently forcing every term into
		// `category`. See https://github.com/bordoni/fakerpress/issues/218.
		$taxonomies_value = $params['taxonomies'] ?? $params['taxonomy'] ?? null;
		if ( null !== $taxonomies_value ) {
			if ( is_array( $taxonomies_value ) ) {
				$taxonomies_value = implode( ',', array_map( 'sanitize_key', array_map( 'strval', $taxonomies_value ) ) );
			}
			$params['taxonomies'] = sanitize_text_field( (string) $taxonomies_value );
			unset( $params['taxonomy'] );
		}

		// Get the module.
		$module = make( Factory::class )->get( 'terms' );
		if ( empty( $module ) ) {
			return $this->error_response(
				__( 'Terms module not found.', 'fakerpress' ),
				'module_not_found',
				404
			);
		}

		// Check module-specific permissions.
		$permission_required = $module::get_permission_required();
		if ( ! current_user_can( $permission_required ) ) {
			return $this->error_response(
				sprintf(
					__( 'Your user needs the "%s" permission to generate terms.', 'fakerpress' ),
					$permission_required
				),
				'insufficient_permissions',
				403
			);
		}

		// Calculate quantity with batching support.
		$batch_info = $this->calculate_batched_quantity( $params, $module );

		// Generate the terms.
		$results = $module->parse_request( $batch_info['quantity'], $params );

		$end_time = microtime( true );

		// Handle empty results.
		if ( empty( $results ) ) {
			return $this->error_response(
				__( 'Failed to generate terms. No results were returned.', 'fakerpress' ),
				'generation_failed',
				400
			);
		}

		// Handle WP Error.
		if ( is_wp_error( $results ) ) {
			return $this->error_response(
				$results->get_error_message(),
				$results->get_error_code(),
				400
			);
		}

		// Format the response.
		$view            = make( View_Factory::class )->get( 'terms' );
		$formatted_links = array_map( [ $view, 'format_link' ], $results );

		$response_data = $this->build_batched_response_data(
			$results,
			$batch_info,
			$formatted_links,
			$end_time - $start_time
		);

		$message = $this->format_batched_success_message(
			count( $results ),
			'term',
			$batch_info
		);

		return $this->success_response( $response_data, $message );
	}



	/**
	 * Get endpoint arguments for validation.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	protected function get_endpoint_args(): array {
		return array_merge(
			[
				'quantity' => [
					'description'       => __( 'Number of terms to generate.', 'fakerpress' ),
					'type'              => 'integer',
					'minimum'           => 1,
					'maximum'           => 1000,
					'default'           => null,
					'sanitize_callback' => function ( $value ) {
						return null === $value ? null : absint( $value );
					},
				],
				'qty'      => [
					'description' => __( 'Quantity range with min/max values.', 'fakerpress' ),
					'type'        => 'object',
					'properties'  => [
						'min' => [
							'type'    => 'integer',
							'minimum' => 1,
						],
						'max' => [
							'type'    => 'integer',
							'minimum' => 1,
						],
					],
				],
				'taxonomies' => [
					'description' => __( 'Taxonomies to generate terms for. Accepts an array of slugs or a comma-separated string.', 'fakerpress' ),
					'type'        => [ 'array', 'string' ],
					'items'       => [
						'type' => 'string',
					],
				],
				'taxonomy'   => [
					'description' => __( 'Deprecated singular alias for taxonomies — accepts a single taxonomy slug.', 'fakerpress' ),
					'type'        => 'string',
				],
				'meta'     => [
					'description' => __( 'Meta data to assign to generated terms.', 'fakerpress' ),
					'type'        => 'object',
				],
			],
			$this->get_batching_args()
		);
	}

	/**
	 * Get the schema for request parameters.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_request_schema() {
		return [];
	}

	/**
	 * Get the schema for response data.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_response_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'success' => [
					'type'        => 'boolean',
					'description' => __( 'Indicates if the generation was successful.', 'fakerpress' ),
					'example'     => true,
				],
				'data'    => [
					'$ref' => '#/components/schemas/GenerationResult',
				],
				'message' => [
					'type'        => 'string',
					'description' => __( 'Success message.', 'fakerpress' ),
					'example'     => __( 'Successfully generated 10 terms.', 'fakerpress' ),
				],
			],
			'required'   => [ 'success', 'data' ],
		];
	}

	/**
	 * Get the meta type for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return string
	 */
	protected function get_meta_type() {
		return 'term';
	}
} 
