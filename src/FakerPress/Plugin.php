<?php
namespace FakerPress;

use lucatume\DI52\ServiceProvider;

class Plugin extends ServiceProvider {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.5.1
	 *
	 * @var string
	 */
	const VERSION = '0.6.0';

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
	 * Set up the Plugin's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->plugin_path = trailingslashit( dirname( static::$_file ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );

		$this->bind_implementations();
		$this->register_hooks();
		$this->register_assets();

		/**
		 * Triggers an action for loading of functionality.
		 *
		 * @since 0.6.0
		 */
		do_action( 'fakerpress.plugin_loaded' );
	}

	/**
	 * Register the implementations of the plugin with the container.
	 *
	 * @since TBD
	 */
	protected function bind_implementations() {
		$this->container->singleton( Admin::class, Admin::class );
		$this->container->singleton( Admin\Menu::class, Admin\Menu::class );
		$this->container->singleton( Ajax::class, Ajax::class );
		$this->container->singleton( Utils\Assets::class, Utils\Assets::class );
		$this->container->singleton( Utils::class, Utils::class );

		// Register all the Service Providers.
		$this->container->register( Module\Factory::class );
		$this->container->register( Admin\View\Factory::class );
		$this->container->register( Fields\Factory::class );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Tickets.
	 *
	 * @since TBD
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Tickets.
	 *
	 * @since TBD
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
	}

	/**
	 * Return a Path relative to the plugin root
	 *
	 * @since 0.1.0
	 *
	 * @uses plugin_dir_path
	 *
	 * @param  string $append A string to be appended to the root path
	 *
	 * @return string         The path after being appended by the variable
	 */
	public static function path( $append = '' ) {
		return (string) make( static::class )->plugin_path . str_replace( '/', DIRECTORY_SEPARATOR, $append );
	}

	/**
	 * Return a URL relative to the plugin root
	 *
	 * @since 0.1.0
	 * @param  string $file   A string to be appended to the root url
	 * @uses plugins_url
	 * @return string         The url to the file
	 */
	public static function url( $file = '' ) {
		return (string) make( static::class )->plugin_url .  $file;
	}

	/**
	 * Return a URL relative to the plugin's administration page.
	 *
	 * @since 0.1.0
	 *
	 * @uses admin_url
	 * @uses wp_parse_args
	 * @uses add_query_arg
	 *
	 * @param  string|array $args Arguments for the admin URL
	 * @param  string       $hash Hash for the admin URL
	 *
	 * @return string       The url to the file
	 */
	public static function admin_url( $args = '', $hash = false ) {
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
	 * @param  string $path Hash for the admin URL
	 * @uses esc_url_raw
	 *
	 * @return string         The url the external website with the appended $path
	 */
	public static function ext_site_url( $path = '/' ) {
		return esc_url_raw( static::$_ext_domain . ( ! empty( $path ) ? $path : '/' ), [ 'http', 'https' ] );
	}

	public static function get( $name, $default = false ) {
		$options = static::all();
		$value = get( $options, $name, $default );

		return $value;
	}

	public static function update( $name = null, $value = false ) {
		$options = static::all();
		$opts = [];

		foreach ( (array) $name as $k => $index ) {
			if ( 0 === $k ) {
				$opts[ -1 ] = &$options;
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
