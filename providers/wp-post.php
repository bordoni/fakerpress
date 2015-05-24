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

	public function post_type( $haystack = array() ){
		if ( empty( $haystack ) ){
			// Later on we will remove the Attachment rule
			$haystack = array_diff( get_post_types( array( 'public' => true, 'show_ui' => true ), 'names' ), array( 'attachment' ) );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_status( $haystack = array( 'draft', 'publish', 'private' ) ){
		if ( empty( $haystack ) ){
			$haystack = array_values( get_post_stati() );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_date( $interval = 'now' ){
		$format = 'Y-m-d H:i:s';
		$interval = (array) $interval;

		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( array_shift( $interval ) );
		} catch ( \Exception $e ) {
			$min = new \Carbon\Carbon( 'today' );
			$min = $min->startOfDay();
		}

		if ( ! empty( $interval ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( array_shift( $interval ) );
			} catch ( \Exception $e ) {}
		}

		if ( ! isset( $max ) ) {
			$max = new \Carbon\Carbon( 'now' );
		}

		// If max has no Time set it to the end of the day
		$max_has_time = array_filter( array( $max->hour, $max->minute, $max->second ) );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ){
			$max = $max->endOfDay();
		}

		$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );

		return $selected;
	}

	public function post_content( $html = true, $args = array() ) {
		if ( true === $html ){
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
	}

	public function post_author( $haystack = array() ){
		if ( empty( $haystack ) ){
			$haystack = get_users(
				array(
					'blog_id' => get_current_blog_id(),
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				)
			);
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

	public function tax_input( $taxonomies = null ) {
		$output = array();
		if ( is_null( $taxonomies ) ){
			return $output;
		}

		// The percentage of change in which the terms will be applied
		$rates = apply_filters( 'fakerpress/provider/WP_Post/tax_input.rates', array(
			'category' => 50,
			'post_tag' => 45,
			'__default' => 35,
		) );

		// The amount of terms that might have, provide a number for exact and array( int, int ) to range
		$ranges = apply_filters( 'fakerpress/provider/WP_Post/tax_input.ranges', array(
			'category' => array( 1, 3 ),
			'post_tag' => array( 0, 15 ),
			'__default' => array( 0, 3 )
		) );

		foreach ( $taxonomies as $taxonomy ){
			// Get all the term ids
			$terms = array_map( 'absint', get_terms( $taxonomy, array( 'fields' => 'ids', 'hide_empty' => false ) ) );

			$range = (array) ( isset( $ranges[ $taxonomy ] ) ? $ranges[ $taxonomy ] : $ranges['__default'] );
			$qty_min = min( (array) $range );
			$rate = ( isset( $rates[ $taxonomy ] ) ? $rates[ $taxonomy ] : $rates['__default'] );

			// Turn a range into a number
			$qty = ( is_array( $range ) ? call_user_func_array( array( $this->generator, 'numberBetween' ), $range ) : $range );

			// Only check if not 0
			if ( 0 !== $qty ){
				$qty = min( count( $terms ), $qty );
			}

			// Select the elements based on range
			$elements = $this->generator->randomElements( $terms , $qty );
			$tax_input = array();

			foreach ( $elements as $term_id ) {
				// Apply the rate
				if ( $this->generator->numberBetween( 0, 100 ) <= absint( $rate ) ){
					$tax_input[] = $term_id;
				}
			}

			// If the number of elements is equals 1 and minimum is 1 then apply any
			if ( count( $tax_input ) < $qty_min ){
				$_elements = $terms;
				for ( $i = count( $tax_input ); $i < $qty_min; $i++ ) {
					$selected = $this->generator->randomElement( $_elements );
					$tax_input[] = $selected;

					// Make elements unique
					$selected_key = array_search( $selected, $_elements );
					unset( $_elements[ $selected_key ] );
				}
			}

			$output[ $taxonomy ] = $tax_input;
		}

		return $output;
	}


}