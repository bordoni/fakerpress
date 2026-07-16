<?php
/**
 * Tests for Attachment module admin-dependency loading.
 *
 * @since 0.9.2
 *
 * @package FakerPress\Tests\Module
 */

namespace FakerPress\Tests\Module;

use FakerPress\Module\Attachment;

/**
 * Class AttachmentDependenciesTest
 *
 * Regression coverage for issue #221: on WordPress 7.0 the single `download_url()` guard
 * skipped `media.php`, so `media_handle_sideload()` was undefined and attachment generation
 * fatalled. The loader must ensure every required admin include is available.
 *
 * @since 0.9.2
 */
class AttachmentDependenciesTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * The loader must make all three sideload dependencies available, not just `download_url()`.
	 *
	 * @test
	 */
	public function it_should_load_all_media_sideload_dependencies(): void {
		$module = new Attachment();
		$method = new \ReflectionMethod( $module, 'load_media_dependencies' );
		$method->setAccessible( true );

		$method->invoke( $module );

		$this->assertTrue( function_exists( 'download_url' ), 'download_url() should be loaded (wp-admin/includes/file.php).' );
		$this->assertTrue( function_exists( 'wp_read_image_metadata' ), 'wp_read_image_metadata() should be loaded (wp-admin/includes/image.php).' );
		$this->assertTrue( function_exists( 'media_handle_sideload' ), 'media_handle_sideload() should be loaded (wp-admin/includes/media.php).' );
	}

	/**
	 * Calling the loader when dependencies already exist must be a safe no-op (no redeclare fatal).
	 *
	 * @test
	 */
	public function it_should_be_idempotent(): void {
		$module = new Attachment();
		$method = new \ReflectionMethod( $module, 'load_media_dependencies' );
		$method->setAccessible( true );

		$method->invoke( $module );
		$method->invoke( $module );

		$this->assertTrue( function_exists( 'media_handle_sideload' ) );
	}
}
