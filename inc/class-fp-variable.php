<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

class Variable {

	public static $filter_callbacks = array(
		FILTER_DEFAULT                     => null,

		// Validate
		FILTER_VALIDATE_BOOLEAN            => 'is_bool',
		FILTER_VALIDATE_EMAIL              => 'is_email',
		FILTER_VALIDATE_FLOAT              => 'is_float',
		FILTER_VALIDATE_INT                => 'is_int',
		FILTER_VALIDATE_IP                 => array( 'Variable', 'is_ip_address' ),
		FILTER_VALIDATE_REGEXP             => array( 'Variable', 'is_regex' ),
		FILTER_VALIDATE_URL                => 'wp_http_validate_url',

		// Sanitize
		FILTER_SANITIZE_EMAIL              => 'sanitize_email',
		FILTER_SANITIZE_ENCODED            => 'esc_url_raw',
		FILTER_SANITIZE_NUMBER_FLOAT       => 'floatval',
		FILTER_SANITIZE_NUMBER_INT         => 'intval',
		FILTER_SANITIZE_SPECIAL_CHARS      => 'htmlspecialchars',
		FILTER_SANITIZE_STRING             => 'sanitize_text_field',
		FILTER_SANITIZE_URL                => 'esc_url_raw',
		'file'                             => 'sanitize_file_name',

		// Other
		FILTER_UNSAFE_RAW                  => null,
	);

	public function __construct() {

	}

	public static function search( $variable = null, $indexes = array(), $default = null ) {
		if ( is_object( $variable ) ){
			$variable = (array) $variable;
		}

		if ( ! is_array( $variable ) ){
			return $variable;
		}

		foreach ( (array) $indexes as $index ) {
			if ( is_array( $variable ) && isset( $variable[ $index ] ) ){
				$variable = $variable[ $index ];
			} else {
				$variable = $default;
				break;
			}
		}

		return $variable;
	}

	public static function super( $type, $variable, $filter = null, $default = null ) {
		$super = null;

		switch ( $type ) {
			case INPUT_POST :
				$super = $_POST;
				break;
			case INPUT_GET :
				$super = $_GET;
				break;
			case INPUT_COOKIE :
				$super = $_COOKIE;
				break;
			case INPUT_ENV :
				$super = $_ENV;
				break;
			case INPUT_SERVER :
				$super = $_SERVER;
				break;
			default:
				// Setting the Super
				if ( is_array( $type ) ){
					$super = $type;
				}
				break;
		}

		if ( is_null( $super ) ) {
			throw new Exception( __( 'Invalid use, type must be one of INPUT_* family.', 'fakerpress' ) );
		}

		$var = self::search( $super, $variable );
		$var = self::filter( $var, $filter, $default );

		return $var;
	}

	public static function filter( $var = null, $filter = FILTER_DEFAULT, $default = null ) {
		if ( is_null( $var ) ){
			$var = $default;
		}

		if ( ! array_key_exists( $filter, self::$filter_callbacks ) ) {
			return $default;
		}

		$filter_callback = self::$filter_callbacks[ $filter ];

		if ( FILTER_UNSAFE_RAW === $filter ) {
			return $var;
		}

		return ( ! is_null( $filter_callback ) ? call_user_func( $filter_callback, $var ) : $var );
	}

	public static function is_regex( $var ) {
		// @codingStandardsIgnoreStart
		$test = @preg_match( $var, '' );
		// @codingStandardsIgnoreEnd

		return $test !== false;
	}

	public static function is_ip_address( $var ) {
		return false !== WP_Http::is_ip_address( $var );
	}

}