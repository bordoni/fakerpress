<?php
namespace Faker\Provider;

class WP_Post extends Base {

	protected static $default = array(
		'ping_status' => array( 'closed', 'open' ),
		'comment_status' => array( 'closed', 'open' ),
	);

	public function post_title( $qty_words = 5 ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		return $title;
	}

	public function post_content( $html = true ) {
		if ( $html === true ){
			$content = implode( "\n", $this->generator->html_elements() );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
	}

	public function post_type( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = get_post_types( array(), 'names' );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function user_id( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = get_users(
				array(
					'blog_id' => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				)
			);
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_status( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = array_values( get_post_stati() );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function ping_status( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = static::$default['ping_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_date( $min = 'now', $max = null, $save = true ){
		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $min );
		} catch (Exception $e) {
			$min = new \Carbon\Carbon();
		}

		if ( ! is_null( $max ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
			} catch (Exception $e) {
				$max = null;
			}
		}

		if ( ! is_null( $max ) ) {
			$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max );
		} else {
			$selected = (string) $min;
		}

		return $selected;
	}
}