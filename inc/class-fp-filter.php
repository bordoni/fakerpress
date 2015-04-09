<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}
/**
 * @see https://github.com/x-team/wp-stream		We forked this idea from X-Team's Stream Plugin
 */

class Filter {

	public static $filter_callbacks = array(
		FILTER_DEFAULT                     => null,

		// Validate
		FILTER_VALIDATE_BOOLEAN            => 'is_bool',
		FILTER_VALIDATE_EMAIL              => 'is_email',
		FILTER_VALIDATE_FLOAT              => 'is_float',
		FILTER_VALIDATE_INT                => 'is_int',
		FILTER_VALIDATE_IP                 => array( 'Filter', 'is_ip_address' ),
		FILTER_VALIDATE_REGEXP             => array( 'Filter', 'is_regex' ),
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

	public static function search( $variable = null, $indexes = array() ) {
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
				$variable = null;
				break;
			}
		}

		return $variable;
	}

	public static function super( $type, $variable, $filter = null, $options = array() ) {
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
		}

		if ( is_null( $super ) ) {
			throw new Exception( __( 'Invalid use, type must be one of INPUT_* family.', 'fakerpress' ) );
		}

		$var = self::search( $super, $variable );
		$var = self::filter( $var, $filter, $options );

		return $var;
	}

	public static function filter( $var, $filter = null, $options = array() ) {
		// Default filter is a sanitizer, not validator
		$filter_type = 'sanitizer';

		// Default value when there is none
		$default = ( array_key_exists( 'default', (array) $options ) ? $options['default'] : $options );
		if ( 'validator' === $filter_type && false === $var ) {
			$var = empty( $options ) ? false : $default;
		} elseif ( 'sanitizer' === $filter_type && null === $var ) {
			$var = empty( $options ) ? null : $default;
		}

		// Only filter value if it is not null
		if ( isset( $var ) && $filter && FILTER_DEFAULT !== $filter ) {
			if ( ! isset( self::$filter_callbacks[ $filter ] ) ) {
				throw new \Exception( __( 'Filter not supported.', 'fakerpress' ) );
			}

			$filter_callback = self::$filter_callbacks[ $filter ];
			$result          = call_user_func( $filter_callback, $var );

			// filter_var / filter_input treats validation/sanitization filters the same
			// they both return output and change the var value, this shouldn't be the case here.
			// We'll do a boolean check on validation function, and let sanitizers change the value
			$filter_type = ( $filter < 500 ) ? 'validator' : 'sanitizer';
			if ( 'validator' === $filter_type ) { // Validation functions
				if ( ! $result ) {
					$var = false;
				}
			} else { // Santization functions
				$var = $result;
			}
		}

		// Detect FILTER_REQUIRE_ARRAY flag
		if ( isset( $var ) && is_int( $options ) && FILTER_REQUIRE_ARRAY === $options ) {
			if ( ! is_array( $var ) ) {
				$var = ( 'validator' === $filter_type ) ? false : null;
			}
		}

		return $var;
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