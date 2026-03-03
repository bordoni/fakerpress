<?php
/**
 * Tests for Attachment module sanitization of external API data.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\Module
 */

namespace FakerPress\Tests\Module;

use FakerPress\Module\Attachment;

/**
 * Class AttachmentSanitizationTest
 *
 * Verifies that external API data (e.g., Lorem Picsum) is properly sanitized.
 *
 * @since 0.9.0
 */
class AttachmentSanitizationTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify that generate_attachment_data sanitizes author data from external API.
	 *
	 * @test
	 */
	public function it_should_sanitize_author_from_api_response(): void {
		$module = new Attachment();
		$method = new \ReflectionMethod( $module, 'generate_attachment_data' );
		$method->setAccessible( true );

		// Mock the HTTP response by using a filter on pre_http_request.
		$malicious_author = '<script>alert("xss")</script>Photographer';
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $malicious_author ) {
				if ( strpos( $url, 'picsum.photos' ) !== false && strpos( $url, '/info' ) !== false ) {
					return [
						'response' => [ 'code' => 200 ],
						'body'     => wp_json_encode( [ 'author' => $malicious_author ] ),
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );

		$request = [
			'provider'             => 'lorempicsum',
			'width'                => [ 800, 800 ],
			'height'               => 600,
			'aspect_ratio'         => null,
			'author_ids'           => [ $user_id ],
			'date_range'           => [
				'min' => '-1 year',
				'max' => 'now',
			],
			'generate_description' => false,
			'generate_caption'     => false,
			'generate_alt_text'    => false,
		];

		$data = $method->invoke( $module, $request );

		// The author should be sanitized — no HTML tags.
		if ( $data['image_author'] !== null ) {
			$this->assertStringNotContainsString( '<script>', $data['image_author'] );
			$this->assertStringNotContainsString( '</script>', $data['image_author'] );
			$this->assertSame( sanitize_text_field( $malicious_author ), $data['image_author'] );
		}
	}

	/**
	 * Verify that non-string author data from API is rejected.
	 *
	 * @test
	 */
	public function it_should_reject_non_string_author_data(): void {
		$module = new Attachment();
		$method = new \ReflectionMethod( $module, 'generate_attachment_data' );
		$method->setAccessible( true );

		// Mock the HTTP response with a non-string author.
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( strpos( $url, 'picsum.photos' ) !== false && strpos( $url, '/info' ) !== false ) {
					return [
						'response' => [ 'code' => 200 ],
						'body'     => wp_json_encode( [ 'author' => [ 'injected' => 'data' ] ] ),
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );

		$request = [
			'provider'             => 'lorempicsum',
			'width'                => [ 800, 800 ],
			'height'               => 600,
			'aspect_ratio'         => null,
			'author_ids'           => [ $user_id ],
			'date_range'           => [
				'min' => '-1 year',
				'max' => 'now',
			],
			'generate_description' => false,
			'generate_caption'     => false,
			'generate_alt_text'    => false,
		];

		$data = $method->invoke( $module, $request );

		// Non-string author should result in null (not used).
		$this->assertNull( $data['image_author'] );
	}
}
