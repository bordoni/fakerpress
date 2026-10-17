<?php
/**
 * Tests for the Terms generation REST endpoint.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Tests\Traits\REST_Test_Case;

/**
 * Class TermsEndpointTest
 *
 * Tests POST /fakerpress/v1/terms/generate (requires manage_categories).
 *
 * @since 0.9.0
 */
class TermsEndpointTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * The route being tested.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $route = '/fakerpress/v1/terms/generate';

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
	 * Verify that users without manage_categories are rejected.
	 *
	 * @test
	 */
	public function it_should_reject_users_without_manage_categories(): void {
		$this->set_user_with_role( 'subscriber' );

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Verify that terms can be generated with defaults.
	 *
	 * @test
	 */
	public function it_should_generate_terms_with_defaults(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 3 ] );

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertSame( 3, $data['data']['generated'] );
		$this->assertCount( 3, $data['data']['ids'] );
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
	 * Verify that the taxonomy parameter is accepted.
	 *
	 * @test
	 */
	public function it_should_accept_taxonomy_parameter(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity' => 1,
				'taxonomy' => 'post_tag',
			]
		);

		$this->assert_success_response( $response );

		$data    = $response->get_data();
		$term_id = $data['data']['ids'][0];
		$term    = get_term( $term_id );

		$this->assertNotNull( $term );
		$this->assertNotInstanceOf( \WP_Error::class, $term );
		$this->assertSame( 'post_tag', $term->taxonomy );
	}

	/**
	 * Verify that generated IDs correspond to real WordPress terms.
	 *
	 * @test
	 */
	public function it_should_generate_actual_wordpress_terms(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 2 ] );
		$data     = $response->get_data();

		foreach ( $data['data']['ids'] as $term_id ) {
			$term = get_term( $term_id );
			$this->assertNotNull( $term, "Term ID {$term_id} should exist in the database." );
			$this->assertNotInstanceOf( \WP_Error::class, $term );
		}
	}

	/**
	 * Verify that the plural `taxonomies` parameter — the exact key the admin form submits —
	 * generates terms in the requested taxonomy. Regression for #218 ("Tags not being generated":
	 * the singular alias's `category` default was overwriting the selected taxonomy).
	 *
	 * @test
	 */
	public function it_should_generate_tags_when_taxonomies_plural_is_sent(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 3,
				'taxonomies' => 'post_tag',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertCount( 3, $data['data']['ids'] );

		foreach ( $data['data']['ids'] as $term_id ) {
			$term = get_term( $term_id );
			$this->assertNotInstanceOf( \WP_Error::class, $term );
			$this->assertSame( 'post_tag', $term->taxonomy );
		}
	}

	/**
	 * Verify that the plural `taxonomies` value wins over the singular `taxonomy` alias, so the
	 * alias can never silently force terms into `category`.
	 *
	 * @test
	 */
	public function it_should_prefer_taxonomies_over_singular_alias(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 3,
				'taxonomies' => 'post_tag',
				'taxonomy'   => 'category',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		foreach ( $data['data']['ids'] as $term_id ) {
			$term = get_term( $term_id );
			$this->assertNotInstanceOf( \WP_Error::class, $term );
			$this->assertSame( 'post_tag', $term->taxonomy );
		}
	}

	/**
	 * Verify that a comma-separated `taxonomies` value (admin form serialization) samples across
	 * the requested taxonomies and never produces an unrequested one.
	 *
	 * @test
	 */
	public function it_should_accept_multiple_taxonomies_as_csv(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request(
			'POST',
			$this->route,
			[
				'quantity'   => 6,
				'taxonomies' => 'category,post_tag',
			]
		);

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertCount( 6, $data['data']['ids'] );

		foreach ( $data['data']['ids'] as $term_id ) {
			$term = get_term( $term_id );
			$this->assertNotInstanceOf( \WP_Error::class, $term );
			$this->assertContains( $term->taxonomy, [ 'category', 'post_tag' ] );
		}
	}
}
