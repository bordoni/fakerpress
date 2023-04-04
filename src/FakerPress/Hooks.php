<?php
/**
 * Handles hooking all the actions and filters used by the module.
 *
 * To remove a filter:
 * remove_filter( 'some_filter', [ make( FakerPress\Hooks::class ), 'some_filtering_method' ] );
 *
 * To remove an action:
 * remove_action( 'some_action', [ make( FakerPress\Hooks::class ), 'some_method' ] );
 *
 * @since   0.6.0
 *
 * @package StellarWP\Jobvite
 */

namespace FakerPress;

use FakerPress\Admin\Menu;
use FakerPress\Admin\View\Factory as View_Factory;
use lucatume\DI52\ServiceProvider;

/**
 * Class Hooks.
 *
 * @since   0.6.0
 *
 * @package FakerPress
 */
class Hooks extends ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 0.6.0
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Plugin component.
	 *
	 * @since 0.6.0
	 */
	protected function add_actions() {
		add_action( 'fakerpress.plugin_loaded', [ $this, 'load_text_domain' ] );
		add_action( 'fakerpress.plugin_loaded', [ $this, 'load_admin' ] );

		$admin = make( Admin::class );
		$menu  = make( Admin\Menu::class );

		// From this point on we are doing hooks!
		add_action( 'admin_body_class', [ $admin, '_filter_body_class' ] );
		add_action( 'admin_notices', [ $admin, '_action_admin_notices' ] );
		add_action( 'fakerpress.view.request.settings', [ $admin, '_action_setup_settings_page' ] );

		add_action( 'admin_init', [ $this, 'parse_view_request' ], 15 );

		// When trying to add a menu, make bigger than the default to avoid conflicting index further on
		add_action( 'admin_menu', [ $menu, 'register_menus_to_wp' ], 11 );

		add_action( 'current_screen', [ $this, 'modify_current_screen' ], 0 );
	}

	/**
	 * Adds the filters required by each Plugin component.
	 *
	 * @since 0.6.0
	 */
	protected function add_filters() {
		$admin = make( Admin::class );

		// Setup the Submenu using an PHP action based on View
		add_filter( 'submenu_file', [ $this, 'filter_admin_submenu_file' ] );

		// Creating information for the plugin pages footer
		add_filter( 'admin_footer_text', [ $admin, '_filter_admin_footer_text' ] );
		add_filter( 'update_footer', [ $admin, '_filter_update_footer' ], 15 );

		add_filter( 'admin_title', [ $admin, '_filter_set_admin_page_title' ], 15, 2 );


		// Allow WordPress
		add_filter( 'fakerpress.messages.allowed_html', [ $admin, '_filter_messages_allowed_html' ], 1, 1 );
	}

	/**
	 * Loads the Administration classes.
	 *
	 * @since 0.6.0
	 */
	public function load_admin() {
		make( Admin::class );
		make( Ajax::class );
	}

	/**
	 * Filter the `$submenu_file` global right before WordPress builds the Administration Menu
	 *
	 * @since  0.6.0
	 *
	 * @param string $submenu_file Which is the current submenu file.
	 *
	 * @return string
	 */
	public function filter_admin_submenu_file( $submenu_file ) {
		return make( Admin\Menu::class )->filter_submenu_file( $submenu_file );
	}

	/**
	 * To allow internationalization for the errors strings the text domain is
	 * loaded in a 5.2 way, no Fatal Errors, only a message to the user.
	 *
	 * @since 0.6.0
	 *
	 * @return bool
	 */
	public function load_text_domain() {
		return load_plugin_textdomain( Plugin::$slug, false, Plugin::path( 'languages/' ) );
	}

	/**
	 * Parses the current view request for the admin views.
	 *
	 * @since 0.6.0
	 *
	 * @return mixed
	 */
	public function parse_view_request() {
		return make( View_Factory::class )->parse_current_view_request();
	}

	/**
	 * Sets the current screen on the administration properly for subviews.
	 *
	 * @since TBD
	 *
	 * @param \WP_Screen $screen
	 *
	 */
	public function modify_current_screen( $screen ) {
		// Removes itself since it's required to avoid infinitte loop.
		remove_action( 'current_screen', [ $this, 'modify_current_screen' ], 0 );

		make( Menu::class )->correctly_set_current_screen( $screen );
	}
}
