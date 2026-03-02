<?php
/**
 * Documentation endpoint for FakerPress REST API.
 *
 * Serves OpenAPI specification and documentation for all endpoints.
 *
 * @since TBD
 * @package FakerPress
 */

namespace FakerPress\REST\Endpoints;

use FakerPress\REST\Abstract_Endpoint;
use FakerPress\REST\Controller;
use FakerPress\REST\OpenAPI;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

use function FakerPress\make;

/**
 * Class Documentation
 *
 * Endpoint for serving API documentation.
 *
 * @since 0.9.0
 */
class Documentation extends Abstract_Endpoint {

	/**
	 * The base route for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	protected string $base_route = '/docs';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @var ?string
	 */
	protected ?string $permission_required = null;

	/**
	 * Get the routes configuration for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	protected function get_routes() {
		return [
			'/openapi' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_openapi_spec' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'summary'             => __( 'Get OpenAPI specification', 'fakerpress' ),
				'description'         => __( 'Returns the OpenAPI 3.0 specification in JSON format.', 'fakerpress' ),
			],
		];
	}

	/**
	 * Get the OpenAPI specification.
	 *
	 * @since 0.9.0
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_openapi_spec( $request ) {
		$controller = make( Controller::class );
		$spec       = $controller->get_openapi_documentation();

		// Add tags to the specification.
		$spec['tags'] = OpenAPI::get_tags();

		// Return raw OpenAPI spec without wrapper.
		return new WP_REST_Response( $spec, 200 );
	}

	/**
	 * Get the endpoint arguments.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	protected function get_endpoint_args(): array {
		return [];
	}

	/**
	 * Get the schema for request parameters.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_request_schema() {
		return [
			'type'       => 'object',
			'properties' => [],
		];
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
					'description' => __( 'Indicates if the request was successful.', 'fakerpress' ),
					'example'     => true,
				],
				'data'    => [
					'type'        => 'object',
					'description' => __( 'The OpenAPI specification.', 'fakerpress' ),
					'properties'  => [
						'openapi' => [
							'type'        => 'string',
							'description' => __( 'OpenAPI version.', 'fakerpress' ),
							'example'     => '3.0.0',
						],
						'info'    => [
							'type'        => 'object',
							'description' => __( 'API information.', 'fakerpress' ),
						],
						'paths'   => [
							'type'        => 'object',
							'description' => __( 'API paths and operations.', 'fakerpress' ),
						],
					],
				],
				'message' => [
					'type'        => 'string',
					'description' => __( 'Success message.', 'fakerpress' ),
					'example'     => __( 'API documentation retrieved successfully.', 'fakerpress' ),
				],
			],
			'required'   => [ 'success', 'data' ],
		];
	}
}
