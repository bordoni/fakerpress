<?php
/**
 * Faker provider for placehold.co image URLs.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider\Image;

use FakerPress\ThirdParty\Faker\Provider\Base;


/**
 * Builds random-size placeholder image URLs on placehold.co.
 *
 * @since 0.6.0
 */
class Placeholder extends Base {
	const ID = 'placeholder';

	/**
	 * Generates a PNG image URL on placehold.co.
	 *
	 * @since 0.1.5
	 * @since 0.4.9  On this version we started to accept Array or Int in the Second Param
	 *
	 * @param array|int       $width  A [ min, max ] range to pick the width from, or a fixed width in pixels.
	 * @param float|array|int $height Fixed height (int), a [ min, max ] range (array), or a width/height
	 *                                ratio (float) used to derive the height from the width.
	 *
	 * @return string The image URL.
	 */
	public function placeholder_image( $width = [ 200, 640 ], $height = 1.25 ) {
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

		// Ask for a PNG: without an extension placehold.co serves SVG, which attachments reject.
		return "https://placehold.co/{$width}x{$height}.png";
	}

	/**
	 * Alias for placeholder_image to match the expected method name.
	 *
	 * @since TBD
	 *
	 * @param array|int       $width  A [ min, max ] range to pick the width from, or a fixed width in pixels.
	 * @param float|array|int $height Fixed height, a [ min, max ] range, or a width/height ratio.
	 *
	 * @return string The image URL.
	 */
	public function placeholder( $width = [ 200, 640 ], $height = 1.25 ) {
		return $this->placeholder_image( $width, $height );
	}
}
