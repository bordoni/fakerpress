<?php

namespace FakerPress;

use FakerPress\Contracts\Service_Provider;

class Plugin {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.5.1
	 *
	 * @var string
	 */
	public const VERSION = '0.6.6';

	/**
	 * @since 0.6.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 0.6.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 0.6.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * Variable holding the folder name of the plugin
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $folder = 'fakerpress';

	/**
	 * Variable holding the slug name of the plugin
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $slug = 'fakerpress';

	/**
	 * The __FILE__ that initialized the plugin
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	public static $_file = __FP_FILE__;

	/**
	 * The Plugin external website base domain
	 *
	 * @since 0.3.2
	 *
	 * @var string
	 */
	public static $_ext_domain = 'https://fakerpress.com';

	/**
	 * Store if the plugin has been booted or not.
	 *
	 * @since 0.6.2
	 *
	 * @var bool Whether the plugin has been booted or not.
	 */
	protected static bool $booted = false;

	/**
	 * Boots the plugin.
	 *
	 * @since 0.6.2
	 *
	 * @return Plugin
	 */
	public static function boot(): Plugin {
		// We assume that if we have the booted variable autoloading is complete.
		if ( static::$booted ) {
			return make( static::class );
		}

		static::$booted = true;

		$plugin = new static();
		$plugin->register();

		return $plugin;
	}

	/**
	 * Plugin constructor.
	 *
	 * This is intentionally empty and protected to prevent direct instantiation, please use the `boot` method.
	 *
	 * @since 0.6.2
	 */
	protected function __construct() {
		// Intentionally empty.
	}

	/**
	 * Registers the plugin.
	 *
	 * Do not attempt to interact with FakerPress before this method is called.
	 *
	 * @since 0.6.2
	 */
	protected function register(): void {
		$this->plugin_path = trailingslashit( dirname( static::$_file ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		$this->autoload();

		// Register this as a singleton on the container.
		singleton( static::class, $this );

		$this->bind_implementations();

		/**
		 * Triggers an action for loading of functionality.
		 *
		 * @since 0.6.0
		 */
		do_action( 'fakerpress.plugin_loaded' );
	}

	/**
	 * Autoload the classes for the plugin via Composer.
	 *
	 * @since 0.6.2
	 *
	 * @return void
	 */
	protected function autoload(): void {
		// Load Composer Vendor Modules
		require_once $this->plugin_path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

		// Load Composer Vendor Modules
		require_once $this->plugin_path . 'vendor-prefixed' . DIRECTORY_SEPARATOR . 'autoload.php';
	}

	/**
	 * Register the implementations of the plugin with the container.
	 *
	 * @since 0.6.2
	 */
	protected function bind_implementations(): void {
		singleton( Admin::class, Admin::class );
		singleton( Admin\Menu::class, Admin\Menu::class );
		singleton( Ajax::class, Ajax::class );
		singleton( Utils\Assets::class, Utils\Assets::class );
		singleton( Utils::class, Utils::class );

		// Register all the Service Providers.
		register( Assets::class );
		register( Hooks::class );

		register( Module\Factory::class );
		register( Admin\View\Factory::class );
		register( Fields\Factory::class );
	}

	/**
	 * Return a Path relative to the plugin root
	 *
	 * @since 0.1.0
	 *
	 * @uses  plugin_dir_path
	 *
	 * @param string $append A string to be appended to the root path
	 *
	 * @return string         The path after being appended by the variable
	 */
	public static function path( string $append = '' ): string {
		return (string) make( static::class )->plugin_path . str_replace( '/', DIRECTORY_SEPARATOR, $append );
	}

	/**
	 * Return a URL relative to the plugin root
	 *
	 * @since 0.1.0
	 * @uses  plugins_url
	 *
	 * @param string $file A string to be appended to the root url
	 *
	 * @return string         The url to the file
	 */
	public static function url( string $file = '' ): string {
		return (string) make( static::class )->plugin_url . $file;
	}

	/**
	 * Return a URL relative to the plugin's administration page.
	 *
	 * @since 0.1.0
	 *
	 * @uses  admin_url
	 * @uses  wp_parse_args
	 * @uses  add_query_arg
	 *
	 * @param string|array $args Arguments for the admin URL
	 * @param string       $hash Hash for the admin URL
	 *
	 * @return string       The url to the file
	 */
	public static function admin_url( $args = '', $hash = false ): string {
		/**
		 * Define the array of defaults
		 */
		$defaults = [
			'page' => static::$slug,
		];

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		return add_query_arg( $args, admin_url( 'admin.php' ) ) . ( $hash !== false ? "#{$hash}" : '' );
	}

	/**
	 * Returns a URL for the external project website
	 *
	 * @since 0.3.2
	 * @uses  esc_url_raw
	 *
	 * @param string $path Hash for the admin URL
	 *
	 * @return string         The url the external website with the appended $path
	 */
	public static function ext_site_url( string $path = '/' ): string {
		return esc_url_raw( static::$_ext_domain . ( ! empty( $path ) ? $path : '/' ), [ 'http', 'https' ] );
	}

	public static function get( $name, $default = false ) {
		$options = static::all();
		$value   = get( $options, $name, $default );

		return $value;
	}

	public static function update( $name = null, $value = false ) {
		$options = static::all();
		$opts    = [];

		foreach ( (array) $name as $k => $index ) {
			if ( 0 === $k ) {
				$opts[ - 1 ] = &$options;
			}

			if ( count( $name ) - 1 !== $k && ! isset( $opts[ $k - 1 ][ $index ] ) ) {
				$opts[ $k - 1 ][ $index ] = [];
			}

			if ( isset( $opts[ $k - 1 ][ $index ] ) ) {
				$opts[ $k ] = &$opts[ $k - 1 ][ $index ];
			} else {
				$opts[ $k - 1 ][ $index ] = $value;
			}
		}
		$opts[ $k ] = $value;

		return update_option( static::$slug . '-plugin-options', $options );
	}

	public static function remove( $name = null ) {
		// @TODO
	}

	public static function all() {
		$defaults = [];

		return get_option( static::$slug . '-plugin-options', $defaults );
	}
}
