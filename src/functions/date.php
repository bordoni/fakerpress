<?php
/**
 * Date Functions
 *
 * @package FakerPress
 * 
 * @since TBD
 */

namespace FakerPress;

use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use WP_Error;

/**
 * Creates a chronos date without throwing an error.
 *
 * @since TBD
 *
 * @param string $raw_date The date to create.
 * @param string $tz The timezone to create the date in.
 *
 * @return Chronos|WP_Error
 */
function chronos( $raw_date, $tz = null ) {
	// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
	try {
		$date = new Chronos( $raw_date, $tz );
	} catch ( \Exception $e ) {
		$date = new WP_Error( 'fakerpress-date-error', null, [ 'error' => $e ] );
	}

	return $date;
}
