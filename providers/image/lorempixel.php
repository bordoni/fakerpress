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
	 *
	 * @param  array|int  $width  A range for the images that will be generated, if a int is passed
	 *                            we use that value always.
	 * @param  float      $ratio  It makes the logic a few times easier to pass the Ratio instead of
	 *                            the height of the image.
	 *
	 * @return string        Return the URL for the
	 */
	public function lorempixel( $width = array( 200, 640 ), $ratio = 1.25 ) {
		if ( is_array( $width ) ){
			$width = call_user_func_array( array( $this->generator, 'numberBetween' ), $width );
		}

		$width = absint( $width );
		$height = floor( $width / floatval( $ratio ) );

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
		 * @param  array  $categories  The set of categories that can be used from LoremPixel
		 * @param  self   $provider    An instance of the Provider
		 */
		$categories = (array) apply_filters( 'fakerpress.provider.image.lorempixel.categories', $categories, $this );

		$url = "http://lorempixel.com/{$width}/{$height}/" . $this->generator->randomElement( $categories );
		return $url;
	}

}