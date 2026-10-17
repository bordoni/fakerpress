<?php
/**
 * Tests for the Attachment module's IRI-safe HTTP helpers.
 *
 * @since 0.9.2
 *
 * @package FakerPress\Tests\Module
 */

namespace FakerPress\Tests\Module;

use FakerPress\Module\Attachment;

/**
 * Class AttachmentRemoteTest
 *
 * Regression coverage for issue #188: external image requests logged a PHP 8.1+
 * "Using null as an array offset" deprecation from WordPress core's Requests/Iri.php.
 * FakerPress routes those requests through helpers that suppress E_DEPRECATED and restore
 * the previous error-reporting level afterwards.
 *
 * @since 0.9.2
 */
class AttachmentRemoteTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Invoke a protected method on a fresh Attachment instance.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments.
	 *
	 * @return mixed
	 */
	private function invoke( string $method, array $args ) {
		$module     = new Attachment();
		$reflection = new \ReflectionMethod( $module, $method );
		$reflection->setAccessible( true );

		return $reflection->invokeArgs( $module, $args );
	}

	/**
	 * The wrapper must clear E_DEPRECATED inside the callback and restore the level afterwards.
	 *
	 * @test
	 */
	public function it_should_suppress_deprecations_inside_and_restore_after(): void {
		$original     = error_reporting();
		$level_inside = null;

		$result = $this->invoke(
			'without_iri_deprecations',
			[
				static function () use ( &$level_inside ) {
					$level_inside = error_reporting();
					return 'done';
				},
			]
		);

		$this->assertSame( 'done', $result, 'The callback return value should pass through.' );
		$this->assertSame( 0, $level_inside & E_DEPRECATED, 'E_DEPRECATED should be disabled inside the callback.' );
		$this->assertSame( $original, error_reporting(), 'The error-reporting level should be restored afterwards.' );
	}

	/**
	 * The error-reporting level must be restored even when the callback throws.
	 *
	 * @test
	 */
	public function it_should_restore_error_reporting_even_when_the_callback_throws(): void {
		$original = error_reporting();

		try {
			$this->invoke(
				'without_iri_deprecations',
				[
					static function () {
						throw new \RuntimeException( 'boom' );
					},
				]
			);
			$this->fail( 'The exception should propagate.' );
		} catch ( \RuntimeException $e ) {
			$this->assertSame( 'boom', $e->getMessage() );
		}

		$this->assertSame( $original, error_reporting(), 'The error-reporting level should be restored after an exception.' );
	}

	/**
	 * safe_remote_get() should still perform the GET and restore the error-reporting level.
	 *
	 * @test
	 */
	public function it_should_perform_the_request_and_restore_error_reporting(): void {
		$original = error_reporting();

		add_filter(
			'pre_http_request',
			static function ( $preempt, $args, $url ) {
				if ( false !== strpos( $url, 'picsum.photos' ) ) {
					return [
						'response' => [ 'code' => 200 ],
						'body'     => wp_json_encode( [ 'author' => 'Test Author' ] ),
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$response = $this->invoke( 'safe_remote_get', [ 'https://picsum.photos/id/1/info', [ 'timeout' => 5 ] ] );

		$this->assertIsArray( $response );
		$this->assertSame( 200, wp_remote_retrieve_response_code( $response ) );
		$this->assertSame( $original, error_reporting(), 'The error-reporting level should be restored after the request.' );
	}
}
