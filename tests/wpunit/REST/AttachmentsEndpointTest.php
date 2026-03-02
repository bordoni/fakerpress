<?php
/**
 * Tests for the Attachments generation REST endpoint.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Tests\Traits\REST_Test_Case;

/**
 * Class AttachmentsEndpointTest
 *
 * Tests POST /fakerpress/v1/attachments/generate (requires upload_files).
 *
 * HTTP requests for image downloads are mocked via the pre_http_request filter
 * to avoid external network dependencies.
 *
 * @since 0.9.0
 */
class AttachmentsEndpointTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * The route being tested.
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $route = '/fakerpress/v1/attachments/generate';

	/**
	 * Minimal valid JPEG binary (1x1 pixel).
	 *
	 * @since 0.9.0
	 *
	 * @var string
	 */
	private string $fake_jpeg;

	/**
	 * Set up each test.
	 *
	 * @since 0.9.0
	 */
	public function set_up(): void {
		parent::set_up();
		$this->init_rest_server();

		// Minimal 1x1 JPEG binary.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$this->fake_jpeg = base64_decode(
			'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'
			. 'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwh'
			. 'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAAR'
			. 'CAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAA'
			. 'AAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMR'
			. 'AD8AKwA//9k='
		);

		// Mock all HTTP requests to return a fake JPEG image.
		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * Tear down each test.
	 *
	 * @since 0.9.0
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10 );
		parent::tear_down();
	}

	/**
	 * Mock HTTP requests to return a fake JPEG response.
	 *
	 * @since 0.9.0
	 *
	 * @param false|array $preempt  Whether to preempt the request.
	 * @param array       $args     HTTP request arguments.
	 * @param string      $url      The request URL.
	 *
	 * @return array Fake HTTP response.
	 */
	public function mock_http_request( $preempt, $args, $url ): array {
		// When download_url() uses stream mode, write the body to the temp file
		// since the HTTP transport is bypassed by pre_http_request.
		if ( ! empty( $args['filename'] ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $args['filename'], $this->fake_jpeg );
		}

		return [
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'headers'  => [
				'content-type'   => 'image/jpeg',
				'content-length' => strlen( $this->fake_jpeg ),
			],
			'body'     => $this->fake_jpeg,
			'filename' => $args['filename'] ?? '',
		];
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
	 * Verify that users without upload_files are rejected.
	 *
	 * @test
	 */
	public function it_should_reject_users_without_upload_files(): void {
		$this->set_user_with_role( 'subscriber' );

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assertSame( 403, $response->get_status() );
	}

	/**
	 * Verify that attachments can be generated with mocked HTTP.
	 *
	 * @test
	 */
	public function it_should_generate_attachments_with_defaults(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );

		$this->assert_success_response( $response );

		$data = $response->get_data();
		$this->assertSame( 1, $data['data']['generated'] );
		$this->assertCount( 1, $data['data']['ids'] );
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
	 * Verify that generated IDs correspond to real WordPress attachments.
	 *
	 * @test
	 */
	public function it_should_generate_actual_wordpress_attachments(): void {
		$this->set_admin_user();

		$response = $this->dispatch_rest_request( 'POST', $this->route, [ 'quantity' => 1 ] );
		$data     = $response->get_data();

		foreach ( $data['data']['ids'] as $attachment_id ) {
			$post = get_post( $attachment_id );
			$this->assertNotNull( $post, "Attachment ID {$attachment_id} should exist in the database." );
			$this->assertSame( 'attachment', $post->post_type );
		}
	}
}
