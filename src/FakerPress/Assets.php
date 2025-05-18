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

		// Register a global CSS files.
		register_asset(
			'fakerpress-icon',
			'font.css',
			[],
			'admin_enqueue_scripts',
			[
				'in_footer' => false,
			]
		);

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
			[ 'jquery', 'underscore', 'fakerpress-qs' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ $admin, 'is_active' ],
			]
		);
	}

}
