<?php
namespace Faker\Provider;

class Post extends Base {
	public function post_title( $qty_words = 5 ) {
		$sentence = $this->generator->sentence( $qty_words );
		return substr( $sentence, 0, strlen( $sentence ) - 1 );
	}
}