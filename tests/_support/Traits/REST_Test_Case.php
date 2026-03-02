<?php
/**
 * Shared REST API test helpers for FakerPress endpoint tests.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests
 */

namespace FakerPress\Tests\Traits;

use WP_REST_Request;
use WP_REST_Response;

/**
 * Trait REST_Test_Case
 *
 * Provides common helpers reused across all REST endpoint tests.
 *
 * @since 0.9.0
 */
trait REST_Test_Case {

	/**
	 * The REST server instance.
	 *
	 * @since 0.9.0
	 *
	 * @var \WP_REST_Server
	 */
	protected $server;

	/**
	 * Initialize the REST server for testing.
	 *
	 * Should be called in set_up().
	 *
	 * @since 0.9.0
	 */
	protected function init_rest_server(): void {
		/** @var \WP_REST_Server $wp_rest_server */
		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();
		$this->server   = $wp_rest_server;

		do_action( 'rest_api_init' );
	}

	/**
	 * Dispatch a REST request in-process.
	 *
	 * @since 0.9.0
	 *
	 * @param string $method HTTP method (GET, POST, etc.).
	 * @param string $route  Full route path including namespace.
	 * @param array  $params Request body parameters.
	 *
	 * @return WP_REST_Response
	 */
	protected function dispatch_rest_request( string $method, string $route, array $params = [] ): WP_REST_Response {
		$request = new WP_REST_Request( $method, $route );

		if ( ! empty( $params ) ) {
			$request->set_body_params( $params );
		}

		return rest_get_server()->dispatch( $request );
	}

	/**
	 * Create and set an administrator as the current user.
	 *
	 * @since 0.9.0
	 *
	 * @return int The admin user ID.
	 */
	protected function set_admin_user(): int {
		$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		return $user_id;
	}

	/**
	 * Create and set a user with the given role as the current user.
	 *
	 * @since 0.9.0
	 *
	 * @param string $role WordPress role name.
	 *
	 * @return int The user ID.
	 */
	protected function set_user_with_role( string $role ): int {
		$user_id = static::factory()->user->create( [ 'role' => $role ] );
		wp_set_current_user( $user_id );

		return $user_id;
	}

	/**
	 * Assert a successful REST response.
	 *
	 * @since 0.9.0
	 *
	 * @param WP_REST_Response $response        The REST response.
	 * @param int              $expected_status  Expected HTTP status code.
	 */
	protected function assert_success_response( WP_REST_Response $response, int $expected_status = 200 ): void {
		$this->assertSame( $expected_status, $response->get_status() );

		$data = $response->get_data();
		$this->assertTrue( $data['success'] );
		$this->assertArrayHasKey( 'data', $data );
	}

	/**
	 * Assert an error REST response.
	 *
	 * @since 0.9.0
	 *
	 * @param WP_REST_Response $response        The REST response.
	 * @param string           $expected_code   Expected error code.
	 * @param int              $expected_status  Expected HTTP status code.
	 */
	protected function assert_error_response( WP_REST_Response $response, string $expected_code, int $expected_status ): void {
		$this->assertSame( $expected_status, $response->get_status() );

		$data = $response->get_data();

		if ( isset( $data['success'] ) ) {
			$this->assertFalse( $data['success'] );
		}

		$this->assertSame( $expected_code, $data['code'] );
	}

	/**
	 * Assert the standard generation response structure.
	 *
	 * @since 0.9.0
	 *
	 * @param array $data The response data array.
	 */
	protected function assert_generation_response_structure( array $data ): void {
		$this->assertArrayHasKey( 'generated', $data );
		$this->assertArrayHasKey( 'ids', $data );
		$this->assertArrayHasKey( 'links', $data );
		$this->assertArrayHasKey( 'time', $data );

		$this->assertIsInt( $data['generated'] );
		$this->assertIsArray( $data['ids'] );
		$this->assertIsArray( $data['links'] );
		$this->assertIsFloat( $data['time'] );
	}
}
