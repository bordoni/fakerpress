<?php
/**
 * OpenAPI documentation utilities for FakerPress REST API.
 *
 * Provides helper methods for generating OpenAPI documentation schemas.
 *
 * @since   TBD
 * @package FakerPress
 */

namespace FakerPress\REST;

use FakerPress\Plugin;

/**
 * Class OpenAPI
 *
 * Utilities for generating OpenAPI documentation.
 *
 * @since TBD
 */
class OpenAPI {

	/**
	 * Get the base OpenAPI specification structure.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public static function get_base_spec() {
		return [
			'openapi' => '3.0.0',
			'info'    => [
				'title'       => 'FakerPress REST API',
				'description' => 'REST API endpoints for FakerPress fake data generation plugin.',
				'version'     => Plugin::VERSION,
				'contact'     => [
					'name' => 'StellarWP',
					'url'  => 'https://stellarwp.com',
				],
				'license' => [
					'name' => 'GPL v2 or later',
					'url'  => 'https://www.gnu.org/licenses/gpl-2.0.html',
				],
			],
			'servers' => [
				[
					'url'         => rest_url( Controller::get_namespace() ),
					'description' => 'FakerPress REST API Server',
				],
			],
			'paths'      => [],
			'components' => [
				'schemas'         => self::get_common_schemas(),
				'securitySchemes' => self::get_security_schemes(),
			],
		];
	}

	/**
	 * Get common schemas used across endpoints.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public static function get_common_schemas() {
		return [
			'SuccessResponse' => [
				'type'       => 'object',
				'properties' => [
					'success' => [
						'type'        => 'boolean',
						'description' => 'Indicates if the request was successful.',
						'example'     => true,
					],
					'data'    => [
						'type'        => 'object',
						'description' => 'The response data.',
					],
					'message' => [
						'type'        => 'string',
						'description' => 'Optional success message.',
						'example'     => 'Operation completed successfully.',
					],
				],
				'required'   => [ 'success' ],
			],
			'ErrorResponse'   => [
				'type'       => 'object',
				'properties' => [
					'success' => [
						'type'        => 'boolean',
						'description' => 'Indicates if the request was successful.',
						'example'     => false,
					],
					'code'    => [
						'type'        => 'string',
						'description' => 'Error code.',
						'example'     => 'rest_error',
					],
					'message' => [
						'type'        => 'string',
						'description' => 'Error message.',
						'example'     => 'An error occurred.',
					],
					'data'    => [
						'type'        => 'object',
						'description' => 'Optional error data.',
					],
				],
				'required'   => [ 'success', 'code', 'message' ],
			],
			'GenerationResult' => [
				'type'       => 'object',
				'properties' => [
					'generated' => [
						'type'        => 'integer',
						'description' => 'Number of items generated.',
						'example'     => 10,
					],
					'ids'       => [
						'type'        => 'array',
						'description' => 'Array of generated item IDs.',
						'items'       => [
							'type' => 'integer',
						],
						'example'     => [ 1, 2, 3, 4, 5 ],
					],
					'time'      => [
						'type'        => 'number',
						'description' => 'Time taken to generate items in seconds.',
						'example'     => 1.23,
					],
				],
				'required'   => [ 'generated' ],
			],
		];
	}

	/**
	 * Get security schemes for authentication.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public static function get_security_schemes() {
		return [
			'cookieAuth' => [
				'type'        => 'apiKey',
				'in'          => 'cookie',
				'name'        => 'wordpress_logged_in',
				'description' => 'WordPress authentication cookie.',
			],
			'nonceAuth'  => [
				'type'        => 'apiKey',
				'in'          => 'header',
				'name'        => 'X-WP-Nonce',
				'description' => 'WordPress nonce for CSRF protection.',
			],
		];
	}

	/**
	 * Generate parameter schema for common parameters.
	 *
	 * @since TBD
	 *
	 * @param string $type Parameter type (quantity, meta, etc.).
	 *
	 * @return array
	 */
	public static function get_parameter_schema( $type ) {
		switch ( $type ) {
			case 'quantity':
				return [
					'type'        => 'integer',
					'description' => 'Number of items to generate.',
					'minimum'     => 1,
					'maximum'     => 1000,
					'default'     => 10,
					'example'     => 10,
				];

			case 'meta':
				return [
					'type'        => 'object',
					'description' => 'Meta data to assign to generated items.',
					'additionalProperties' => [
						'type' => 'string',
					],
					'example' => [
						'custom_field' => 'custom_value',
					],
				];

			case 'date_range':
				return [
					'type'       => 'object',
					'description' => 'Date range for generated items.',
					'properties' => [
						'start' => [
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => 'Start date (ISO 8601 format).',
							'example'     => '2023-01-01T00:00:00Z',
						],
						'end'   => [
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => 'End date (ISO 8601 format).',
							'example'     => '2023-12-31T23:59:59Z',
						],
					],
				];

			case 'author_ids':
				return [
					'type'        => 'array',
					'description' => 'Array of author IDs to assign to generated items.',
					'items'       => [
						'type' => 'integer',
					],
					'example'     => [ 1, 2, 3 ],
				];

			default:
				return [
					'type'        => 'string',
					'description' => 'Parameter value.',
				];
		}
	}

	/**
	 * Generate response schema for generation endpoints.
	 *
	 * @since TBD
	 *
	 * @param string $item_type Type of items being generated (posts, users, etc.).
	 *
	 * @return array
	 */
	public static function get_generation_response_schema( $item_type = 'items' ) {
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
					'example'     => sprintf( '%s generated successfully.', ucfirst( $item_type ) ),
				],
			],
			'required'   => [ 'success', 'data' ],
		];
	}

	/**
	 * Generate tags for endpoint categorization.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public static function get_tags() {
		return [
			[
				'name'        => 'Posts',
				'description' => 'Generate fake posts and custom post types.',
			],
			[
				'name'        => 'Users',
				'description' => 'Generate fake users.',
			],
			[
				'name'        => 'Terms',
				'description' => 'Generate fake taxonomy terms.',
			],
			[
				'name'        => 'Comments',
				'description' => 'Generate fake comments.',
			],
			[
				'name'        => 'Attachments',
				'description' => 'Generate fake media attachments.',
			],
			[
				'name'        => 'Meta',
				'description' => 'Generate fake meta data.',
			],
		];
	}

	/**
	 * Generate example request body for generation endpoints.
	 *
	 * @since TBD
	 *
	 * @param string $module_type Module type (post, user, etc.).
	 *
	 * @return array
	 */
	public static function get_example_request( $module_type ) {
		$base_example = [
			'quantity' => 10,
			'meta'     => [
				'custom_field' => 'custom_value',
			],
		];

		switch ( $module_type ) {
			case 'post':
				return array_merge( $base_example, [
					'post_type'   => 'post',
					'post_status' => 'publish',
					'author_ids'  => [ 1 ],
					'date_range'  => [
						'start' => '2023-01-01T00:00:00Z',
						'end'   => '2023-12-31T23:59:59Z',
					],
				] );

			case 'user':
				return array_merge( $base_example, [
					'role' => 'subscriber',
				] );

			case 'term':
				return array_merge( $base_example, [
					'taxonomy' => 'category',
				] );

			case 'comment':
				return array_merge( $base_example, [
					'post_ids' => [ 1, 2, 3 ],
				] );

			case 'attachment':
				return array_merge( $base_example, [
					'file_type' => 'image',
				] );

			default:
				return $base_example;
		}
	}
} 
