<?php
/**
 * Abstract base class for FakerPress REST API endpoints.
 *
 * Provides common functionality and implements the Interface_Endpoint.
 *
 * @since   TBD
 * @package FakerPress
 */

namespace FakerPress\REST;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Error;

/**
 * Abstract class Abstract_Endpoint
 *
 * Base implementation for all REST API endpoints.
 *
 * @since TBD
 */
abstract class Abstract_Endpoint implements Interface_Endpoint {

	/**
	 * The base route for this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $base_route = '';

	/**
	 * The permission required to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $permission_required = 'manage_options';

	/**
	 * Register the routes for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_routes() {
		$routes = $this->get_routes();

		foreach ( $routes as $route => $config ) {
			register_rest_route(
				Controller::get_namespace(),
				$this->get_base_route() . $route,
				$config
			);
		}
	}

	/**
	 * Get the routes configuration for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	abstract protected function get_routes();

	/**
	 * Get the base route for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_base_route() {
		return $this->base_route;
	}

	/**
	 * Get the permission required to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_permission_required() {
		return $this->permission_required;
	}

	/**
	 * Check if the current user has permission to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function check_permission() {
		return current_user_can( $this->get_permission_required() );
	}

	/**
	 * Validate the request parameters.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return bool|WP_Error
	 */
	public function validate_request( $request ) {
		$schema = $this->get_request_schema();

		if ( empty( $schema ) ) {
			return true;
		}

		$errors = [];

		// Validate required parameters.
		if ( isset( $schema['required'] ) && is_array( $schema['required'] ) ) {
			foreach ( $schema['required'] as $required_param ) {
				if ( ! $request->has_param( $required_param ) ) {
					$errors[] = sprintf(
						__( 'Missing required parameter: %s', 'fakerpress' ),
						$required_param
					);
				}
			}
		}

		// Validate parameter types and formats.
		if ( isset( $schema['properties'] ) && is_array( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $param => $config ) {
				if ( ! $request->has_param( $param ) ) {
					continue;
				}

				$value = $request->get_param( $param );
				$type  = $config['type'] ?? 'string';

				if ( ! $this->validate_parameter_type( $value, $type ) ) {
					$errors[] = sprintf(
						__( 'Parameter %s must be of type %s', 'fakerpress' ),
						$param,
						$type
					);
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'rest_invalid_param',
				implode( ', ', $errors ),
				[ 'status' => 400 ]
			);
		}

		return true;
	}

	/**
	 * Validate a parameter type.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value The parameter value.
	 * @param string $type  The expected type.
	 *
	 * @return bool
	 */
	protected function validate_parameter_type( $value, $type ) {
		switch ( $type ) {
			case 'integer':
				return is_numeric( $value );
			case 'boolean':
				return is_bool( $value ) || in_array( $value, [ '1', '0', 'true', 'false' ], true );
			case 'array':
				return is_array( $value );
			case 'object':
				return is_object( $value ) || is_array( $value );
			case 'string':
			default:
				return is_string( $value ) || is_numeric( $value );
		}
	}

	/**
	 * Sanitize the request parameters.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return array
	 */
	public function sanitize_request( $request ) {
		$schema     = $this->get_request_schema();
		$sanitized  = [];

		if ( empty( $schema['properties'] ) ) {
			return $request->get_params();
		}

		foreach ( $schema['properties'] as $param => $config ) {
			if ( ! $request->has_param( $param ) ) {
				continue;
			}

			$value = $request->get_param( $param );
			$type  = $config['type'] ?? 'string';

			$sanitized[ $param ] = $this->sanitize_parameter( $value, $type );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a parameter value.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value The parameter value.
	 * @param string $type  The parameter type.
	 *
	 * @return mixed
	 */
	protected function sanitize_parameter( $value, $type ) {
		switch ( $type ) {
			case 'integer':
				return (int) $value;
			case 'boolean':
				return rest_sanitize_boolean( $value );
			case 'array':
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : [];
			case 'object':
				return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : (object) [];
			case 'string':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Create a standardized success response.
	 *
	 * @since TBD
	 *
	 * @param mixed  $data    The response data.
	 * @param string $message Optional success message.
	 * @param int    $status  HTTP status code.
	 *
	 * @return WP_REST_Response
	 */
	protected function success_response( $data = null, $message = '', $status = 200 ) {
		$response = [
			'success' => true,
			'data'    => $data,
		];

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Create a standardized error response.
	 *
	 * @since TBD
	 *
	 * @param string $message Error message.
	 * @param string $code    Error code.
	 * @param int    $status  HTTP status code.
	 * @param mixed  $data    Optional error data.
	 *
	 * @return WP_REST_Response
	 */
	protected function error_response( $message, $code = 'rest_error', $status = 400, $data = null ) {
		$response = [
			'success' => false,
			'code'    => $code,
			'message' => $message,
		];

		if ( null !== $data ) {
			$response['data'] = $data;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Get the OpenAPI schema for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_openapi_schema() {
		$routes = $this->get_routes();
		$schema = [];

		foreach ( $routes as $route => $config ) {
			$path = '/' . $this->get_base_route() . $route;
			$path = str_replace( '//', '/', $path );

			$schema[ $path ] = $this->build_openapi_path_schema( $config );
		}

		return $schema;
	}

	/**
	 * Build OpenAPI path schema for a route configuration.
	 *
	 * @since TBD
	 *
	 * @param array $config Route configuration.
	 *
	 * @return array
	 */
	protected function build_openapi_path_schema( $config ) {
		$path_schema = [];

		if ( ! is_array( $config ) ) {
			$config = [ $config ];
		}

		foreach ( $config as $method_config ) {
			$methods = $method_config['methods'] ?? [ WP_REST_Server::READABLE ];

			if ( is_string( $methods ) ) {
				$methods = [ $methods ];
			}

			foreach ( $methods as $method ) {
				$method_lower = strtolower( $method );

				if ( 'GET' === $method ) {
					$method_lower = 'get';
				} elseif ( 'POST' === $method ) {
					$method_lower = 'post';
				} elseif ( 'PUT' === $method ) {
					$method_lower = 'put';
				} elseif ( 'DELETE' === $method ) {
					$method_lower = 'delete';
				}

				$path_schema[ $method_lower ] = [
					'summary'     => $method_config['summary'] ?? '',
					'description' => $method_config['description'] ?? '',
					'parameters'  => $this->build_openapi_parameters(),
					'responses'   => $this->build_openapi_responses(),
				];

				if ( in_array( $method, [ 'POST', 'PUT', 'PATCH' ], true ) ) {
					$path_schema[ $method_lower ]['requestBody'] = [
						'content' => [
							'application/json' => [
								'schema' => $this->get_request_schema(),
							],
						],
					];
				}
			}
		}

		return $path_schema;
	}

	/**
	 * Build OpenAPI parameters schema.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function build_openapi_parameters() {
		return [];
	}

	/**
	 * Build OpenAPI responses schema.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function build_openapi_responses() {
		return [
			'200' => [
				'description' => 'Success',
				'content'     => [
					'application/json' => [
						'schema' => $this->get_response_schema(),
					],
				],
			],
			'400' => [
				'description' => 'Bad Request',
				'content'     => [
					'application/json' => [
						'schema' => [
							'type'       => 'object',
							'properties' => [
								'success' => [
									'type'    => 'boolean',
									'example' => false,
								],
								'code'    => [
									'type'    => 'string',
									'example' => 'rest_error',
								],
								'message' => [
									'type'    => 'string',
									'example' => 'Error message',
								],
							],
						],
					],
				],
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
	abstract public function get_request_schema();

	/**
	 * Get the schema for response data.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	abstract public function get_response_schema();
} 
