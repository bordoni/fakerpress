<?php

namespace FakerPress\Admin\View;

/**
 * Class View Interface.
 *
 * @since   0.6.0
 *
 * @package FakerPress\Admin\View
 */
interface Interface_View {
	/**
	 * Gets the Slug of this view, will also be used on the admin menu by default.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_slug(): string;

	/**
	 * Gets the Slug of this view, by default is the same as the view name.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_menu_slug(): string;

	/**
	 * Gets the label for this given view, will be used on the template.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_label(): string;

	/**
	 * The title of the page, used on the browser.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Determines if the current view object is the one we are currently on.
	 *
	 * @since 0.6.0
	 *
	 * @return bool
	 */
	public function is_current_view(): bool;

	/**
	 * Determines if a menu exists for this view.
	 *
	 * @since 0.6.0
	 *
	 * @return bool
	 */
	public function has_menu(): bool;

	/**
	 * Is this particular view a top menu?
	 *
	 * @since 0.6.0
	 *
	 * @return bool
	 */
	public function is_top_menu(): bool;

	/**
	 * Holds if this particular view has a parent item.
	 *
	 * @since 0.6.0
	 *
	 * @return string|null
	 */
	public function get_menu_parent();

	/**
	 * Gets the menu object used to access this view.
	 *
	 * @since 0.6.0
	 *
	 * @return object|null
	 */
	public function get_menu();

	/**
	 * Gets the capability required to see this view.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_capability_required(): string;

	/**
	 * Priority for this view on the admin menu.
	 *
	 * @since 0.6.0
	 *
	 * @return int
	 */
	public function get_menu_priority(): int;

	/**
	 * Renders this view using the slug as a base template name, it prints the Template.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function render_view(): string;

	/**
	 * Configures this object to handle template loading and rendering.
	 *
	 * @since 0.6.0
	 */
	public function setup_template(): void;

	/**
	 * Hooks the view related actions and filters on WP.
	 *
	 * @since 0.6.0
	 */
	public function hook(): void;

	/**
	 * Parse the request for all views, by default the abstract handles a nonce verification if it's a Post Request
	 * use if needed.
	 *
	 * @since 0.6.0
	 *
	 * @return bool
	 */
	public function parse_request();

}
