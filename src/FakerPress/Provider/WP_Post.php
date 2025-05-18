<?php
/**
 * FakerPress Post Provider
 *
 * @package FakerPress
 * 
 * @since TBD
 */
namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\Utils;
use function FakerPress\make;

/**
 * FakerPress WP_Post Provider
 *
 * @package FakerPress
 * 
 * @since TBD
 */
class WP_Post extends Base {

	protected static $default = [
		'ping_status' => [ 'closed', 'open' ],
		'comment_status' => [ 'closed', 'open' ],
	];

	public function post_title( $qty_words = 5 ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		return $title;
	}

	public function post_type( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			// Later on we will remove the Attachment rule
			$haystack = array_diff( get_post_types( [ 'public' => true, 'show_ui' => true ], 'names' ), [ 'attachment' ] );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_status( $haystack = [ 'draft', 'publish', 'private' ] ) {
		if ( empty( $haystack ) ) {
			$haystack = array_values( get_post_stati() );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_date( $interval = 'now' ) {
		$format = 'Y-m-d H:i:s';
		$interval = (array) $interval;

		// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = newChronos( array_shift( $interval ) );
		} catch ( \Exception $e ) {
			$min = newChronos( 'today' );
			$min = $min->startOfDay();
		}

		if ( ! empty( $interval ) ) {
			// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = newChronos( array_shift( $interval ) );
			} catch ( \Exception $e ) {

			}
		}

		if ( ! isset( $max ) ) {
			$max = newChronos( 'now' );
		}

		// If max has no Time set it to the end of the day
		$max_has_time = array_filter( [ $max->hour, $max->minute, $max->second ] );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ) {
			$max = $max->endOfDay();
		}

		$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );

		return $selected;
	}

	public function post_content( $html = true, $args = [] ) {
		$defaults = [
			'qty' => [ 5, 15 ],
		];
		$args = wp_parse_args( $args, $defaults );

		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( make( Utils::class )->get_qty_from_range( $args['qty'] ) ) );
		}

		return $content;
	}

	/**
	 * Configures a Excerpt for the Post
	 *
	 * @since  0.4.9
	 *
	 * @param  array|int  $qty     How many words we should generate (range with Array)
	 * @param  boolean    $html    Should use HTML or not (currently not used)
	 * @param  integer    $weight  Percentage of times where we will setup a Excerpt
	 *
	 * @return string
	 */
	public function post_excerpt( $qty = [ 25, 75 ], $html = false, $weight = 60 ) {
		$words = make( Utils::class )->get_qty_from_range( $qty );
		$paragraphs = $this->generator->randomElement( [ 1, 1, 1, 1, 1, 2, 2, 2, 3, 4 ] );

		for ( $i = 0; $i < $paragraphs; $i++ ) {
			$excerpt[ $i ] = $this->generator->sentence( $words );
		}

		$excerpt = implode( "\n\n", $excerpt );

		return $this->generator->optional( $weight / 100, '' )->randomElement( (array) $excerpt );
	}

	public function post_author( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = get_users(
				[
					'blog_id' => get_current_blog_id(),
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				]
			);
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_parent( $haystack = [], $weight = 70 ) {
		return $this->generator->optional( $weight / 100, 0 )->randomElement( (array) $haystack );
	}

	public function ping_status( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = static::$default['ping_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function comment_status( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = static::$default['comment_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function menu_order( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			return 0;
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_password( $generator = null, $args = [] ) {
		if ( is_null( $generator ) ) {
			return '';
		}

		return call_user_func_array( $generator, $args );
	}

	public function tax_input( $config = null ) {
		$output = [];
		if ( is_null( $config ) ) {
			return $output;
		}

		// The percentage of change in which the terms will be applied
		$rates = apply_filters( 'fakerpress/provider/WP_Post/tax_input.rates', [
			'category' => 50,
			'post_tag' => 45,
			'__default' => 35,
		] );

		// The amount of terms that might have, provide a number for exact and [ int, int ] to range
		$ranges = apply_filters( 'fakerpress/provider/WP_Post/tax_input.ranges', [
			'category' => [ 1, 3 ],
			'post_tag' => [ 0, 15 ],
			'__default' => [ 0, 3 ],
		] );

		foreach ( $config as $settings ) {
			$settings = (object) $settings;

			if ( ! empty( $settings->taxonomies ) && is_string( $settings->taxonomies ) ) {
				$settings->taxonomies = explode( ',', $settings->taxonomies );
			}
			$settings->taxonomies = array_filter( (array) $settings->taxonomies );

			if ( ! empty( $settings->terms ) && is_string( $settings->terms ) ) {
				$settings->terms = explode( ',', $settings->terms );
			}
			$settings->terms = array_filter( (array) $settings->terms );

			foreach ( $settings->taxonomies as $taxonomy ) {
				if ( empty( $settings->terms ) ) {
					$terms = get_terms( $taxonomy, [ 'fields' => 'ids', 'hide_empty' => false ] );
				} else {
					$terms = $settings->terms;
				}

				// Get all the term ids
				$terms = array_filter( array_map( 'absint', $terms ) );

				if ( ! isset( $settings->qty ) ) {
					$qty = make( Utils::class )->get_qty_from_range( ( isset( $ranges[ $taxonomy ] ) ? $ranges[ $taxonomy ] : $ranges['__default'] ), $terms );
				} else {
					$qty = (int) make( Utils::class )->get_qty_from_range( $settings->qty, $terms );
				}

				if ( ! isset( $settings->rate ) ) {
					$rate = isset( $rates[ $taxonomy ] ) ? $rates[ $taxonomy ] : $rates['__default'];
				} else {
					$rate = (int) $settings->rate;
				}

				// Select the elements based on qty
				$output[ $taxonomy ] = $this->generator->optional( ( (int) $rate ) / 100, null )->randomElements( $terms, (int) $qty );
			}
		}

		return $output;
	}
}
