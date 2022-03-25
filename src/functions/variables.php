<?php

namespace FakerPress;

/**
 * Set key/value within an array, can set a key nested inside a multidimensional array.
 *
 * Example: set( $a, [ 0, 1, 2 ], 'hi' ) sets $a[0][1][2] = 'hi' and returns $a.
 *
 * @since 0.6.0
 *
 * @param mixed        $array  The array containing the key this sets.
 * @param string|array $key    To set a key nested multiple levels deep pass an array
 *                             specifying each key in order as a value.
 *                             Example: array( 'lvl1', 'lvl2', 'lvl3' );
 * @param mixed        $value  The value.
 *
 * @return array Full array with the key set to the specified value.
 */
function set( array $array, $key, $value ) {
	// Convert strings and such to array.
	$key = (array) $key;

	// Setup a pointer that we can point to the key specified.
	$key_pointer = &$array;

	// Iterate through every key, setting the pointer one level deeper each time.
	foreach ( $key as $i ) {

		// Ensure current array depth can have children set.
		if ( ! is_array( $key_pointer ) ) {
			// $key_pointer is set but is not an array. Converting it to an array
			// would likely lead to unexpected problems for whatever first set it.
			$error = sprintf(
				'Attempted to set $array[%1$s] but %2$s is already set and is not an array.',
				implode( $key, '][' ),
				$i
			);

			_doing_it_wrong( __FUNCTION__, esc_html( $error ), '0.1.0' );
			break;
		} elseif ( ! isset( $key_pointer[ $i ] ) ) {
			$key_pointer[ $i ] = [];
		}

		// Dive one level deeper into the nested array.
		$key_pointer = &$key_pointer[ $i ];
	}

	// Set the value for the specified key
	$key_pointer = $value;

	return $array;
}

/**
 * Find a value inside an array or object, including one nested a few levels deep.
 *
 * Example: get( $a, [ 0, 1, 2 ] ) returns the value of $a[0][1][2] or the default.
 *
 * @since 0.6.0
 *
 * @param array|object $variable  Array or object to search within.
 * @param array|string $indexes   Specify each nested index in order.
 *                                Example: array( 'lvl1', 'lvl2' );
 * @param mixed        $default   Default value if the search finds nothing.
 *
 * @return mixed The value of the specified index or the default if not found.
 */
function get( $variable, $indexes, $default = null ) {
	if ( is_object( $variable ) ) {
		$variable = (array) $variable;
	}

	if ( ! is_array( $variable ) ) {
		return $default;
	}

	foreach ( (array) $indexes as $index ) {
		if ( ! is_array( $variable ) || ! isset( $variable[ $index ] ) ) {
			$variable = $default;
			break;
		}

		$variable = $variable[ $index ];
	}

	return $variable;
}

/**
 * Find a value inside a list of array or objects, including one nested a few levels deep.
 *
 * @since 0.6.0
 *
 * Example: get( [$a, $b, $c], [ 0, 1, 2 ] ) returns the value of $a[0][1][2] found in $a, $b or $c
 * or the default.
 *
 * @param array        $variables  Array of arrays or objects to search within.
 * @param array|string $indexes    Specify each nested index in order.
 *                                 Example: array( 'lvl1', 'lvl2' );
 * @param mixed        $default    Default value if the search finds nothing.
 *
 * @return mixed The value of the specified index or the default if not found.
 */
function get_in_any( array $variables, $indexes, $default = null ) {
	foreach ( $variables as $variable ) {
		$found = get( $variable, $indexes, '__not_found__' );
		if ( '__not_found__' !== $found ) {
			return $found;
		}
	}

	return $default;
}

/**
 * Tests to see if the requested variable is set either as a post field or as a URL
 * param and returns the value if so.
 *
 * Post data takes priority over fields passed in the URL query. If the field is not
 * set then $default (null unless a different value is specified) will be returned.
 *
 * The variable being tested for can be an array if you wish to find a nested value.
 *
 * @see   \StellarWP\Jobvite\get_in_any()
 *
 * @since 0.6.0
 *
 * @param string|array $indexes
 * @param mixed        $default
 *
 * @return mixed
 */
function get_request_var( $indexes, $default = null ) {
	return get_in_any( [ $_GET, $_POST, $_REQUEST ], $indexes, $default );
}

/**
 * Fetches the Request method used for this request.
 *
 * @since 0.6.0
 *
 * @return string
 */
function get_request_method(): string {
	return strtoupper( wp_unslash( get( $_SERVER, 'REQUEST_METHOD', 'GET' ) ) );
}

