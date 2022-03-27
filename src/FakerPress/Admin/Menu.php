<?php

namespace FakerPress\Admin;

use FakerPress\Admin\View\Factory as View_Factory;
use function FakerPress\get_request_var;
use function FakerPress\make;

use FakerPress\Plugin;
use FakerPress\Admin;

/**
 * Class Menu
 *
 * @since TBD
 *
 */
class Menu {
	/**
	 * Variable holding the submenus objects
	 *
	 * @since 0.6.0
	 *
	 * @var array
	 */
	protected static $items = [];

	/**
	 * Determines if the list of menus needs sorting.
	 *
	 * @since 0.6.0
	 *
	 * @var bool
	 */
	protected static $needs_sorting = false;

	/**
	 * Creating submenus easier to be extent from outside
	 *
	 * @since 0.6.0
	 *
	 * @param string        $slug       Translatable string for the title of the page
	 * @param string        $title      Translatable string that will go on the menu
	 * @param string        $label      Translatable string that will go on the menu
	 * @param string        $capability WordPress capability that will be required to access this view
	 * @param integer       $priority   The priority to show this submenu
	 * @param callable|null $callback   A callback to render this menu item.
	 * @param string|null   $parent     What is the parent menu.
	 *
	 */
	public static function add( string $slug, $title, $label, string $capability = 'manage_options', int $priority = 10, $callback = null, $parent = null ): void {
		$slug = sanitize_key( $slug );

		static::$items[ $slug ] = $menu = (object) [
			'slug'       => $slug,
			'title'      => esc_attr( $title ),
			'hook'       => null, // Will be set later from WP.
			'label'      => esc_attr( $label ),
			'capability' => sanitize_key( $capability ),
			'priority'   => absint( $priority ),// Used to set up error page.
			'callback'   => $callback, // Used to set up error page.
			'parent'     => $parent, // Used to set up error page.
		];

		if ( ! is_callable( $menu->callback ) ) {
			$menu->callback = static function () use ( $menu ) {
				$menu = make( Menu::class )->get_current();

				return make( Admin::class )->render( $menu->slug, [ 'view' => $menu ] );
			};
		}

		static::$needs_sorting = true;
	}

	/**
	 * Given a slug updates that menu object with the new variables.
	 *
	 * @since 0.6.0
	 *
	 * @param string $slug
	 * @param array  $update_with
	 *
	 * @return bool
	 */
	public function update( string $slug, array $update_with ) {
		$menu = $this->get( $slug );

		if ( empty( $menu ) ) {
			return false;
		}

		$menu = (object) array_merge( (array) $menu, (array) $update_with );

		static::$items[ $slug ] = $menu;

		return true;
	}

	/**
	 * Gets all the menu items sorted by their priority.
	 *
	 * @since 0.6.0
	 *
	 * @return array
	 */
	public function get_all(): array {
		if ( static::$needs_sorting ) {
			uasort( static::$items, 'FakerPress\sort_by_priority' );
			static::$needs_sorting = false;
		}

		return static::$items;
	}

	/**
	 * Gets a specific item from the menu, using its slug.
	 *
	 * @since 0.6.0
	 *
	 * @param string $slug
	 *
	 * @return object|null
	 */
	public function get( string $slug ) {
		$all = $this->get_all();

		return $all[ $slug ] ?? null;
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
	public function filter_submenu_file( $submenu_file ) {
		if ( ! make( Admin::class )->is_active() ) {
			return $submenu_file;
		}

		$view = make( Admin\View\Factory::class )->get_current_view();

		if (
			0 !== $view->get_menu_priority()
		) {
			$submenu_file = Plugin::$slug . '&view=' . $view::get_slug();
		}

		return $submenu_file;
	}

	/**
	 * Method triggered to add the menu to WordPress administration
	 *
	 * @since 0.6.0
	 *
	 * @uses  \FakerPress\Plugin::$slug
	 * @uses  esc_attr__
	 * @uses  add_menu_page
	 * @uses  add_submenu_page
	 * @uses  current_user_can
	 *
	 * @return null Actions do not return
	 */
	public function register_menus_to_wp() {
		foreach ( $this->get_all() as $menu ) {
			if ( ! current_user_can( $menu->capability ) ) {
				continue;
			}

			$update = [];

			if ( empty( $menu->parent ) ) {
				$update['hook'] = add_menu_page(
					$menu->title,
					$menu->label,
					$menu->capability,
					$menu->slug,
					$menu->callback,
					'none'
				);
			} else {
				$update['hook'] = add_submenu_page(
					$menu->parent,
					$menu->title,
					$menu->label,
					$menu->capability,
					"{$menu->parent}&view={$menu->slug}",
					$menu->callback // The submenus will likely never be called due to howe register.
				);
			}

			$this->update( $menu->slug, $update );
		}

		// Change the Default Submenu for FakerPress menus
		if ( ! empty( $GLOBALS['submenu'][ Plugin::$slug ] ) ) {
			$GLOBALS['submenu'][ Plugin::$slug ][0][0] = esc_attr__( 'Settings', 'fakerpress' );
		}
	}

	/**
	 * Properly sets the current screen, this is a hacky solution because of how poorly WordPress handles Admin menus not
	 * using the page param.
	 *
	 * @since TBD
	 *
	 * @param \WP_Screen $screen Which screen are we in?
	 *
	 * @return void
	 */
	public function correctly_set_current_screen( $screen ) {
		$view = make( View_Factory::class )->get_current_view();
		if ( ! $view ) {
			return;
		}

		if ( ! $view->has_menu() ) {
			return;
		}

		$menu = $view->get_menu();

		set_current_screen( $menu->hook );

		global $page_hook;

		$page_hook = $menu->hook;
	}
}
