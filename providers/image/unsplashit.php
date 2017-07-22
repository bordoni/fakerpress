<?php
namespace Faker\Provider;

/**
 * @since  0.4.2
 */
class UnsplashIt extends Base {
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
	 * Generates a URL for Unsplash.it
	 *
	 * @since  0.4.2
	 *
	 * @param  array|int  $width  A range for the images that will be generated, if a int is passed
	 *                            we use that value always.
	 * @param  float      $ratio  It makes the logic a few times easier to pass the Ratio instead of
	 *                            the height of the image.
	 *
	 * @return string
	 */
	public function unsplashit( $width = array( 800, 1440 ), $ratio = 1.25 ) {
		if ( is_array( $width ) ){
			$width = call_user_func_array( array( $this->generator, 'numberBetween' ), $width );
		}

		$width = absint( $width );
		$height = floor( $width / floatval( $ratio ) );

		// https://unsplash.it/200/640/?random
		$url = "https://unsplash.it/{$width}/{$height}/?random";

		return $url;
	}

}