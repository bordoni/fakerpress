<?php

namespace FakerPress\Module;

use lucatume\DI52\ServiceProvider;

/**
 * Class Factory
 *
 * @since   0.6.0
 *
 * @package FakerPress\Module
 */
class Factory extends ServiceProvider {
	/**
	 * Store the modules that were initialized.
	 *
	 * @since 0.6.0
	 *
	 * @var array
	 */
	protected $modules = [];

	/**
	 * Fetches all modules used by FakerPress
	 *
	 * @since 0.6.0
	 *
	 * @return Abstract_Module[]
	 */
	public function get_all(): array {
		if ( empty( $this->views ) ) {
			$modules_classes = [
				Attachment::class,
				Comment::class,
				Meta::class,
				Post::class,
				Term::class,
				User::class,
			];

			foreach ( $modules_classes as $module_class ) {
				$this->container->singleton( $module_class, $module_class, [ 'hook' ] );
				$this->modules[] = $this->container->make( $module_class );
			}
		}

		/**
		 * Allows the filtering of the FakerPress available modules.
		 *
		 * @since 0.6.0
		 *
		 * @param Abstract_Module[] $modules Which modules are available.
		 */
		return apply_filters( 'fakerpress.modules', $this->modules );
	}

	/**
	 * Gets a specific module based on its slug.
	 *
	 * @since 0.6.0
	 *
	 * @param string $slug Which module we are looking for.
	 *
	 * @return Abstract_Module|null
	 */
	public function get( string $slug ) {
		$views = array_filter( $this->get_all(), static function( $module ) use ( $slug ) {
			return $module::get_slug() === $slug;
		} );

		if ( empty( $views ) ) {
			return null;
		}

		return reset( $views );
	}

	/**
	 * Register all the Modules as Singletons and initializes them.
	 *
	 * @since 0.6.0
	 */
	public function register() {
		// Register the provider as a singleton.
		$this->container->singleton( static::class, $this );

		// Initializes all modules.
		$this->get_all();
	}
}
