<?php

namespace FakerPress\Tests\Admin;

use FakerPress\Assets;
use FakerPress\Plugin;
use function FakerPress\make;

/**
 * Tests for React admin bundle asset registration.
 *
 * @since 0.9.0
 */
class ReactAssetTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify that the React script handle is registered.
	 *
	 * @test
	 */
	public function it_should_register_react_script_handle(): void {
		$this->assertTrue( wp_script_is( 'fakerpress-admin-react', 'registered' ) );
	}

	/**
	 * Verify that the React styles handle is registered.
	 *
	 * @test
	 */
	public function it_should_register_react_styles_handle(): void {
		$this->assertTrue( wp_style_is( 'fakerpress-admin-react-styles', 'registered' ) );
	}

	/**
	 * Verify that the asset file exists and contains expected keys.
	 *
	 * @test
	 */
	public function it_should_have_valid_asset_file(): void {
		$asset_path = Plugin::path( 'build/admin.asset.php' );
		$this->assertFileExists( $asset_path );

		$asset = require $asset_path;
		$this->assertArrayHasKey( 'dependencies', $asset );
		$this->assertArrayHasKey( 'version', $asset );
		$this->assertIsArray( $asset['dependencies'] );
		$this->assertIsString( $asset['version'] );
	}

	/**
	 * Verify that the React script includes expected WordPress dependencies.
	 *
	 * @test
	 */
	public function it_should_include_wp_dependencies(): void {
		$asset_path = Plugin::path( 'build/admin.asset.php' );

		if ( ! file_exists( $asset_path ) ) {
			$this->markTestSkipped( 'Build assets not found. Run bun run build first.' );
		}

		$asset = require $asset_path;
		$deps  = $asset['dependencies'];

		// React bundle should depend on wp-i18n and wp-api-fetch.
		$this->assertContains( 'wp-i18n', $deps );
		$this->assertContains( 'wp-api-fetch', $deps );
	}

	/**
	 * Verify that the React script URL points to the correct file.
	 *
	 * @test
	 */
	public function it_should_point_to_correct_script_url(): void {
		$scripts = wp_scripts();
		$handle  = 'fakerpress-admin-react';

		if ( ! isset( $scripts->registered[ $handle ] ) ) {
			$this->markTestSkipped( 'React script not registered.' );
		}

		$src = $scripts->registered[ $handle ]->src;
		$this->assertStringContainsString( 'build/admin.js', $src );
	}
}
