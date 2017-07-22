<?php
namespace Faker\Provider;

/**
 * @since  0.1.5
 */
class PlaceHoldIt extends Base {
	/**
	 * Constructor for the Provider
	 *
	 * @since  0.1.5
	 *
	 * @param \Faker\Generator $generator An instance of the Faker Generator class
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Generates a URL for Placehold.it
	 *
	 * @since  0.1.5
	 *
	 * @param  array|int  $width  A range for the images that will be generated, if a int is passed
	 *                            we use that value always.
	 * @param  float      $ratio  It makes the logic a few times easier to pass the Ratio instead of
	 *                            the height of the image.
	 *
	 * @return string
	 */
	public function placeholdit( $width = array( 200, 640 ), $ratio = 1.25 ) {
		if ( is_array( $width ) ){
			$width = call_user_func_array( array( $this->generator, 'numberBetween' ), $width );
		}

		$width = absint( $width );
		$height = floor( $width / floatval( $ratio ) );
		$url = "http://placehold.it/{$width}x{$height}/";

		return $url;
	}
}