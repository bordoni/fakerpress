<?php
/**
 * Tests for the REST Controller service provider.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Contracts\Container;
use FakerPress\REST\Controller;
use FakerPress\REST\Abstract_Endpoint;
use FakerPress\Tests\Traits\REST_Test_Case;

use function FakerPress\make;

/**
 * Class ControllerTest
 *
 * @since 0.9.0
 */
class ControllerTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * Set up each test.
	 *
	 * @since 0.9.0
	 */
	public function set_up(): void {
		parent::set_up();
		$this->init_rest_server();
	}

	/**
	 * Verify that the Controller is registered as a singleton in the container.
	 *
	 * @test
	 */
	public function it_should_be_registered_as_singleton(): void {
		$instance_a = make( Controller::class );
		$instance_b = make( Controller::class );

		$this->assertSame( $instance_a, $instance_b );
	}

	/**
	 * Verify that the namespace constant is correct.
	 *
	 * @test
	 */
	public function it_should_return_correct_namespace(): void {
		$this->assertSame( 'fakerpress/v1', Controller::get_namespace() );
	}

	/**
	 * Verify that all 6 default endpoints are loaded.
	 *
	 * @test
	 */
	public function it_should_load_all_default_endpoints(): void {
		$controller = make( Controller::class );
		$endpoints  = $controller->get_endpoints();

		$this->assertCount( 6, $endpoints );

		foreach ( $endpoints as $endpoint ) {
			$this->assertInstanceOf( Abstract_Endpoint::class, $endpoint );
		}
	}

	/**
	 * Verify that routes for all endpoints are registered on rest_api_init.
	 *
	 * @test
	 */
	public function it_should_register_routes_on_rest_api_init(): void {
		$routes = $this->server->get_routes();

		$expected_routes = [
			'/fakerpress/v1/docs/openapi',
			'/fakerpress/v1/posts/generate',
			'/fakerpress/v1/users/generate',
			'/fakerpress/v1/terms/generate',
			'/fakerpress/v1/comments/generate',
			'/fakerpress/v1/attachments/generate',
		];

		foreach ( $expected_routes as $route ) {
			$this->assertArrayHasKey( $route, $routes, "Route {$route} should be registered." );
		}
	}

	/**
	 * Verify that OpenAPI documentation structure is correct.
	 *
	 * @test
	 */
	public function it_should_generate_openapi_documentation(): void {
		$controller    = make( Controller::class );
		$documentation = $controller->get_openapi_documentation();

		$this->assertArrayHasKey( 'openapi', $documentation );
		$this->assertArrayHasKey( 'info', $documentation );
		$this->assertArrayHasKey( 'paths', $documentation );
	}

	/**
	 * Verify that the fakerpress_rest_endpoint_classes filter works.
	 *
	 * @test
	 */
	public function it_should_allow_filtering_endpoint_classes(): void {
		// Force a fresh Controller instance for this test.
		$controller = new Controller( Container::init() );

		add_filter(
			'fakerpress_rest_endpoint_classes',
			static function () {
				return [];
			}
		);

		$endpoints = $controller->get_endpoints();

		$this->assertCount( 0, $endpoints );

		// Clean up the filter.
		remove_all_filters( 'fakerpress_rest_endpoint_classes' );
	}
}
