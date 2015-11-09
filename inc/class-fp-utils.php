<?php
namespace FakerPress;
use Faker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

class Utils {
	/**
	 * Static Singleton Holder
	 * @var self|null
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Prevents the class to be called from "new" structure
	 *
	 * @return void
	 */
	private function __construct() {

	}


	/**
	 * From range return a random Integer
	 * Providing the $elements param will limit the returning integer to the total number of elements
	 *
	 * @param  array|int $qty   The range or integer
	 * @param  null|int|array $elements {
	 *      @example null  Will not limit the Range to a maximum int
	 *      @example int   Limits the range to this maximum
	 *      @example array Counts the elements in array and limit to that
	 * }
	 * @return int
	 */
	public function get_qty_from_range( $range, $total = null ) {
		if ( is_array( $range ) ) {
			// Remove non-wanted items
			$range = array_filter( $range );

			// Grabs Min
			$min = reset( $range );

			// Grabs Max if range has 2 elements
			if ( count( $range ) > 1 ) {
				$max = end( $range );
			} else {
				// Otherwise just set qty to the Min
				$qty = $min;
			}
		} elseif ( is_numeric( $range ) ) {
			// If we have a numeric we just set it
			$qty = $range;
		} else {
			// All the other cases are a qty 0
			return 0;
		}

		// Now we treat the Range and select a random number
		if ( ! isset( $qty ) ) {
			$qty = Faker\Provider\Base::numberBetween( $min, $max );
		}

		// If an array for the total was provided, turn it to a integer
		if ( is_array( $total ) ){
			$total = count( $total );
		}

		// If we have a numeric total, make sure we don't go over that
		if ( is_numeric( $total ) ){
			$qty = min( $qty, $total );
		}

		// We just make sure we are dealing with a absolute number
		return absint( $qty );
	}
}