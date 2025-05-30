<?php
/**
 * REST API Controller for FakerPress.
 *
 * Handles the registration and management of REST endpoints for FakerPress modules.
 *
 * @since   TBD
 * @package FakerPress
 */

namespace FakerPress\REST;

use FakerPress\Plugin;
use FakerPress\Contracts\Service_Provider;
use WP_REST_Server;

use function FakerPress\singleton;

/**
 * Class Controller
 *
 * Main REST API controller that manages endpoint registration and routing.
 *
 * @since TBD
 */
class Controller extends Service_Provider {

	/**
	 * The namespace for all FakerPress REST endpoints.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	const NAMESPACE = 'fakerpress/v1';

	/**
	 * Array of registered endpoint controllers.
	 *
	 * @since TBD
	 *
	 * @var Abstract_Endpoint[]
	 */
	protected $endpoints = [];

	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		singleton( static::class, $this );

		$this->add_actions();
	}

	/**
	 * Adds the actions required by the REST API.
	 *
	 * @since TBD
	 */
	protected function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register all REST API routes.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_routes() {
		foreach ( $this->get_endpoints() as $endpoint ) {
			$endpoint->register_routes();
		}
	}

	/**
	 * Get all registered endpoints.
	 *
	 * @since TBD
	 *
	 * @return Abstract_Endpoint[]
	 */
	public function get_endpoints() {
		if ( empty( $this->endpoints ) ) {
			$this->endpoints = $this->load_endpoints();
		}

		return $this->endpoints;
	}

	/**
	 * Load and instantiate all endpoint classes.
	 *
	 * @since TBD
	 *
	 * @return Abstract_Endpoint[]
	 */
	protected function load_endpoints() {
		$endpoints = [];

		/**
		 * Filter the list of REST endpoint classes to load.
		 *
		 * @since TBD
		 *
		 * @param array $endpoint_classes Array of endpoint class names.
		 */
		$endpoint_classes = apply_filters( 'fakerpress_rest_endpoint_classes', [
			'FakerPress\REST\Endpoints\Documentation',
			// Module endpoints will be added here as they are created
			// 'FakerPress\REST\Endpoints\Posts',
			// 'FakerPress\REST\Endpoints\Users',
			// 'FakerPress\REST\Endpoints\Terms',
			// 'FakerPress\REST\Endpoints\Comments',
			// 'FakerPress\REST\Endpoints\Attachments',
			// 'FakerPress\REST\Endpoints\Meta',
		] );

		foreach ( $endpoint_classes as $class ) {
			if ( class_exists( $class ) ) {
				$endpoints[] = new $class();
			}
		}

		return $endpoints;
	}

	/**
	 * Get the REST namespace.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public static function get_namespace() {
		return self::NAMESPACE;
	}

	/**
	 * Check if the current user has permission to access FakerPress endpoints.
	 *
	 * @since TBD
	 *
	 * @param string $permission The required permission capability.
	 *
	 * @return bool
	 */
	public static function check_permission( $permission = 'manage_options' ) {
		return current_user_can( $permission );
	}

	/**
	 * Get OpenAPI documentation for all endpoints.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_openapi_documentation() {
		$documentation = [
			'openapi' => '3.0.0',
			'info'    => [
				'title'       => 'FakerPress REST API',
				'description' => 'REST API endpoints for FakerPress fake data generation.',
				'version'     => Plugin::VERSION,
			],
			'servers' => [
				[
					'url'         => rest_url( self::NAMESPACE ),
					'description' => 'FakerPress REST API',
				],
			],
			'paths'   => [],
		];

		foreach ( $this->get_endpoints() as $endpoint ) {
			if ( method_exists( $endpoint, 'get_openapi_schema' ) ) {
				$schema = $endpoint->get_openapi_schema();
				$documentation['paths'] = array_merge( $documentation['paths'], $schema );
			}
		}

		return $documentation;
	}
} 
