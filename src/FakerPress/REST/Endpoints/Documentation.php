<?php
/**
 * Documentation endpoint for FakerPress REST API.
 *
 * Serves OpenAPI specification and documentation for all endpoints.
 *
 * @since   TBD
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
 * @since TBD
 */
class Documentation extends Abstract_Endpoint {

	/**
	 * The base route for this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $base_route = '/docs';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $permission_required = 'read';

	/**
	 * Get the routes configuration for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_routes() {
		return [
			''         => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_documentation' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'summary'             => 'Get OpenAPI documentation',
				'description'         => 'Returns the complete OpenAPI specification for the FakerPress REST API.',
			],
			'/openapi' => [
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_openapi_spec' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'summary'             => 'Get OpenAPI specification',
				'description'         => 'Returns the OpenAPI 3.0 specification in JSON format.',
			],
		];
	}

	/**
	 * Get the API documentation.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_documentation( $request ) {
		$controller = make( Controller::class );
		$spec       = $controller->get_openapi_documentation();

		// Add tags to the specification.
		$spec['tags'] = OpenAPI::get_tags();

		return $this->success_response(
			$spec,
			__( 'API documentation retrieved successfully.', 'fakerpress' )
		);
	}

	/**
	 * Get the OpenAPI specification.
	 *
	 * @since TBD
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
	 * Get the schema for request parameters.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_response_schema() {
		return [
			'type'       => 'object',
			'properties' => [
				'success' => [
					'type'        => 'boolean',
					'description' => 'Indicates if the request was successful.',
					'example'     => true,
				],
				'data'    => [
					'type'        => 'object',
					'description' => 'The OpenAPI specification.',
					'properties'  => [
						'openapi' => [
							'type'        => 'string',
							'description' => 'OpenAPI version.',
							'example'     => '3.0.0',
						],
						'info'    => [
							'type'        => 'object',
							'description' => 'API information.',
						],
						'paths'   => [
							'type'        => 'object',
							'description' => 'API paths and operations.',
						],
					],
				],
				'message' => [
					'type'        => 'string',
					'description' => 'Success message.',
					'example'     => 'API documentation retrieved successfully.',
				],
			],
			'required'   => [ 'success', 'data' ],
		];
	}
} 
