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
	protected string $base_route = '';

	/**
	 * The permission required to access this endpoint, if not set, the endpoint is public.
	 *
	 * @since TBD
	 *
	 * @var ?string
	 */
	protected ?string $permission_required = 'manage_options';

	/**
	 * Whether to automatically convert endpoint args to request schema.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected bool $use_endpoint_args_for_body_schema = false;

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
	public function get_base_route(): string {
		return $this->base_route;
	}

	/**
	 * Get the permission required to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @return ?string
	 */
	public function get_permission_required(): ?string {
		return $this->permission_required;
	}

	/**
	 * Check if the current user has permission to access this endpoint.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error
	 */
	public function check_permission( WP_REST_Request $request ) {
		$permission = $this->get_permission_required();

		if ( null === $permission ) {
			return true;
		}

		$is_logged_in = is_user_logged_in();
		if ( ! $is_logged_in ) {
			return new WP_Error( 'rest_not_logged_in', __( 'You must be logged in to access this endpoint.', 'fakerpress' ), [ 'status' => 401 ] );
		}

		return current_user_can( $permission );
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
		$errors = [];

		if ( empty( $schema['properties'] ) ) {
			return true;
		}

		foreach ( $schema['properties'] as $param => $config ) {
			if ( ! $request->has_param( $param ) ) {
				// Check if parameter is required.
				if ( isset( $config['required'] ) && $config['required'] ) {
					$errors[] = sprintf(
						/* translators: %s: parameter name */
						__( 'Missing required parameter: %s', 'fakerpress' ),
						$param
					);
				}
				continue;
			}

			$value = $request->get_param( $param );
			$type  = $config['type'] ?? 'string';

			// Validate parameter type.
			if ( ! $this->validate_parameter_type( $value, $type ) ) {
				$errors[] = sprintf(
					/* translators: 1: parameter name, 2: expected type */
					__( 'Parameter %1$s must be of type %2$s.', 'fakerpress' ),
					$param,
					$type
				);
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

			$sanitized[ $param ] = $this->sanitize_parameter( $value, $type, $param );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a parameter value.
	 *
	 * Sanitizes values based on their type:
	 * - integer: Casts to integer
	 * - boolean: Converts to true/false using rest_sanitize_boolean()
	 * - array: Recursively sanitizes array values
	 * - object: Recursively sanitizes object properties
	 * - string: Sanitizes using sanitize_text_field()
	 *
	 * @since TBD
	 *
	 * @param mixed  $value      The parameter value to sanitize.
	 * @param string $type       The parameter type (integer, boolean, array, object, string).
	 * @param ?string $param_name Optional. The parameter name for context-specific sanitization.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_parameter( $value, string $type, ?string $param_name = null ) {
		switch ( $type ) {
			case 'integer':
				return (int) $value;
			case 'number':
				return is_float( $value ) ? (float) $value : (int) $value;
			case 'boolean':
				return rest_sanitize_boolean( $value );
			case 'array':
				return $this->sanitize_array_parameter( $value, $param_name );
			case 'object':
				return $this->sanitize_object_parameter( $value, $param_name );
			case 'string':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Sanitize an array parameter with proper chaining.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value      The array value to sanitize.
	 * @param string $param_name The parameter name for context.
	 *
	 * @return array
	 */
	protected function sanitize_array_parameter( $value, $param_name ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		// For non-meta arrays, we need schema to know how to sanitize.
		// Without schema, we cannot safely sanitize, so return empty array.
		return [];
	}

	/**
	 * Sanitize an object parameter with proper chaining.
	 *
	 * @since TBD
	 *
	 * @param mixed  $value      The object value to sanitize.
	 * @param string $param_name The parameter name for context.
	 *
	 * @return array|object
	 */
	protected function sanitize_object_parameter( $value, $param_name ) {
		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			return (object) [];
		}

		$array_value = (array) $value;

		// For non-meta objects, we need schema to know how to sanitize.
		// Without schema, we cannot safely sanitize, so return empty object.
		return (object) [];
	}

	/**
	 * Get the meta type for this endpoint.
	 *
	 * Override this method in child classes to specify the correct meta type.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function get_meta_type() {
		return 'post';
	}

	/**
	 * Get the meta schema for this endpoint.
	 *
	 * Override this method in child classes to provide specific meta field schemas.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_meta_schema() {
		return [];
	}

	/**
	 * Get the endpoint arguments.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	abstract protected function get_endpoint_args(): array;

	/**
	 * Get common batching arguments for generation endpoints.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_batching_args(): array {
		return [
			'offset' => [
				'description'       => __( 'Offset for batched generation (used internally).', 'fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 0,
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'total' => [
				'description'       => __( 'Total items to generate across all batches (used internally).', 'fakerpress' ),
				'type'              => 'integer',
				'minimum'           => 0,
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
		];
	}

	/**
	 * Convert endpoint args to request schema format.
	 *
	 * This method transforms WordPress REST API endpoint args format
	 * to OpenAPI/JSON Schema format for request body validation.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function convert_endpoint_args_to_schema() {
		if ( ! method_exists( $this, 'get_endpoint_args' ) ) {
			return [
				'type'       => 'object',
				'properties' => [],
			];
		}

		$endpoint_args = $this->get_endpoint_args();
		$properties    = [];

		foreach ( $endpoint_args as $param => $config ) {
			$property = [];

			// Map type.
			if ( isset( $config['type'] ) ) {
				$property['type'] = $config['type'];
			}

			// Map description.
			if ( isset( $config['description'] ) ) {
				$property['description'] = $config['description'];
			}

			// Map default value.
			if ( isset( $config['default'] ) ) {
				$property['default'] = $config['default'];
			}

			// Map enum values.
			if ( isset( $config['enum'] ) ) {
				$property['enum'] = $config['enum'];
			}

			// Map minimum/maximum for numbers.
			if ( isset( $config['minimum'] ) ) {
				$property['minimum'] = $config['minimum'];
			}
			if ( isset( $config['maximum'] ) ) {
				$property['maximum'] = $config['maximum'];
			}

			// Map array items.
			if ( isset( $config['items'] ) ) {
				$property['items'] = $config['items'];
			}

			// Map object properties.
			if ( isset( $config['properties'] ) ) {
				$property['properties'] = $config['properties'];
			}

			// Map required status.
			if ( isset( $config['required'] ) && $config['required'] ) {
				$property['required'] = true;
			}

			// Add example if not present but default is available.
			if ( ! isset( $property['example'] ) && isset( $property['default'] ) ) {
				$property['example'] = $property['default'];
			}

			$properties[ $param ] = $property;
		}

		return [
			'type'       => 'object',
			'properties' => $properties,
		];
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
	protected function build_openapi_path_schema( array $config ) {
		$path_schema = [];

		if ( is_array( $config ) && isset( $config['methods'] ) ) {
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
					'tags'        => $this->get_tags(),
				];

				if ( in_array( $method, [ 'POST', 'PUT', 'PATCH' ], true ) ) {
					$path_schema[ $method_lower ]['requestBody'] = [
						'content' => [
							'application/json' => [
								'schema' => $this->convert_endpoint_args_to_schema(),
							],
						],
					];
				}
			}
		}

		return $path_schema;
	}

	/**
	 * Get the tags for this endpoint.
	 *
	 * @since TBD
	 *
	 * @return array<string>
	 */
	protected function get_tags(): array {
		return [ trim( $this->get_base_route(), '/' ) ];
	}

	/**
	 * Build OpenAPI parameters schema.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function build_openapi_parameters() {
		return $this->get_request_schema()['properties'] ?? [];
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
