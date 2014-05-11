<?php
namespace Faker\Provider;

class Post extends Base {
	public function __construct( $fakerpress ) {
		$this->generator  = $fakerpress->faker;
		$this->fakerpress = $fakerpress;

		$provider = new Html( $this->generator );
		$this->generator->addProvider( $provider );
	}

	public function post_title( $qty_words = 5, $save = true ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		if ( true === $save ){
			$this->fakerpress->args['post_title'] = $title;
		}

		return $title;
	}

	public function post_content( $html = true, $save = true ) {
		if ( $html === true ){
			$content = implode( "\n", $this->generator->html_elements() );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		if ( true === $save ){
			$this->fakerpress->args['post_content'] = $content;
		}

		return $content;
	}

	public function post_type( $haystack = array(), $save = true ){
		if ( empty( $haystack ) ){
			$haystack = get_post_types( array(), 'names' );
		}

		$selected = $this->generator->randomElement( (array) $haystack );

		if ( true === $save ){
			$this->fakerpress->args['post_type'] = $selected;
		}

		return $selected;
	}

	public function user_id( $haystack = array(), $save = true ){
		if ( empty( $haystack ) ){
			$haystack = get_users(
				array(
					'blog_id' => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				)
			);
		}

		$selected = $this->generator->randomElement( (array) $haystack );

		if ( true === $save ){
			$this->fakerpress->args['user_id'] = $selected;
		}

		return $selected;
	}

	public function post_date( $min, $max = null, $save = true ){
		return null;
	}
}