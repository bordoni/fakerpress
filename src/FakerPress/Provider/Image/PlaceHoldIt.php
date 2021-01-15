<?php
namespace FakerPress\Provider\Image;

use Faker\Provider\Base;


/**
 * @since  0.1.5
 */
class PlaceHoldIt extends Base {
	/**
	 * Generates a URL for Placehold.it
	 *
	 * @since  0.1.5
	 * @since  0.4.9  On this version we started to accept Array or Int in the Second Param
	 *
	 * @param  array|int        $width   A range for the images that will be generated, if a int is passed
	 *                                   we use that value always.
	 * @param  float|array|int  $height  Image height, int for fixed size, array for randomized and
	 *                                   float to use a ratio
	 *
	 * @return string
	 */
	public function placeholdit( $width = [ 200, 640 ], $height = 1.25 ) {
		if ( is_array( $width ) ){
			$width = call_user_func_array( [ $this->generator, 'numberBetween' ], $width );
		}

		// Makes sure we have an Int
		$width = absint( $width );

		// Check For float (ratio)
		if ( is_float( $height ) ) {
			$height = floor( $width / floatval( $height ) );
		} elseif ( is_array( $height ) ) {
			$height = call_user_func_array( [ $this->generator, 'numberBetween' ], $height );
		}

		$url = "http://placehold.it/{$width}x{$height}/";

		return $url;
	}
}