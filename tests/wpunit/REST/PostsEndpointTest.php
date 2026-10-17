<?php
/**
 * Tests for the Posts generation REST endpoint.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Tests\Traits\REST_Test_Case;

/**
 * Class PostsEndpointTest
 *
 * Tests POST /fakerpress/v1/posts/generate (requires publish_posts).
 *
 * @since 0.9.0
 */
class PostsEndpointTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * The route being tested.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $route = '/fakerpress/v1/posts/generate';

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
	 * Verify that users without publish_posts are rejected.
	 *
	 * @test
	 */
	public function it_should_reject_users_without_publish_posts(): void {
		$this->set_user_with_role( 'subscriber' );

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Verify that posts can be generated with defaults.
	 *
	 * @test
	 */
	public function it_should_generate_posts_with_defaults(): void {
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
	 * Verify that the post_type parameter is accepted.
	 *
	 * @test
	 */
	public function it_should_accept_post_type_parameter(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'  => 1,
				'post_type' => 'page',
			]
		);

		$this->assert_success_response( $response );

		$data    = $response->get_data();
		$post_id = $data['data']['ids'][0];
		$post    = get_post( $post_id );

		$this->assertSame( 'page', $post->post_type );
	}

	/**
	 * Verify that the post_status parameter is accepted.
	 *
	 * @test
	 */
	public function it_should_accept_post_status_parameter(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'    => 1,
				'post_status' => 'draft',
			]
		);

		$this->assert_success_response( $response );

		$data    = $response->get_data();
		$post_id = $data['data']['ids'][0];
		$post    = get_post( $post_id );

		$this->assertSame( 'draft', $post->post_status );
	}

	/**
	 * Verify that the quantity parameter controls how many posts are generated.
	 *
	 * @test
	 */
	public function it_should_accept_quantity_parameter(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 3 ] );

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertSame( 3, $data['data']['generated'] );
		$this->assertCount( 3, $data['data']['ids'] );
	}

	/**
	 * Verify that generated IDs correspond to real WordPress posts.
	 *
	 * @test
	 */
	public function it_should_generate_actual_wordpress_posts(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 2 ] );
		$data     = $response->get_data();

		foreach ( $data['data']['ids'] as $post_id ) {
			$post = get_post( $post_id );
			$this->assertNotNull( $post, "Post ID {$post_id} should exist in the database." );
			$this->assertSame( 'post', $post->post_type );
		}
	}

	/**
	 * Verify that a `date` meta rule generates a post and a valid meta value
	 * without fataling on the missing Chronos import (wp.org bug report).
	 *
	 * @test
	 */
	public function it_should_generate_a_post_with_a_date_meta_field(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity' => 1,
				'meta'     => [
					[
						'type'     => 'date',
						'name'     => 'fp_test_date_meta',
						'interval' => [
							'min' => '2020-01-01',
							'max' => '2020-12-31',
						],
						'format'   => 'Y-m-d H:i:s',
						'weight'   => 100,
					],
				],
			]
		);

		$this->assert_success_response( $response );

		$data    = $response->get_data();
		$post_id = $data['data']['ids'][0];
		$value   = get_post_meta( $post_id, 'fp_test_date_meta', true );

		$this->assertNotEmpty( $value, 'Date meta value should be saved.' );
		$this->assertMatchesRegularExpression( '/^2020-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value );
	}

	/**
	 * Verify that the plural `post_types` parameter — the exact key the admin form
	 * submits — generates posts of the requested type. Regression for the wp.org
	 * report "Doesn't seem to create Pages now".
	 *
	 * @test
	 */
	public function it_should_generate_pages_when_post_types_plural_is_sent(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 3,
				'post_types' => 'page',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertCount( 3, $data['data']['ids'] );

		foreach ( $data['data']['ids'] as $post_id ) {
			$post = get_post( $post_id );
			$this->assertNotNull( $post );
			$this->assertSame( 'page', $post->post_type );
		}
	}

	/**
	 * Verify that a comma-separated `post_types` value (admin form serialization)
	 * samples across the requested types and never produces an unrequested type.
	 *
	 * @test
	 */
	public function it_should_accept_multiple_post_types_as_csv(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 6,
				'post_types' => 'page,post',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertCount( 6, $data['data']['ids'] );

		foreach ( $data['data']['ids'] as $post_id ) {
			$post = get_post( $post_id );
			$this->assertNotNull( $post );
			$this->assertContains( $post->post_type, [ 'page', 'post' ] );
		}
	}

	/**
	 * Verify that an array-shaped `post_types` (used by JS clients that send
	 * structured JSON) is also accepted.
	 *
	 * @test
	 */
	public function it_should_accept_post_types_as_array(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 2,
				'post_types' => [ 'page' ],
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		foreach ( $data['data']['ids'] as $post_id ) {
			$this->assertSame( 'page', get_post( $post_id )->post_type );
		}
	}

	/**
	 * Sending the plural `post_types` must take precedence over the singular
	 * `post_type` alias (whose schema default is `post`). This is the exact bug:
	 * the prior code overwrote `post_types` with `post_type`'s default and silently
	 * generated posts instead of pages.
	 *
	 * @test
	 */
	public function it_should_prefer_post_types_over_singular_alias(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 2,
				'post_types' => 'page',
				'post_type'  => 'post',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		foreach ( $data['data']['ids'] as $post_id ) {
			$this->assertSame( 'page', get_post( $post_id )->post_type );
		}
	}

	/**
	 * A sparse payload (no post_parent, html_tags, images_origin or post_types) must not emit a
	 * `explode(): Passing null` deprecation. Regression for the wp.org "critical error" report.
	 *
	 * @test
	 */
	public function it_should_not_emit_explode_deprecation_on_sparse_payload(): void {
		$this->set_admin_user();

		$deprecations = [];
		set_error_handler(
			static function ( $errno, $errstr ) use ( &$deprecations ) {
				if ( false !== strpos( $errstr, 'explode' ) || false !== strpos( $errstr, 'Passing null' ) ) {
					$deprecations[] = $errstr;
				}
				return true;
			},
			E_DEPRECATED
		);

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 2 ] );

		restore_error_handler();

		$this->assert_success_response( $response );
		$this->assertSame( [], $deprecations, 'No explode()/null deprecation should be emitted for a sparse request.' );
	}
}
