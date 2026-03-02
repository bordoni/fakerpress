<?php
/**
 * Posts generation endpoint for FakerPress REST API.
 *
 * Handles fake post generation via REST API.
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
 * Class Posts
 *
 * Endpoint for generating fake posts.
 *
 * @since 0.9.0
 */
class Posts extends Abstract_Endpoint {
	use Handles_Batching;

	/**
	 * The base route for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected string $base_route = '/posts';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected ?string $permission_required = 'publish_posts';

	/**
	 * Whether to automatically convert endpoint args to request schema.
	 *
	 * @since 0.9.0
	 *
	 * @var bool
	 */
	protected bool $use_endpoint_args_for_schema = true;

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
	 * @since 0.9.0
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

		// Calculate quantity with batching support.
		$batch_info = $this->calculate_batched_quantity( $params, $module );

		// Generate the posts.
		$results = $module->parse_request( $batch_info['quantity'], $params );

		$end_time = microtime( true );

		// Handle WP Error.
		if ( is_wp_error( $results ) ) {
			return $this->error_response(
				$results->get_error_message(),
				$results->get_error_code(),
				400
			);
		}

		// Format the response.
		$view            = make( View_Factory::class )->get( 'posts' );
		$formatted_links = array_map( [ $view, 'format_link' ], $results );

		$response_data = $this->build_batched_response_data(
			$results,
			$batch_info,
			$formatted_links,
			$end_time - $start_time
		);

		$message = $this->format_batched_success_message(
			count( $results ),
			'post',
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
				'quantity'    => [
					'description'       => __( 'Number of posts to generate.', 'fakerpress' ),
					'type'              => 'integer',
					'minimum'           => 1,
					'maximum'           => 1000,
					'default'           => null,
					'sanitize_callback' => function ( $value ) {
						return null === $value ? null : absint( $value );
					},
				],
				'qty'         => [
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
				'post_type'   => [
					'description' => __( 'Post type to generate.', 'fakerpress' ),
					'type'        => 'string',
					'default'     => 'post',
				],
				'post_status' => [
					'description' => __( 'Post status for generated posts.', 'fakerpress' ),
					'type'        => 'string',
					'default'     => 'publish',
					'enum'        => array_keys( get_post_stati( [], 'names' ) ),
				],
				'author_ids'  => [
					'description' => __( 'Array of author IDs to assign to posts.', 'fakerpress' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'integer',
					],
				],
				'meta'        => [
					'description' => __( 'Meta data to assign to generated posts.', 'fakerpress' ),
					'type'        => 'object',
				],
			],
			$this->get_batching_args()
		);
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
					'description' => 'Success message.',
					'example'     => __( 'Successfully generated 10 posts.', 'fakerpress' ),
				],
			],
			'required'   => [ 'success', 'data' ],
		];
	}

	/**
	 * Get the request schema for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_request_schema() {
		return [];
	}

	/**
	 * Get the meta type for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return string
	 */
	protected function get_meta_type() {
		return 'post';
	}
} 
