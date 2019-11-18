<?php
/**
 * Determines if the provided value should be regarded as 'true'.
 *
 * @since  0.5.1
 *
 * @param  mixed  $var  Value to be tested.
 *
 * @return bool
 */
function fp_is_truthy( $var ) {
	if ( is_bool( $var ) ) {
		return $var;
	}

	/**
	 * Provides an opportunity to modify strings that will be
	 * deemed to evaluate to true.
	 *
	 * @param array $truthy_strings
	 */
	$truthy_strings = (array) apply_filters( 'fakerpress_is_truthy_strings', [
		'1',
		'enable',
		'enabled',
		'on',
		'y',
		'yes',
		'true',
	] );
	// Makes sure we are dealing with lowercase for testing
	if ( is_string( $var ) ) {
		$var = strtolower( $var );
	}

	// If $var is a string, it is only true if it is contained in the above array
	if ( in_array( $var, $truthy_strings, true ) ) {
		return true;
	}

	// All other strings will be treated as false
	if ( is_string( $var ) ) {
		return false;
	}

	// For other types (ints, floats etc) cast to bool
	return (bool) $var;
}

/**
 * Determines if the provided value is a regular expressions.
 *
 * @since  0.5.1
 *
 * @param  mixed  $variable  Value to be tested.
 *
 * @return bool
 */
function fp_is_regex( $variable ) {
	// @codingStandardsIgnoreStart
	$test = @preg_match( $variable, '' );
	// @codingStandardsIgnoreEnd

	return $test !== false;
}

/**
 * Determines if the provided value is an IP address.
 *
 * @since  0.5.1
 *
 * @param  mixed  $variable  Value to be tested.
 *
 * @return bool
 */
function fp_is_ip_address( $variable ) {
	return false !== WP_Http::is_ip_address( $variable );
}