<?php
/**
 * Attachments generation endpoint for FakerPress REST API.
 *
 * Handles fake attachment generation via REST API.
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
 * Class Attachments
 *
 * Endpoint for generating fake attachments.
 *
 * @since 0.9.0
 */
class Attachments extends Abstract_Endpoint {
	use Handles_Batching;

	/**
	 * The base route for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected string $base_route = '/attachments';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected ?string $permission_required = 'upload_files';

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
				'callback'            => [ $this, 'generate_attachments' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => $this->get_endpoint_args(),
				'summary'             => 'Generate fake attachments',
				'description'         => 'Generates fake attachments with customizable parameters including image sizes, providers, and metadata.',
			],
		];
	}

	/**
	 * Generate fake attachments.
	 *
	 * @since 0.9.0
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function generate_attachments( $request ) {
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
		$module = make( Factory::class )->get( 'attachments' );
		if ( empty( $module ) ) {
			return $this->error_response(
				__( 'Attachments module not found.', 'fakerpress' ),
				'module_not_found',
				404
			);
		}

		// Check module-specific permissions.
		$permission_required = $module::get_permission_required();
		if ( ! current_user_can( $permission_required ) ) {
			return $this->error_response(
				sprintf(
					__( 'Your user needs the "%s" permission to generate attachments.', 'fakerpress' ),
					$permission_required
				),
				'insufficient_permissions',
				403
			);
		}

		// Calculate quantity with batching support.
		$batch_info = $this->calculate_batched_quantity( $params, $module );

		// Generate the attachments.
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
		$view            = make( View_Factory::class )->get( 'attachments' );
		$formatted_links = array_map( [ $view, 'format_link' ], $results );

		$response_data = $this->build_batched_response_data(
			$results,
			$batch_info,
			$formatted_links,
			$end_time - $start_time
		);

		$message = $this->format_batched_success_message(
			count( $results ),
			'attachment',
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
				'quantity'             => [
					'description'       => __( 'Number of attachments to generate.', 'fakerpress' ),
					'type'              => 'integer',
					'minimum'           => 1,
					'maximum'           => 100,
					'default'           => null,
					'sanitize_callback' => function ( $value ) {
						return null === $value ? null : absint( $value );
					},
				],
				'qty'                  => [
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
				'provider'             => [
					'description' => __( 'Image provider to use for generating images.', 'fakerpress' ),
					'type'        => 'string',
					'default'     => 'placeholder',
					'enum'        => [ 'placeholder', 'lorempicsum' ],
				],
				'width'                => [
					'description' => __( 'Image width range or specific value.', 'fakerpress' ),
					'type'        => [ 'integer', 'object' ],
					'properties'  => [
						'min' => [
							'type'    => 'integer',
							'minimum' => 50,
						],
						'max' => [
							'type'    => 'integer',
							'minimum' => 50,
						],
					],
				],
				'height'               => [
					'description' => __( 'Image height range or specific value.', 'fakerpress' ),
					'type'        => [ 'integer', 'object' ],
					'properties'  => [
						'min' => [
							'type'    => 'integer',
							'minimum' => 50,
						],
						'max' => [
							'type'    => 'integer',
							'minimum' => 50,
						],
					],
				],
				'aspect_ratio'         => [
					'description' => __( 'Aspect ratio for generated images (width/height).', 'fakerpress' ),
					'type'        => 'number',
					'minimum'     => 0.1,
					'maximum'     => 10,
					'default'     => 1.5,
				],
				'file_types'           => [
					'description' => __( 'Array of file types to generate (jpg, png, gif, webp).', 'fakerpress' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
						'enum' => [ 'jpg', 'jpeg', 'png', 'gif', 'webp' ],
					],
					'default'     => [ 'jpg' ],
				],
				'post_parent'          => [
					'description' => __( 'Parent post ID or array of IDs to attach images to.', 'fakerpress' ),
					'type'        => [ 'integer', 'array' ],
					'items'       => [
						'type' => 'integer',
					],
					'default'     => 0,
				],
				'author_ids'           => [
					'description' => __( 'Array of author IDs to assign to attachments.', 'fakerpress' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'integer',
					],
				],
				'generate_alt_text'    => [
					'description' => __( 'Whether to generate random alt text for images.', 'fakerpress' ),
					'type'        => 'boolean',
					'default'     => true,
				],
				'generate_caption'     => [
					'description' => __( 'Whether to generate random captions for images.', 'fakerpress' ),
					'type'        => 'boolean',
					'default'     => true,
				],
				'generate_description' => [
					'description' => __( 'Whether to generate random descriptions for images.', 'fakerpress' ),
					'type'        => 'boolean',
					'default'     => true,
				],
				'date_range'           => [
					'description' => __( 'Date range for attachment creation dates.', 'fakerpress' ),
					'type'        => 'object',
					'properties'  => [
						'min' => [
							'type'   => 'string',
							'format' => 'date',
						],
						'max' => [
							'type'   => 'string',
							'format' => 'date',
						],
					],
				],
				'meta'                 => [
					'description' => __( 'Meta data to assign to generated attachments.', 'fakerpress' ),
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
					'description' => 'Indicates if the generation was successful.',
					'example'     => true,
				],
				'data'    => [
					'$ref' => '#/components/schemas/GenerationResult',
				],
				'message' => [
					'type'        => 'string',
					'description' => 'Success message.',
					'example'     => __( 'Successfully generated 10 attachments.', 'fakerpress' ),
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
