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

	public function post_content( $html = true, $args = array() ) {
		if ( $html === true ){
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
	}

	public function tax_input( $taxonomies = null, $range = array( 1, 6 ) ) {
		$output = array();

		if ( is_null( $taxonomies ) ){
			return $output;
		}

		foreach ( $taxonomies as $taxonomy ){
			$terms = array_map( 'absint', get_terms( $taxonomy, array( 'fields' => 'ids', 'hide_empty' => false ) ) );

			if ( is_array( $range ) ){
				$qty = call_user_func_array( array( $this->generator, 'numberBetween' ), $range );
			} else {
				$qty = $range;
			}

			$qty = min( count( $terms ), $qty );

			$output[ $taxonomy ] = $this->generator->randomElements( $terms , $qty );
		}

		return $output;
	}

	public function post_type( $haystack = array() ){
		if ( empty( $haystack ) ){
			// Later on we will remove the Attachment rule
			$haystack = array_diff( get_post_types( array( 'public' => true, 'show_ui' => true ), 'names' ), array( 'attachment' ) );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_author( $haystack = array() ){
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

	public function post_parent( $haystack = array(), $rate = 70 ){
		return $this->generator->numberBetween( 0, 100 ) < absint( $rate ) ? 0 : $this->generator->randomElement( (array) $haystack );
	}

	public function ping_status( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = static::$default['ping_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function comment_status( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = static::$default['comment_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function menu_order( $haystack = array() ){
		if ( empty( $haystack ) ){
			return 0;
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_password( $generator = null, $args = array() ){
		if ( is_null( $generator ) ){
			return '';
		}

		return call_user_func_array( $generator, $args );
	}

	public function post_date( $min = 'now', $max = null ){
		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $min );
		} catch (\Exception $e) {
			return null;
		}

		if ( ! is_null( $max ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
			} catch (\Exception $e) {
				return null;
			}
		}

		if ( ! is_null( $max ) ) {
			$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( 'Y-m-d H:i:s' );
		} else {
			$selected = (string) $min;
		}

		return $selected;
	}
}