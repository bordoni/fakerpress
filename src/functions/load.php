<?php
/**
 * Loads the whole FakerPress plugin.
 *
 * Please keep in mind that this is the only function that should be called from the main plugin file.
 * This function will load the plugin, all its dependencies, and it will boot the plugin.
 * Only function or class that is not namespaced.
 *
 * @since 0.6.0
 */
function fakerpress_load_plugin(): FakerPress\Plugin {
	require_once dirname( __FP_FILE__ ) . '/src/FakerPress/Plugin.php';
	return FakerPress\Plugin::boot();
}
