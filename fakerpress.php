<?php
/**
 * Plugin Name:       FakerPress
 * Plugin URI:        https://fakerpress.com
 * Description:       FakerPress is a clean way to generate fake data to your WordPress installation, great for developers who need testing
 * Version:           0.6.1
 * Author:            Gustavo Bordoni
 * Author URI:        http://bordoni.me
 * Text Domain:       fakerpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/bordoni/fakerpress
 */

// Need to store this variable before leaving this file
define( '__FP_FILE__', __FILE__ );

/**
 * Version compares to PHP 7.4, so we can use namespaces, anonymous functions
 * and a lot of packages require 7.4, so...
 */
if ( PHP_VERSION_ID < 70400 ) {
	if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_admin() ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! is_plugin_active( plugin_basename( __FP_FILE__ ) ) ) {
			wp_print_styles( 'open-sans' );
			echo "<style>body{margin: 0 2px;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.5em;}</style>";
			echo '<b>FakerPress</b> requires PHP 7.1 or higher, and the plugin has now disabled itself.' .
			     '<br />' .
			     'To allow better control over dates, advanced security improvements and performance gain.' .
			     '<br />' .
			     'Contact your Hosting or your system administrator and ask for this Upgrade to version 7.4 of PHP.';
			exit;
		}

		deactivate_plugins( __FP_FILE__ );
	}
} else {
	// Load Composer Vendor Modules
	require_once plugin_dir_path( __FP_FILE__ ) . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

	// Add a second action to handle the case where Common is not loaded, we still want to let the user know what is happening.
	add_action( 'plugins_loaded', '\FakerPress\load_plugin', 50 );
}
