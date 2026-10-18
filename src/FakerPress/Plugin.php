<?php
/**
 * Plugin main class.
 *
 * @since 0.1.0
 *
 * @package FakerPress
 * @subpackage Main
 * @copyright Copyright (c) 2014-2025, Gustavo Bordoni
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */

namespace FakerPress;

/**
 * Main plugin class used to setup all the necessary components.
 *
 * @since 0.1.0
 */
class Plugin {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.5.1
	 *
	 * @var string
	 */
	public const VERSION = '0.10.0';

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
	 * Variable holding the folder name of the plugin.
	 *
	 * @since 0.1.0
	 * @deprecated 0.9.0 Use `get_var( 'fakerpress.slug' )` instead.
	 *
	 * @var string
	 */
	public static $folder = 'fakerpress';

	/**
	 * Variable holding the slug name of the plugin.
	 *
	 * @since 0.1.0
	 * @deprecated 0.9.0 Use `get_slug()` instance method or `get_var( 'fakerpress.slug' )` instead.
	 *
	 * @var string
	 */
	public static $slug = 'fakerpress';

	/**
	 * The __FILE__ that initialized the plugin.
	 *
	 * @since 0.1.0
	 * @deprecated 0.9.0 Use `get_var( 'fakerpress.file' )` instead.
	 *
	 * @var string
	 */
	public static $_file = __FP_FILE__; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore -- Public static property, renaming it breaks Plugin::$_file references.

	/**
	 * The Plugin external website base domain
	 *
	 * @since 0.3.2
	 *
	 * @var string
	 */
	public static $_ext_domain = 'https://fakerpress.com'; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore -- Public static property, renaming it breaks Plugin::$_ext_domain references.

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
		$this->static_load();

		// Register this as a singleton on the container.
		singleton( static::class, $this );

		// Store key plugin values in the container for DI-friendly access.
		set_var( 'fakerpress.slug', static::$slug );
		set_var( 'fakerpress.version', static::VERSION );
		set_var( 'fakerpress.file', static::$_file );

		$this->bind_implementations();

		/**
		 * Triggers an action for loading of functionality.
		 *
		 * @since 0.6.0
		 */
		do_action( 'fakerpress.plugin_loaded' );
	}

	/**
	 * Loads function files that must be available after the Composer autoloader runs.
	 *
	 * @since 0.9.0
	 *
	 * @return void
	 */
	protected function static_load(): void {
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'container.php';
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'date.php';
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'variables.php';
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'conditionals.php';
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'sorting.php';
		require_once $this->plugin_path . 'src' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'assets.php';
	}

	/**
	 * Autoload the classes for the plugin via Composer.
	 *
	 * @since 0.6.2
	 *
	 * @return void
	 */
	protected function autoload(): void {
		require_once $this->plugin_path . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
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

		bind( REST\Endpoints\Comments::class, REST\Endpoints\Comments::class );
		bind( REST\Endpoints\Posts::class, REST\Endpoints\Posts::class );
		bind( REST\Endpoints\Users::class, REST\Endpoints\Users::class );
		bind( REST\Endpoints\Terms::class, REST\Endpoints\Terms::class );

		// Register all the Service Providers.
		register( Assets::class );
		register( Hooks::class );
		register( REST\Controller::class );

		register( Module\Factory::class );
		register( Admin\View\Factory::class );
		register( Fields\Factory::class );
	}

	/**
	 * Returns the plugin slug.
	 *
	 * @since 0.9.0
	 *
	 * @return string The plugin slug.
	 */
	public function get_slug(): string {
		return static::$slug;
	}

	/**
	 * Returns the plugin version.
	 *
	 * @since 0.9.0
	 *
	 * @return string The plugin version.
	 */
	public function get_version(): string {
		return static::VERSION;
	}

	/**
	 * Return a Path relative to the plugin root.
	 *
	 * @since 0.1.0
	 * @deprecated 0.9.0 Use `make( Plugin::class )->plugin_path` instead.
	 *
	 * @uses plugin_dir_path
	 *
	 * @param string $append A string to be appended to the root path.
	 *
	 * @return string The path after being appended by the variable.
	 */
	public static function path( string $append = '' ): string {
		return (string) make( static::class )->plugin_path . str_replace( '/', DIRECTORY_SEPARATOR, $append );
	}

	/**
	 * Return a URL relative to the plugin root.
	 *
	 * @since 0.1.0
	 * @deprecated 0.9.0 Use `make( Plugin::class )->plugin_url` instead.
	 *
	 * @uses plugins_url
	 *
	 * @param string $file A string to be appended to the root url.
	 *
	 * @return string The url to the file.
	 */
	public static function url( string $file = '' ): string {
		return (string) make( static::class )->plugin_url . $file;
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
	 * @param string|array $args Query arguments for the admin URL, merged over `page=fakerpress`.
	 * @param string|false $hash Fragment to append after `#`, or false for none.
	 *
	 * @return string The FakerPress admin page URL.
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
	 * @uses esc_url_raw
	 *
	 * @param string $path Path to append to the project website domain.
	 *
	 * @return string The url the external website with the appended $path.
	 */
	public static function ext_site_url( string $path = '/' ): string {
		return esc_url_raw( static::$_ext_domain . ( ! empty( $path ) ? $path : '/' ), [ 'http', 'https' ] );
	}

	/**
	 * Gets a plugin option, including one nested inside the stored options array.
	 *
	 * @param string|array $name    Option index, or a list of nested indexes in order.
	 * @param mixed        $default Value returned when the option is not set.
	 *
	 * @return mixed The option value or the default.
	 */
	public static function get( $name, $default = false ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Public API, renaming the parameter breaks callers that pass it as a named argument.
		$options = static::all();
		return get( $options, $name, $default );
	}

	/**
	 * Sets a plugin option, creating the nested arrays along the way, and saves the options.
	 *
	 * @param string|array $name  Option index, or a list of nested indexes in order.
	 * @param mixed        $value Value to store.
	 *
	 * @return bool Whether `update_option()` changed the stored options.
	 */
	public static function update( $name = null, $value = false ) {
		$options = static::all();
		$opts    = [];

		foreach ( (array) $name as $k => $index ) {
			if ( 0 === $k ) {
				$opts[- 1] = &$options;
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

	/**
	 * Placeholder for removing a plugin option; not implemented yet and does nothing.
	 *
	 * @param string|array|null $name Option index, or a list of nested indexes in order.
	 *
	 * @return void
	 */
	public static function remove( $name = null ) {
		// @TODO
	}

	/**
	 * Gets every stored plugin option.
	 *
	 * @return array The `fakerpress-plugin-options` option, an empty array when it is not set.
	 */
	public static function all() {
		$defaults = [];

		return get_option( static::$slug . '-plugin-options', $defaults );
	}
}
