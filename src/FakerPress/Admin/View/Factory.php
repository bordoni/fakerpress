<?php

namespace FakerPress\Admin\View;

use FakerPress\Plugin;
use lucatume\DI52\ServiceProvider;
use function FakerPress\get_request_var;

/**
 * Class Factory for Admin Views.
 *
 * @since   0.6.0
 *
 * @package FakerPress\Admin\View
 */
class Factory extends ServiceProvider {
	/**
	 * Store the views that were initialized.
	 *
	 * @since 0.6.0
	 *
	 * @var array
	 */
	protected $views = [];

	/**
	 * Fetches all admin views used by FakerPress
	 *
	 * @since 0.6.0
	 *
	 * @return Abstract_View[]
	 */
	public function get_all(): array {
		if ( empty( $this->views ) ) {
			$views_classes = [
				Attachment_View::class,
				Comment_View::class,
				Post_View::class,
				Settings_View::class,
				Error_View::class,
				Changelog_View::class,
				Term_View::class,
				User_View::class,
			];
			foreach ( $views_classes as $view_class ) {
				$this->container->singleton( $view_class, $view_class, [ 'setup_template', 'hook' ] );
				$this->views[] = $this->container->make( $view_class );
			}
		}

		/**
		 * Allows the filtering of the FakerPress available modules.
		 *
		 * @since 0.6.0
		 *
		 * @param Abstract_View[] $views Which modules are available.
		 */
		return apply_filters( 'fakerpress.admin.views', $this->views );
	}

	/**
	 * Register all the Admin Views as Singletons and initializes them.
	 *
	 * @since 0.6.0
	 */
	public function register() {
		// Register the provider as a singleton.
		$this->container->singleton( static::class, $this );

		// When fetching all items it will initialize.
		$this->get_all();
	}

	/**
	 * Fetches the current FakerPress view.
	 *
	 * @since TBD
	 *
	 * @return Abstract_View|null
	 */
	public function get_current_view() {
		$page = get_request_var( 'page' );
		if ( Plugin::$slug !== $page ) {
			return null;
		}

		$views = array_filter( $this->get_all(), static function( $view ) {
			return $view->is_current_view();
		} );

		if ( empty( $views ) ) {
			return null;
		}

		// Return the first view that has current view as true.
		return reset( $views );
	}

	/**
	 * Gets a specific view based on its slug.
	 *
	 * @since TBD
	 *
	 * @param string $slug Which view we are looking for.
	 *
	 * @return Abstract_View|null
	 */
	public function get( string $slug ) {
		$views = array_filter( $this->get_all(), static function( $view ) use ( $slug ) {
			return $view::get_slug() === $slug;
		} );

		if ( empty( $views ) ) {
			return null;
		}

		return reset( $views );
	}

	/**
	 * If we are in a particular view of FakerPress we trigger the parse of that request.
	 *
	 * @since TBD
	 */
	public function parse_current_view_request(): void {
		$view = $this->get_current_view();

		if ( empty( $view ) ) {
			return;
		}

		$slug = $view::get_slug();

		/**
		 * Allow third-party hooking ot the admin view request level.
		 *
		 * @since 0.6.0
		 *
		 * @param Abstract_View $view Which view we are parsing the request from.
		 */
		do_action( 'fakerpress.admin.view.request', $view );

		/**
		 * Allow third-party hooking ot the admin view request level.
		 *
		 * @since 0.6.0
		 *
		 * @param Abstract_View $view Which view we are parsing the request from.
		 */
		do_action( "fakerpress.admin.view.{$slug}.request", $view );

		// Parse the request from the view object.
		$view->parse_request();
	}
}
