<?php
namespace Faker\Provider;

class LoremPixel extends Base {
	/**
	 * @param \Faker\Generator $generator
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

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

		$url = "http://lorempixel.com/{$width}/{$height}/" . $this->generator->randomElement( $categories );
		return $url;
	}

}