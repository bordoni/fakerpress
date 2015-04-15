<?php
namespace Faker\Provider;

class PlaceHoldIt extends Base {
	/**
	 * @param \Faker\Generator $generator
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

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