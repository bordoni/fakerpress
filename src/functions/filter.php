<?php
/**
 * Filters a given variable for security reasons
 * @param  [type] $var     [description]
 * @param  [type] $filter  [description]
 * @param  [type] $default [description]
 * @return [type]          [description]
 */
function fb_filter_var( $var = null, $filter = FILTER_DEFAULT, $default = null ) {
	$filter_callbacks = [
		FILTER_DEFAULT                     => null,

		FILTER_SANITIZE_EMAIL              => 'sanitize_email',
		FILTER_SANITIZE_ENCODED            => 'esc_url_raw',
		FILTER_SANITIZE_NUMBER_FLOAT       => 'floatval',
		FILTER_SANITIZE_NUMBER_INT         => 'intval',
		FILTER_SANITIZE_SPECIAL_CHARS      => 'htmlspecialchars',
		FILTER_SANITIZE_STRING             => 'sanitize_text_field',
		FILTER_SANITIZE_URL                => 'esc_url_raw',
		'file'                             => 'sanitize_file_name',

		FILTER_UNSAFE_RAW                  => null,
	];

	if ( is_null( $var ) ){
		$var = $default;
	}

	if ( ! array_key_exists( $filter, $filter_callbacks ) ) {
		return $default;
	}

	$filter_callback = $filter_callbacks[ $filter ];

	if ( FILTER_UNSAFE_RAW === $filter ) {
		return $var;
	}

	return ( ! is_null( $filter_callback ) ? call_user_func( $filter_callback, $var ) : $var );
}

