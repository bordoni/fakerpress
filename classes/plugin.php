<?php
namespace FakerPress;

class Plugin {
	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	const version = '0.1.5';

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
		$defaults = array(
			'page' => self::$slug,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		return add_query_arg( $args, admin_url( 'admin.php' ) ) . ( $hash !== false ? "#{$hash}" : '' );
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
			$_file = $_link . '/' . end( explode( '/', $_file ) );
		}
		return (string) plugin_basename( $_file );
	}

	/**
	 * The initialization of the static and dynamic variables
	 *
	 * @since 0.1.0
	 */
	public function __construct(){
		// Setup the global version of the class, this only runs once...
		null === self::$instance and self::$instance = &$this;
	}
}