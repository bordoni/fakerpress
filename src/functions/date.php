<?php
namespace FakerPress;

/**
 * Creates a carbon date without throwing an error.
 *
 * @since 0.6.0
 *
 * @param $raw_date
 * @param $tz
 *
 * @return \Carbon\Carbon|\WP_Error
 */
function carbon( $raw_date, $tz = null ) {
	// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime
	try {
		$date = new \Carbon\Carbon( $raw_date, $tz );
	} catch ( \Exception $e ) {
		$date = new \WP_Error( 'fakerpress-date-error', null, [ 'error' => $e ] );
	}

	return $date;
}

