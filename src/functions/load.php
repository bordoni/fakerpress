<?php
namespace FakerPress;

use function \FakerPress\register_provider;

/**
 * Provides functions to handle the loading operations of the plugin.
 *
 * The functions are defined in the global namespace to allow easier loading in the main plugin file.
 *
 * @since 0.6.0
 */

/**
 * Register and load the service provider for loading the plugin.
 *
 * @since 0.6.0
 */
function load_plugin() {
	register_provider( Plugin::class );
}
