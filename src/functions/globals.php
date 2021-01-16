<?php
/**
 * Tests to see if the requested variable is set either as a post field or as a URL
 * param and returns the value if so.
 *
 * Post data takes priority over fields passed in the URL query. If the field is not
 * set then $default (null unless a different value is specified) will be returned.
 *
 * The variable being tested for can be an array if you wish to find a nested value.
 *
 * @since 0.5.1
 *
 * @see   fp_get_global_var()
 *
 * @param string|array $variable
 * @param mixed        $default
 * @param mixed        $filter
 *
 * @return mixed
 */
function fp_get_request_var( $variable, $default = null, $filter = FILTER_UNSAFE_RAW ) {
	$unsafe = fp_get_global_var( [ INPUT_GET, INPUT_POST ], $variable, $filter, $default );
	return $unsafe;
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
 * @since 0.5.1
 *
 * @see   fp_get_global_var()
 *
 * @param string|array $var
 * @param mixed        $default
 *
 * @return mixed
 */
function fp_get_global_var( $global, $variable, $filter = null, $default = null ) {
	$super         = [];
	$super_globals = [ INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_ENV, INPUT_SERVER ];
	$search_in     = array_intersect( $super_globals, (array) $global );

	if ( empty( $search_in ) ) {
		throw new Exception( __( 'Invalid use, type must be one of INPUT_* family.', 'fakerpress' ) );
	}

	foreach ( $search_in as $super_name ) {
		switch ( $super_name ) {
			case INPUT_POST :
				$super[] = $_POST;
				break;
			case INPUT_GET :
				$super[] = $_GET;
				break;
			case INPUT_COOKIE :
				$super[] = $_COOKIE;
				break;
			case INPUT_ENV :
				$super[] = $_ENV;
				break;
			case INPUT_SERVER :
				$super[] = $_SERVER;
				break;
		}
	}

	$var = fp_array_get_in_any( $super, $variable );
	$var = fb_filter_var( $var, $filter, $default );

	return $var;
}
