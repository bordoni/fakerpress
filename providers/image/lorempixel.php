<?php
namespace Faker\Provider;

/**
 * Provides images from Lorem Pixel
 *
 * @since  0.3.2
 */
class LoremPixel extends Base {
	/**
	 * Constructor for the LoremPixel provider
	 *
	 * @since  0.3.2
	 *
	 * @param \Faker\Generator $generator An instance of the Faker Generator class
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Generate an URL for an image from LoremPixel
	 *
	 * @since  0.3.2
	 * @since  0.4.9  On this version we started to accept Array or Int in the Second Param
	 *
	 * @param  array|int        $width   A range for the images that will be generated, if a int is passed
	 *                                   we use that value always.
	 * @param  float|array|int  $height  Image height, int for fixed size, array for randomized and
	 *                                   float to use a ratio
	 *
	 * @return string       Return the URL for Lorem Pixel
	 */
	public function lorempixel( $width = array( 200, 640 ), $height = 1.25 ) {
		if ( is_array( $width ) ){
			$width = call_user_func_array( array( $this->generator, 'numberBetween' ), $width );
		}

		// Makes sure we have an Int
		$width = absint( $width );

		// Check For float (ratio)
		if ( is_float( $height ) ) {
			$height = floor( $width / floatval( $height ) );
		} elseif ( is_array( $height ) ) {
			$height = call_user_func_array( array( $this->generator, 'numberBetween' ), $height );
		}

		$categories = array(
			'abstract',
			'animals',
			'business',
			'cats',
			'city',
			'food',
			'nightlife',
			'fashion',
			'people',
			'nature',
			'sports',
			'technics',
			'transport',
		);

		/**
		 * Allow developers to filter the Categories, only one will be selected.
		 *
		 * @since  0.4.9
		 *
		 * @param  array  $categories  The set of categories that can be used from LoremPixel
		 * @param  self   $provider    An instance of the Provider
		 */
		$categories = (array) apply_filters( 'fakerpress.provider.image.lorempixel.categories', $categories, $this );

		$url = "http://lorempixel.com/{$width}/{$height}/" . $this->generator->randomElement( $categories );
		return $url;
	}

}