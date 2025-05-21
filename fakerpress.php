<?php
/**
 * Plugin Name:       FakerPress
 * Plugin URI:        https://fakerpress.com
 * Description:       FakerPress is a clean way to generate fake data to your WordPress installation, great for developers who need testing
 * Version:           0.8.0
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
			return register_activation_hook( __FP_FILE__, '_fp_handle_activation' );
		}

		_fp_handle_activation();
	}
} else {
	require_once dirname( __FP_FILE__ ) . '/src/functions/load.php';
	// Add a second action to handle the case where Common is not loaded, we still want to let the user know what is happening.
	add_action( 'plugins_loaded', 'fakerpress_load_plugin', 50 );
}

// Check for PHP version error flag and display notice.
add_action( 'admin_notices', '_fp_display_activation_notice' );

/**
 * Handles plugin activation and version incompatibility.
 *
 * @since 0.8.0
 *
 * @return void
 */
function _fp_handle_activation() {
	// Deactivate the plugin immediately upon activation.
	deactivate_plugins( plugin_basename( __FP_FILE__ ) );

	// Get the plugin data to access version.
	$plugin_data = get_plugin_data( __FP_FILE__, false, false );
	$version     = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : 'generic';
	
	// Set a version-specific option with the error type.
	update_option( "_fp_activation_error_{$version}", 'php_invalid' );
}

/**
 * Displays PHP version notice if needed.
 *
 * @since 0.8.0
 *
 * @return void
 */
function _fp_display_activation_notice() {
	if ( ! is_admin() ) {
		return;
	}
	
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	
	// Get the plugin data to access version.
	$plugin_data = get_plugin_data( __FP_FILE__, false, false );
	$version     = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : 'generic';
	
	// Check for any version-specific error.
	$option_name = "_fp_activation_error_{$version}";
	$error_type  = get_option( $option_name );
	
	if ( ! $error_type ) {
		return;
	}

	if ( 'php_invalid' !== $error_type ) {
		return;
	}
	?>
	<div class="error">
		<p>
			<?php
			printf(
				/* translators: %s: Plugin name */
				esc_html__( '%s requires PHP 8.1 or higher, and the plugin has now disabled itself.', 'fakerpress' ),
				'<b>FakerPress</b>'
			);
			?>
			<br />
			<?php esc_html_e( 'To allow better control over dates, advanced security improvements and performance gain.', 'fakerpress' ); ?>
			<br />
			<?php esc_html_e( 'Contact your Hosting or your system administrator and ask for this Upgrade to version 8.1 of PHP.', 'fakerpress' ); ?>
		</p>
	</div>
	<?php

	// Clear the flag after displaying the notice, only delete the flag if the notice was displayed.
	delete_option( $option_name );
}
