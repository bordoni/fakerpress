<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Admin\Menu;
use FakerPress\Plugin;
use FakerPress\Template;
use function FakerPress\get_request_var;
use function FakerPress\is_post_request;
use function FakerPress\make;

/**
 * Class Abstract View.
 *
 * @since   0.6.0
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
	 */
	protected function register_menu(): void {
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
		$this->register_menu();
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
