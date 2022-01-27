<?php
/**
 * Plugin Name:       FakerPress
 * Plugin URI:        https://fakerpress.com
 * Description:       FakerPress is a clean way to generate fake data to your WordPress installation, great for developers who need testing
 * Version:           0.5.2
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
 * To allow internationalization for the errors strings the text domain is
 * loaded in a 5.2 way, no Fatal Errors, only a message to the user.
 *
 * @return bool;
 */

function _fp_l10n() {
	// Doing that to use the real folder that the plugin is living, not a static string
	$plugin_folder = str_replace( DIRECTORY_SEPARATOR . basename( __FILE__ ), '', plugin_basename( __FP_FILE__ ) );
	return load_plugin_textdomain( 'fakerpress', false, $plugin_folder . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR );
}
add_action( 'plugins_loaded', '_fp_l10n' );

/**
 * Version compare to PHP 5.3, so we can use namespaces, anonymous functions
 * and a lot of packages require 5.3, so...
 *
 * For now 3.8 or bigger is needed for the admin interface, later on the
 * intention is to bring this number lower
 */
if ( PHP_VERSION_ID < 50600 ) {
	if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_admin() ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! is_plugin_active( plugin_basename( __FP_FILE__ ) ) ) {
			wp_print_styles( 'open-sans' );
			echo "<style>body{margin: 0 2px;font-family: 'Open Sans',sans-serif;font-size: 13px;line-height: 1.5em;}</style>";
			echo wp_kses_post( __( '<b>FakerPress</b> requires PHP 5.6 or higher, and the plugin has now disabled itself.', 'fakerpress' ) ) .
				'<br />' .
				esc_attr__( 'To allow better control over dates, advanced security improvements and performance gain.', 'fakerpress' ) .
				'<br />' .
				esc_attr__( 'Contact your Hosting or your system administrator and ask for this Upgrade to version 5.6 of PHP.', 'fakerpress' );
			exit;
		}

		deactivate_plugins( __FP_FILE__ );
	}
} else {
	require_once plugin_dir_path( __FP_FILE__ ) . 'load.php';
}
