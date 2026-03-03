<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin\Menu;
use FakerPress\Plugin;
use FakerPress\Template;
use function FakerPress\get_request_var;
use function FakerPress\is_post_request;
use function FakerPress\make;

/**
 * Class Abstract View.
 *
 * @since 0.6.0
 *
 * @package FakerPress\Admin\View
 */
abstract class Abstract_View extends Template implements Interface_View {

	/**
	 * @inheritDoc
	 */
	abstract public static function get_slug(): string;

	/**
	 * @inheritDoc
	 */
	public static function get_menu_slug(): string {
		return static::get_slug();
	}

	/**
	 * @inheritDoc
	 */
	abstract public function get_label(): string;

	/**
	 * @inheritDoc
	 */
	abstract public function get_title(): string;

	/**
	 * @inheritDoc
	 */
	public function is_current_view(): bool {
		$page = get_request_var( 'page' );
		if ( Plugin::$slug !== $page ) {
			return false;
		}

		$view = get_request_var( 'view', Settings_View::get_slug() );
		if ( $view !== static::get_slug() ) {
			return false;
		}

		return true;
	}

	/**
	 * Register this particular view on the Admin menu.
	 *
	 * @since 0.6.0
	 * @since 0.7.1 - Moved to a public method register_menu with a hook.
	 */
	public function register_menu(): void {
		if ( ! $this->has_menu() ) {
			return;
		}

		make( Menu::class )->add(
			static::get_menu_slug(),
			$this->get_title(),
			$this->get_label(),
			$this->get_capability_required(),
			$this->get_menu_priority(),
			[ $this, 'render_view' ],
			$this->get_menu_parent()
		);
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function is_top_menu(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu_parent() {
		if ( $this->is_top_menu() ) {
			return null;
		}

		return Settings_View::get_menu_slug();
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu() {
		return make( Menu::class )->get( static::get_menu_slug() );
	}

	/**
	 * @inheritDoc
	 */
	public function get_capability_required(): string {
		return 'publish_posts';
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu_priority(): int {
		return 10;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_rendering_arguments(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function render_view(): string {
		// WordPress bug forces us to check if we are in a top view since unregistered menu items won't use the correct filtering method.
		if ( $this->is_top_menu() ) {
			$view = make( Factory::class )->get_current_view();

			// Only do anything in case we are in a diff view from the current one.
			if ( $view::get_slug() !== static::get_slug() ) {
				return $view->render_view();
			}
		}

		return $this->render( static::get_slug(), $this->get_rendering_arguments() );
	}

	/**
	 * @inheritDoc
	 */
	public function setup_template(): void {
		// Builds the template object for usage.
		$this->set_template_origin( make( Plugin::class ) )
			->set_template_folder( 'src/templates/pages' )
			->set_template_context_extract( true );
	}

	/**
	 * @inheritDoc
	 */
	public function hook(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'localize_page_config' ] );
	}

	/**
	 * Localize the React admin script with page configuration data.
	 *
	 * Each View subclass can override `get_page_data()` to provide
	 * page-specific data (taxonomies, post types, roles, etc.).
	 *
	 * @since 0.9.0
	 *
	 * @return void
	 */
	public function localize_page_config(): void {
		if ( ! $this->is_current_view() ) {
			return;
		}

		if ( ! wp_script_is( 'fakerpress-admin-react', 'registered' ) ) {
			return;
		}

		wp_localize_script(
			'fakerpress-admin-react',
			'fakerpressPageConfig',
			[
				'page'       => static::get_slug(),
				'restRoot'   => esc_url_raw( rest_url() ),
				'restNonce'  => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'ajaxNonces' => $this->get_ajax_nonces(),
				'data'       => $this->get_page_data(),
			] 
		);
	}

	/**
	 * Get AJAX nonces for this page.
	 *
	 * @since 0.9.0
	 *
	 * @return array<string, string> Nonce map keyed by action.
	 */
	protected function get_ajax_nonces(): array {
		return [
			'search_authors' => wp_create_nonce( Plugin::$slug . '-select2-search_authors' ),
			'search_terms'   => wp_create_nonce( Plugin::$slug . '-select2-search_terms' ),
			'wp_query'       => wp_create_nonce( Plugin::$slug . '-select2-WP_Query' ),
		];
	}

	/**
	 * Get page-specific data for the React interface.
	 *
	 * Subclasses should override this method to provide data
	 * such as taxonomies, post types, roles, etc.
	 *
	 * @since 0.9.0
	 *
	 * @return array<string, mixed> Page-specific data.
	 */
	protected function get_page_data(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function parse_request() {
		// When dealing with a GET request return true since a nonce check is not required.
		if ( ! is_post_request() ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . static::get_slug();

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}

		return true;
	}
}
