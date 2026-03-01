<?php

namespace FakerPress;

use FakerPress\Contracts\Service_Provider;

/**
 * Class Assets.
 *
 * @since   0.6.4
 *
 * @package StellarWP\Jobvite
 */
class Assets extends Service_Provider {

	public function register() {
		singleton( static::class, $this );

		$admin = make( Admin::class );

		// Font icon CSS removed - now using SVG icon for admin menu

		// Register QS.js.
		register_asset(
			'fakerpress-qs',
			'qs.js',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);

		// Register Vendor Select2.
		register_asset(
			'fakerpress-select2-styles',
			'select2.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);
		register_asset(
			'fakerpress-select2-wordpress',
			'select2-wordpress.css',
			[ 'fakerpress-select2-styles' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);

		register_asset(
			'fakerpress-select2',
			'select2.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);

		// Register DatePicker Skins.
		register_asset(
			'fakerpress-jquery-ui',
			'jquery-ui.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);
		register_asset(
			'fakerpress-datepicker',
			'datepicker.css',
			[ 'fakerpress-jquery-ui' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);

		// Register the plugin CSS files.
		register_asset(
			'fakerpress-admin',
			'admin.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);
		register_asset(
			'fakerpress-messages',
			'messages.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);

		// Register the plugin JS files.
		register_asset(
			'fakerpress-fields',
			'fields.js',
			[ 'jquery', 'underscore', 'fakerpress-select2', 'jquery-ui-datepicker', 'fakerpress-module' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);
		register_asset(
			'fakerpress-module',
			'module.js',
			[ 'jquery', 'underscore', 'fakerpress-qs', 'wp-api-request', 'wp-i18n' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
				'localize' => [
					'name' => 'fakerpressRestApi',
					'data' => [ $this, 'get_rest_localization_data' ],
				],
			]
		);

		// Set up script translations.
		add_action( 'admin_enqueue_scripts', [ $this, 'setup_script_translations' ], 20 );
	}

	/**
	 * Get REST API localization data for the module script.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function get_rest_localization_data() {
		return [
			'root'  => esc_url_raw( rest_url() ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		];
	}

	/**
	 * Set up script translations for FakerPress scripts.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function setup_script_translations() {
		// Only set up translations if the script is enqueued.
		if ( wp_script_is( 'fakerpress-module', 'enqueued' ) ) {
			wp_set_script_translations( 'fakerpress-module', 'fakerpress' );
		}
	}

}
