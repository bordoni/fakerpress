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
use function FakerPress\make;

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
		
		// Ensure REST routes are registered early enough
		add_action( 'init', [ $this, 'ensure_rest_routes' ], 5 );
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

		/**
		 * Filter the list of REST endpoint classes to load.
		 *
		 * @since TBD
		 *
		 * @param array $endpoint_classes Array of endpoint class names.
		 */
		$endpoint_classes = apply_filters( 'fakerpress_rest_endpoint_classes', [
			Endpoints\Documentation::class,
			Endpoints\Posts::class,
			Endpoints\Users::class,
			Endpoints\Terms::class,
			Endpoints\Comments::class,
			Endpoints\Attachments::class,
			// Additional endpoints can be added here
			// 'FakerPress\REST\Endpoints\Meta',
		] );

		$endpoints = array_map(
			static function( $class ) {
				return class_exists( $class ) ? make( $class ) : null;
			},
			$endpoint_classes
		);

		// Filter out null values (classes that don't exist)
		return array_filter( $endpoints );
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
	 * Ensure REST routes are properly registered.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function ensure_rest_routes() {
		// Force re-registration of routes if needed
		if ( did_action( 'rest_api_init' ) ) {
			$this->register_routes();
		}
	}

	/**
	 * Get OpenAPI documentation for all endpoints.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_openapi_documentation() {
		$documentation = OpenAPI::get_base_spec();

		foreach ( $this->get_endpoints() as $endpoint ) {
			if ( method_exists( $endpoint, 'get_openapi_schema' ) ) {
				$schema = $endpoint->get_openapi_schema();
				$documentation['paths'] = array_merge( $documentation['paths'], $schema );
			}
		}

		return $documentation;
	}
} 
