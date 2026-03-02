<?php
/**
 * Tests for the Documentation REST endpoint.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Plugin;
use FakerPress\REST\Controller;
use FakerPress\Tests\Traits\REST_Test_Case;

/**
 * Class DocumentationEndpointTest
 *
 * Tests GET /fakerpress/v1/docs/openapi (public, no auth).
 *
 * @since 0.9.0
 */
class DocumentationEndpointTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * The route being tested.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $route = '/fakerpress/v1/docs/openapi';

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
	 * Verify that the OpenAPI route is registered.
	 *
	 * @test
	 */
	public function it_should_register_the_openapi_route(): void {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( $this->route, $routes );
	}

	/**
	 * Verify that the endpoint is accessible without authentication.
	 *
	 * @test
	 */
	public function it_should_be_accessible_without_authentication(): void {
		wp_set_current_user( 0 );

		$response = $this->dispatch_rest_request( 'GET', $this->route );

		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * Verify the response contains valid OpenAPI structure.
	 *
	 * @test
	 */
	public function it_should_return_valid_openapi_structure(): void {
		$response = $this->dispatch_rest_request( 'GET', $this->route );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'openapi', $data );
		$this->assertArrayHasKey( 'info', $data );
		$this->assertArrayHasKey( 'paths', $data );
		$this->assertArrayHasKey( 'components', $data );
	}

	/**
	 * Verify that info.version matches Plugin::VERSION.
	 *
	 * @test
	 */
	public function it_should_include_api_version_in_info(): void {
		$response = $this->dispatch_rest_request( 'GET', $this->route );
		$data     = $response->get_data();

		$this->assertSame( Plugin::VERSION, $data['info']['version'] );
	}

	/**
	 * Verify that all endpoint paths are included in the documentation.
	 *
	 * @test
	 */
	public function it_should_include_all_endpoint_paths(): void {
		$response = $this->dispatch_rest_request( 'GET', $this->route );
		$data     = $response->get_data();

		$expected_paths = [
			'/posts/generate',
			'/users/generate',
			'/terms/generate',
			'/comments/generate',
			'/attachments/generate',
		];

		foreach ( $expected_paths as $path ) {
			$this->assertArrayHasKey( $path, $data['paths'], "Path {$path} should be in the OpenAPI docs." );
		}
	}

	/**
	 * Verify that the response contains tags.
	 *
	 * @test
	 */
	public function it_should_include_tags(): void {
		$response = $this->dispatch_rest_request( 'GET', $this->route );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'tags', $data );
		$this->assertIsArray( $data['tags'] );
		$this->assertNotEmpty( $data['tags'] );
	}
}
