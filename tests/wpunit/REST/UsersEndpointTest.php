<?php
/**
 * Tests for the Users generation REST endpoint.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Tests\Traits\REST_Test_Case;

/**
 * Class UsersEndpointTest
 *
 * Tests POST /fakerpress/v1/users/generate (requires create_users).
 *
 * @since 0.9.0
 */
class UsersEndpointTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * The route being tested.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $route = '/fakerpress/v1/users/generate';

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
	 * Verify that the generate route is registered.
	 *
	 * @test
	 */
	public function it_should_register_the_generate_route(): void {
		$routes = $this->server->get_routes();

		$this->assertArrayHasKey( $this->route, $routes );
	}

	/**
	 * Verify that unauthenticated requests are rejected.
	 *
	 * @test
	 */
	public function it_should_reject_unauthenticated_requests(): void {
		wp_set_current_user( 0 );

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assert_error_response( $response, 'rest_not_logged_in', 401 );
	}

	/**
	 * Verify that users without create_users are rejected.
	 *
	 * @test
	 */
	public function it_should_reject_users_without_create_users(): void {
		$this->set_user_with_role( 'subscriber' );

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Verify that users can be generated with defaults.
	 *
	 * @test
	 */
	public function it_should_generate_users_with_defaults(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 2 ] );

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertSame( 2, $data['data']['generated'] );
		$this->assertCount( 2, $data['data']['ids'] );
	}

	/**
	 * Verify the correct response structure.
	 *
	 * @test
	 */
	public function it_should_return_correct_response_structure(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'success', $data );
		$this->assertArrayHasKey( 'data', $data );
		$this->assertArrayHasKey( 'message', $data );

		$this->assert_generation_response_structure( $data['data'] );
	}

	/**
	 * Verify that the role parameter is accepted.
	 *
	 * @test
	 */
	public function it_should_accept_role_parameter(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity' => 1,
				'role'     => 'editor',
			]
		);

		$this->assert_success_response( $response );

		$data    = $response->get_data();
		$user_id = $data['data']['ids'][0];
		$user    = get_userdata( $user_id );

		$this->assertNotFalse( $user );
		$this->assertContains( 'editor', $user->roles );
	}

	/**
	 * Verify that generated IDs correspond to real WordPress users.
	 *
	 * @test
	 */
	public function it_should_generate_actual_wordpress_users(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 2 ] );
		$data     = $response->get_data();

		foreach ( $data['data']['ids'] as $user_id ) {
			$user = get_userdata( $user_id );
			$this->assertNotFalse( $user, "User ID {$user_id} should exist in the database." );
		}
	}
}
