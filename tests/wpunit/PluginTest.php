<?php

namespace FakerPress\Tests;

use FakerPress\Plugin;

/**
 * Smoke tests to verify the plugin boots correctly.
 *
 * @since 0.9.0
 */
class PluginTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify that the Plugin::VERSION constant is defined and non-empty.
	 *
	 * @test
	 */
	public function it_should_have_a_version(): void {
		$this->assertNotEmpty( Plugin::VERSION );
	}

	/**
	 * Verify that the plugin has been booted.
	 *
	 * @test
	 */
	public function it_should_boot(): void {
		$plugin = \FakerPress\make( Plugin::class );
		$this->assertInstanceOf( Plugin::class, $plugin );
	}

	/**
	 * Verify that plugin paths are set correctly.
	 *
	 * @test
	 */
	public function it_should_set_plugin_paths(): void {
		$this->assertNotEmpty( Plugin::path() );
		$this->assertNotEmpty( Plugin::url() );
		$this->assertDirectoryExists( Plugin::path() );
	}
}
