<?php
/**
 * Plugin Name:       FakerPress
 * Plugin URI:        https://fakerpress.com
 * Description:       FakerPress is a clean way to generate fake data to your WordPress installation, great for developers who need testing
 * Version:           0.7.3
 * Author:            Gustavo Bordoni
 * Author URI:        https://bordoni.me
 * Text Domain:       fakerpress
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/bordoni/fakerpress
 */

// Need to store this variable before leaving this file.
define( '__FP_FILE__', __FILE__ );

/**
 * Version compares to PHP 8.0, so we can use namespaces, anonymous functions
 * and a lot of packages require 8.0, so...
 */
if ( PHP_VERSION_ID < 80100 ) {
	if ( ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && is_admin() ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( plugin_basename( __FP_FILE__ ) ) ) {
			// Register activation hook to handle PHP version incompatibility.
			register_activation_hook(
				__FP_FILE__,
				static function () {
					// Deactivate the plugin immediately upon activation.
					deactivate_plugins( plugin_basename( __FP_FILE__ ) );
					
					// Use a user option to mark this for the current user.
					if ( function_exists( 'wp_get_current_user' ) && get_current_user_id() ) {
						// Get the plugin data to access version.
						$plugin_data = get_plugin_data( __FP_FILE__ );
						$version = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : 'generic';
						
						// Set a version-specific option with the error type.
						update_user_option( get_current_user_id(), "_fp_activation_error_{$version}", 'php_invalid', false );
					}
				}
			);
			return;
		}

		deactivate_plugins( __FP_FILE__ );
	}
} else {
	require_once dirname( __FP_FILE__ ) . '/src/functions/load.php';
	// Add a second action to handle the case where Common is not loaded, we still want to let the user know what is happening.
	add_action( 'plugins_loaded', 'fakerpress_load_plugin', 50 );
}

// Check for PHP version error flag and display notice.
add_action(
	'admin_notices',
	static function () {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}
		
		// Get the plugin data to access version.
		$plugin_data = get_plugin_data( __FP_FILE__ );
		$version = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : 'generic';
		
		// Check for any version-specific error.
		$option_name = "_fp_activation_error_{$version}";
		$error_type = get_user_option( $option_name, $user_id );
		
		if ( $error_type ) {
			// Clear the flag after displaying the notice.
			delete_user_option( $user_id, $option_name, false );
			
			if ( 'php_invalid' === $error_type ) {
				?>
				<div class="error">
					<p>
						<b>FakerPress</b> requires PHP 8.1 or higher, and the plugin has now disabled itself.
						<br />
						To allow better control over dates, advanced security improvements and performance gain.
						<br />
						Contact your Hosting or your system administrator and ask for this Upgrade to version 8.1 of PHP.
					</p>
				</div>
				<?php
			}
		}
	}
);
