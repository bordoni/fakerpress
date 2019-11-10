<?php
namespace FakerPress\Provider\Image;

use Faker\Provider\Base;

/**
 * @since  0.4.2
 * @since  0.5.0 Unsplash.it turned into Lorem Picsum
 */
class LoremPicsum extends Base {
	/**
	 * Constructor for the Provider
	 *
	 * @since  0.4.2
	 *
	 * @param \Faker\Generator $generator An instance of the Faker Generator class
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Generates a URL for Lorem Picsum, previosuly known as Unsplash.it
	 *
	 * @since  0.4.2
	 * @since  0.4.9  On this version we started to accept Array or Int in the Second Param
	 * @since  0.5.0  Moved from Unsplash.it to Lorem Picsum
	 *
	 * @param  array|int        $width   A range for the images that will be generated, if a int is passed
	 *                                   we use that value always.
	 * @param  float|array|int  $height  Image height, int for fixed size, array for randomized and
	 *                                   float to use a ratio
	 *
	 * @return string
	 */
	public function lorempicsum( $width = [ 800, 1440 ], $height = 1.25 ) {
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

		// https://picsum.photos/200/640/?random
		$url = "https://picsum.photos/{$width}/{$height}/?random";

		return $url;
	}

}