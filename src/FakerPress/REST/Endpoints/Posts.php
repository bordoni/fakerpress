<?php
/**
 * Posts generation endpoint for FakerPress REST API.
 *
 * Handles fake post generation via REST API.
 *
 * @since   TBD
 * @package FakerPress
 */

namespace FakerPress\REST\Endpoints;

use FakerPress\REST\Abstract_Endpoint;
use FakerPress\REST\OpenAPI;
use FakerPress\Module\Factory;
use FakerPress\Admin\View\Factory as View_Factory;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

use function FakerPress\make;

/**
 * Class Posts
 *
 * Endpoint for generating fake posts.
 *
 * @since TBD
 */
class Posts extends Abstract_Endpoint {

	/**
	 * The base route for this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $base_route = '/posts';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $permission_required = 'publish_posts';

	/**
	 * Get the routes configuration for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_routes() {
		return [
			'/generate' => [
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'generate_posts' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => $this->get_endpoint_args(),
				'summary'             => 'Generate fake posts',
				'description'         => 'Generates fake posts with customizable parameters.',
			],
		];
	}

	/**
	 * Generate fake posts.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function generate_posts( $request ) {
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

		// Get the module.
		$module = make( Factory::class )->get( 'posts' );
		if ( empty( $module ) ) {
			return $this->error_response(
				__( 'Posts module not found.', 'fakerpress' ),
				'module_not_found',
				404
			);
		}

		// Check module-specific permissions.
		$permission_required = $module::get_permission_required();
		if ( ! current_user_can( $permission_required ) ) {
			return $this->error_response(
				sprintf(
					__( 'Your user needs the "%s" permission to generate posts.', 'fakerpress' ),
					$permission_required
				),
				'insufficient_permissions',
				403
			);
		}

		// Calculate quantity.
		$quantity = $this->calculate_quantity( $params, $module );

		// Generate the posts.
		$results = $module->parse_request( $quantity, $params );

		$end_time = microtime( true );

		// Handle results.
		if ( is_string( $results ) ) {
			return $this->error_response(
				$results,
				'generation_failed',
				400
			);
		}

		// Format the response.
		$view = make( View_Factory::class )->get( 'posts' );
		$formatted_links = array_map( [ $view, 'format_link' ], $results );

		$response_data = [
			'generated' => count( $results ),
			'ids'       => $results,
			'links'     => $formatted_links,
			'time'      => round( $end_time - $start_time, 3 ),
		];

		return $this->success_response(
			$response_data,
			sprintf(
				__( 'Successfully generated %d %s.', 'fakerpress' ),
				count( $results ),
				_n( 'post', 'posts', count( $results ), 'fakerpress' )
			)
		);
	}

	/**
	 * Calculate the quantity to generate based on request parameters.
	 *
	 * @since TBD
	 *
	 * @param array $params Request parameters.
	 * @param mixed $module The module instance.
	 *
	 * @return int
	 */
	protected function calculate_quantity( $params, $module ) {
		$quantity = $params['quantity'] ?? 10;

		// Handle quantity range.
		if ( isset( $params['qty'] ) && is_array( $params['qty'] ) ) {
			$min = absint( $params['qty']['min'] ?? 1 );
			$max = max( absint( $params['qty']['max'] ?? $min ), $min );
			$quantity = $module->get_faker()->numberBetween( $min, $max );
		}

		// Respect module limits.
		$allowed = $module->get_amount_allowed();
		if ( $quantity > $allowed ) {
			$quantity = $allowed;
		}

		return max( 1, $quantity );
	}

	/**
	 * Get endpoint arguments for validation.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_endpoint_args() {
		return [
			'quantity' => [
				'description'       => __( 'Number of posts to generate.', 'fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 1,
				'maximum'           => 1000,
				'default'           => 10,
				'sanitize_callback' => 'absint',
			],
			'qty' => [
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
			'post_type' => [
				'description' => __( 'Post type to generate.', 'fakerpress' ),
				'type'        => 'string',
				'default'     => 'post',
			],
			'post_status' => [
				'description' => __( 'Post status for generated posts.', 'fakerpress' ),
				'type'        => 'string',
				'default'     => 'publish',
				'enum'        => [ 'publish', 'draft', 'private', 'pending' ],
			],
			'author_ids' => [
				'description' => __( 'Array of author IDs to assign to posts.', 'fakerpress' ),
				'type'        => 'array',
				'items'       => [
					'type' => 'integer',
				],
			],
			'meta' => [
				'description' => __( 'Meta data to assign to generated posts.', 'fakerpress' ),
				'type'        => 'object',
			],
		];
	}

	/**
	 * Get the schema for request parameters.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_request_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'quantity'    => OpenAPI::get_parameter_schema( 'quantity' ),
				'qty'         => [
					'type'       => 'object',
					'description' => 'Quantity range with min/max values.',
					'properties' => [
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
				'post_type'   => [
					'type'        => 'string',
					'description' => 'Post type to generate.',
					'default'     => 'post',
					'example'     => 'post',
				],
				'post_status' => [
					'type'        => 'string',
					'description' => 'Post status for generated posts.',
					'default'     => 'publish',
					'enum'        => [ 'publish', 'draft', 'private', 'pending' ],
					'example'     => 'publish',
				],
				'author_ids'  => OpenAPI::get_parameter_schema( 'author_ids' ),
				'meta'        => OpenAPI::get_parameter_schema( 'meta' ),
			],
		];
	}

	/**
	 * Get the schema for response data.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_response_schema() {
		return OpenAPI::get_generation_response_schema( 'posts' );
	}
} 
