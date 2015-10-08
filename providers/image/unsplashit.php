<?php
namespace Faker\Provider;

class UnsplashIt extends Base {
	/**
	 * @param \Faker\Generator $generator
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

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