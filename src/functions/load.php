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
	$plugin_dir = dirname( __FP_FILE__ );

	// Load function files that Plugin.php depends on before booting.
	require_once $plugin_dir . '/src/functions/container.php';
	require_once $plugin_dir . '/src/functions/date.php';
	require_once $plugin_dir . '/src/functions/variables.php';
	require_once $plugin_dir . '/src/functions/conditionals.php';
	require_once $plugin_dir . '/src/functions/sorting.php';
	require_once $plugin_dir . '/src/functions/assets.php';

	require_once $plugin_dir . '/src/FakerPress/Plugin.php';

	return FakerPress\Plugin::boot();
}
