<?php
/**
 * OpenAPI documentation utilities for FakerPress REST API.
 *
 * Provides helper methods for generating OpenAPI documentation schemas.
 *
 * @since TBD
 * @package FakerPress
 */

namespace FakerPress\REST;

use FakerPress\Plugin;

/**
 * Class OpenAPI
 *
 * Utilities for generating OpenAPI documentation.
 *
 * @since 0.9.0
 */
class OpenAPI {

	/**
	 * Get the base OpenAPI specification structure.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public static function get_base_spec() {
		return [
			'openapi'    => '3.0.0',
			'info'       => [
				'title'       => __( 'FakerPress REST API', 'fakerpress' ),
				'description' => __( 'REST API endpoints for FakerPress fake data generation plugin.', 'fakerpress' ),
				'version'     => Plugin::VERSION,
				'contact'     => [
					'name' => 'Gustavo Bordoni',
					'url'  => 'https://bordoni.me',
				],
				'license'     => [
					'name' => 'GPL v2 or later',
					'url'  => 'https://www.gnu.org/licenses/gpl-2.0.html',
				],
			],
			'servers'    => [
				[
					'url'         => rest_url( Controller::get_namespace() ),
					'description' => __( 'FakerPress REST API Server', 'fakerpress' ),
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
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public static function get_common_schemas() {
		return [
			'SuccessResponse'  => [
				'type'       => 'object',
				'properties' => [
					'success' => [
						'type'        => 'boolean',
						'description' => __( 'Indicates if the request was successful.', 'fakerpress' ),
						'example'     => true,
					],
					'data'    => [
						'type'        => 'object',
						'description' => __( 'The response data.', 'fakerpress' ),
					],
					'message' => [
						'type'        => 'string',
						'description' => __( 'Optional success message.', 'fakerpress' ),
						'example'     => __( 'Operation completed successfully.', 'fakerpress' ),
					],
				],
				'required'   => [ 'success' ],
			],
			'ErrorResponse'    => [
				'type'       => 'object',
				'properties' => [
					'success' => [
						'type'        => 'boolean',
						'description' => __( 'Indicates if the request was successful.', 'fakerpress' ),
						'example'     => false,
					],
					'code'    => [
						'type'        => 'string',
						'description' => __( 'Error code.', 'fakerpress' ),
						'example'     => 'rest_error',
					],
					'message' => [
						'type'        => 'string',
						'description' => __( 'Error message.', 'fakerpress' ),
						'example'     => __( 'An error occurred.', 'fakerpress' ),
					],
					'data'    => [
						'type'        => 'object',
						'description' => __( 'Optional error data.', 'fakerpress' ),
					],
				],
				'required'   => [ 'success', 'code', 'message' ],
			],
			'GenerationResult' => [
				'type'       => 'object',
				'properties' => [
					'generated' => [
						'type'        => 'integer',
						'description' => __( 'Number of items generated.', 'fakerpress' ),
						'example'     => 10,
					],
					'ids'       => [
						'type'        => 'array',
						'description' => __( 'Array of generated item IDs.', 'fakerpress' ),
						'items'       => [
							'type' => 'integer',
						],
						'example'     => [ 1, 2, 3, 4, 5 ],
					],
					'links'     => [
						'type'        => 'array',
						'description' => __( 'Array of formatted links to generated items.', 'fakerpress' ),
						'items'       => [
							'type' => 'string',
						],
						'example'     => [ '<a href="...">Item 1</a>', '<a href="...">Item 2</a>' ],
					],
					'time'      => [
						'type'        => 'number',
						'description' => __( 'Time taken to generate items in seconds.', 'fakerpress' ),
						'example'     => 1.23,
					],
					'is_capped' => [
						'type'        => 'boolean',
						'description' => __( 'Whether the generation was capped due to limits (batching).', 'fakerpress' ),
						'example'     => false,
					],
					'offset'    => [
						'type'        => 'integer',
						'description' => __( 'Current offset for batched generation.', 'fakerpress' ),
						'example'     => 50,
					],
					'total'     => [
						'type'        => 'integer',
						'description' => __( 'Total number of items to generate across all batches.', 'fakerpress' ),
						'example'     => 100,
					],
				],
				'required'   => [ 'generated' ],
			],
		];
	}

	/**
	 * Get security schemes for authentication.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public static function get_security_schemes() {
		return [
			'cookieAuth' => [
				'type'        => 'apiKey',
				'in'          => 'cookie',
				'name'        => 'wordpress_logged_in',
				'description' => __( 'WordPress authentication cookie.', 'fakerpress' ),
			],
			'nonceAuth'  => [
				'type'        => 'apiKey',
				'in'          => 'header',
				'name'        => 'X-WP-Nonce',
				'description' => __( 'WordPress nonce for CSRF protection.', 'fakerpress' ),
			],
		];
	}

	/**
	 * Generate parameter schema for common parameters.
	 *
	 * @since 0.9.0
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
					'description' => __( 'Number of items to generate.', 'fakerpress' ),
					'minimum'     => 1,
					'maximum'     => 1000,
					'default'     => 10,
					'example'     => 10,
				];

			case 'meta':
				return [
					'type'                 => 'object',
					'description'          => __( 'Meta data to assign to generated items.', 'fakerpress' ),
					'additionalProperties' => [
						'type' => 'string',
					],
					'example'              => [
						'custom_field' => 'custom_value',
					],
				];

			case 'date_range':
				return [
					'type'        => 'object',
					'description' => __( 'Date range for generated items.', 'fakerpress' ),
					'properties'  => [
						'start' => [
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => __( 'Start date (ISO 8601 format).', 'fakerpress' ),
							'example'     => '2023-01-01T00:00:00Z',
						],
						'end'   => [
							'type'        => 'string',
							'format'      => 'date-time',
							'description' => __( 'End date (ISO 8601 format).', 'fakerpress' ),
							'example'     => '2023-12-31T23:59:59Z',
						],
					],
				];

			case 'author_ids':
				return [
					'type'        => 'array',
					'description' => __( 'Array of author IDs to assign to generated items.', 'fakerpress' ),
					'items'       => [
						'type' => 'integer',
					],
					'example'     => [ 1, 2, 3 ],
				];

			default:
				return [
					'type'        => 'string',
					'description' => __( 'Parameter value.', 'fakerpress' ),
				];
		}
	}

	/**
	 * Generate tags for endpoint categorization.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public static function get_tags() {
		return [
			[
				'name'        => 'posts',
				'description' => __( 'Generate fake posts and custom post types.', 'fakerpress' ),
			],
			[
				'name'        => 'users',
				'description' => __( 'Generate fake users.', 'fakerpress' ),
			],
			[
				'name'        => 'terms',
				'description' => __( 'Generate fake taxonomy terms.', 'fakerpress' ),
			],
			[
				'name'        => 'comments',
				'description' => __( 'Generate fake comments.', 'fakerpress' ),
			],
			[
				'name'        => 'attachments',
				'description' => __( 'Generate fake media attachments.', 'fakerpress' ),
			],
			[
				'name'        => 'meta',
				'description' => __( 'Generate fake meta data.', 'fakerpress' ),
			],
		];
	}
} 
