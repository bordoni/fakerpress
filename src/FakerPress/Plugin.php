<?php
namespace FakerPress;

class Plugin {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.5.1
	 * @var string
	 */
	const VERSION = '0.5.1';

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.1.0
	 * @deprecated 0.5.1
	 *
	 * @var string
	 */
	const version = self::VERSION;

	/**
	 * A static variable that holds a dinamic instance of the class
	 *
	 * @since 0.1.0
	 * @var null|object The dynamic version of this class
	 */
	public static $instance = null;

	/**
	 * A static variable that holds a dinamic instance of the class of the admin
	 *
	 * @since 0.1.0
	 * @var null|object The dynamic version of this class
	 */
	public static $admin = null;

	/**
	 * A static variable that holds a dinamic instance of the class of the AJAX methods
	 *
	 * @since 0.2.0
	 * @var null|object The dynamic version of this class
	 */
	public static $ajax = null;

	/**
	 * Variable holding the folder name of the plugin
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $folder = 'fakerpress';

	/**
	 * Variable holding the slug name of the plugin
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public static $slug = 'fakerpress';

	/**
	 * The __FILE__ that initialized the plugin
	 *
	 * @since 0.1.0
	 * @var string/path
	 */
	public static $_file = __FP_FILE__;


	/**
	 * The Plugin external website base domain
	 *
	 * @since 0.3.2
	 * @var string
	 */
	public static $_ext_domain = 'http://fakerpress.com';

	/**
	 * Return a Path relative to the plugin root
	 *
	 * @since 0.1.0
	 * @param  string $append A string to be appended to the root path
	 * @uses plugin_dir_path
	 * @return string         The path after been appended by the variable
	 */
	public static function path( $append = '' ) {
		return (string) plugin_dir_path( self::$_file ) . str_replace( '/', DIRECTORY_SEPARATOR, $append );
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
		return (string) plugins_url( $file, self::basename() );
	}

	/**
	 * Return a URL relative to the plugin's administration page
	 *
	 * @since 0.1.0
	 * @param  string|array $args Arguments for the admin URL
	 * @param  string $hash Hash for the admin URL
	 * @uses admin_url
	 * @uses wp_parse_args
	 * @uses add_query_arg
	 * @return string         The url to the file
	 */
	public static function admin_url( $args = '', $hash = false ) {
		/**
		 * Define the array of defaults
		 */
		$defaults = [
			'page' => self::$slug,
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
		return esc_url_raw( self::$_ext_domain . ( ! empty( $path ) ? $path : '/' ), [ 'http', 'https' ] );
	}

	/**
	 * Return the plugin basename
	 *
	 * @since 0.1.0
	 * @uses plugin_basename
	 * @return string plugin_basename from __FILE__
	 */
	public static function basename() {
		$_link = WP_PLUGIN_DIR . '/' . self::$folder;
		$_file = self::$_file;

		if ( is_link( $_link ) && readlink( $_link ) == dirname( $_file ) ) {
			$basename = explode( '/', $_file );
			$_file = $_link . '/' . end( $basename );
		}
		return (string) plugin_basename( $_file );
	}

	public static function get( $name, $default = false ) {
		$options = self::all();
		$value = fp_array_get( $options, $name, null, $default );

		return $value;
	}

	public static function update( $name = null, $value = false ) {
		$options = self::all();
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

		return update_option( self::$slug . '-plugin-options', $options );
	}

	public static function remove( $name = null ) {
		// @TODO
	}

	public static function all() {
		$defaults = [];
		$options = get_option( self::$slug . '-plugin-options', $defaults );

		return $options;
	}

	/**
	 * The initialization of the static and dynamic variables
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		// Setup the global version of the class, this only runs once...
		null === self::$instance && self::$instance = &$this;
	}
}
