<?php
/**
 * Faker provider for Lorem Picsum image URLs.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider\Image;

use FakerPress\ThirdParty\Faker\Provider\Base;

// phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase -- Public class name, renaming it breaks code that references the class.
/**
 * Builds random photo URLs on Lorem Picsum (picsum.photos).
 *
 * @since 0.4.2
 * @since 0.5.0 Unsplash.it turned into Lorem Picsum
 */
class LoremPicsum extends Base {
	// phpcs:enable StellarWP.Classes.ValidClassName.NotSnakeCase
	const ID = 'lorempicsum';

	/**
	 * Generates a URL for Lorem Picsum, previously known as Unsplash.it
	 *
	 * @since 0.4.2
	 * @since 0.4.9  On this version we started to accept Array or Int in the Second Param
	 * @since 0.5.0  Moved from Unsplash.it to Lorem Picsum
	 *
	 * @param array|int       $width  A [ min, max ] range to pick the width from, or a fixed width in pixels.
	 * @param float|array|int $height Fixed height (int), a [ min, max ] range (array), or a width/height
	 *                                ratio (float) used to derive the height from the width.
	 *
	 * @return string The image URL.
	 */
	public function lorempicsum( $width = [ 800, 1440 ], $height = 1.25 ) {
		if ( is_array( $width ) ) {
			$width = call_user_func_array( [ $this->generator, 'numberBetween' ], $width );
		}

		// Makes sure we have an Int.
		$width = absint( $width );

		// Check For float (ratio).
		if ( is_float( $height ) ) {
			$height = floor( $width / floatval( $height ) );
		} elseif ( is_array( $height ) ) {
			$height = call_user_func_array( [ $this->generator, 'numberBetween' ], $height );
		}

		// Builds a URL like https://picsum.photos/200/640/?random so every request returns a random photo.
		return "https://picsum.photos/{$width}/{$height}/?random";
	}
}
