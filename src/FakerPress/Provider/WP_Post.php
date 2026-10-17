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
 * Generates the fields of a fake post: title, content, dates, author, statuses and taxonomy terms.
 *
 * @package FakerPress
 *
 * @since TBD
 */
class WP_Post extends Base {

	/**
	 * Values to pick from when no haystack is passed to ping_status() or comment_status().
	 *
	 * @var array<string, string[]>
	 */
	protected static $default = [
		'ping_status'    => [ 'closed', 'open' ],
		'comment_status' => [ 'closed', 'open' ],
	];

	/**
	 * Generates a title: a random sentence without its final full stop.
	 *
	 * @param int $qty_words Approximate number of words.
	 *
	 * @return string The title.
	 */
	public function post_title( $qty_words = 5 ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		return $title;
	}

	/**
	 * Picks a random post type.
	 *
	 * @param string[]|string $haystack Post types to pick from; empty uses every public post type with an admin UI, except attachments.
	 *
	 * @return string The post type.
	 */
	public function post_type( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			// Later on we will remove the Attachment rule.
			$haystack = array_diff(
				get_post_types(
					[
						'public'  => true,
						'show_ui' => true,
					],
					'names' 
				),
				[ 'attachment' ] 
			);
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Picks a random post status.
	 *
	 * @param string[]|string $haystack Statuses to pick from; empty uses every registered post status.
	 *
	 * @return string The post status.
	 */
	public function post_status( $haystack = [ 'draft', 'publish', 'private' ] ) {
		if ( empty( $haystack ) ) {
			$haystack = array_values( get_post_stati() );
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Generates a random post date within an interval.
	 *
	 * An invalid start falls back to the start of today, a missing or invalid end to now, and an
	 * end without a time is pushed to the end of that day.
	 *
	 * @param string[]|string $interval Start date string, or [ start, end ] date strings.
	 *
	 * @return string The date as `Y-m-d H:i:s`.
	 */
	public function post_date( $interval = 'now' ) {
		$format   = 'Y-m-d H:i:s';
		$interval = (array) $interval;

		// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
		try {
			$min = new Chronos( array_shift( $interval ) );
		} catch ( \Exception $e ) {
			$min = new Chronos( 'today' );
			$min = $min->startOfDay();
		}

		if ( ! empty( $interval ) ) {
			// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
			try {
				$max = new Chronos( array_shift( $interval ) );
			} catch ( \Exception $e ) {
				// Invalid end date, fall back to "now" below.
				$max = null;
			}
		}

		if ( ! isset( $max ) ) {
			$max = new Chronos( 'now' );
		}

		// If max has no Time set it to the end of the day.
		$max_has_time = array_filter( [ $max->hour, $max->minute, $max->second ] );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ) {
			$max = $max->endOfDay();
		}

		return $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );
	}

	/**
	 * Generates the post content.
	 *
	 * @param bool  $html Whether to build HTML elements (true) or plain paragraphs.
	 * @param array $args Arguments for HTML::html_elements(); `qty` (default [ 5, 15 ]) also sets the paragraph count for plain text.
	 *
	 * @return string The content.
	 */
	public function post_content( $html = true, $args = [] ) {
		$defaults = [
			'qty' => [ 5, 15 ],
		];
		$args     = wp_parse_args( $args, $defaults );

		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( make( Utils::class )->get_qty_from_range( $args['qty'] ) ) );
		}

		return $content;
	}

	/**
	 * Generates an excerpt of one to four sentences, or an empty string for the posts left without one.
	 *
	 * @since 0.4.9
	 *
	 * @param array|int $qty    Number of words per sentence, or a [ min, max ] range to pick it from.
	 * @param boolean   $html   Should use HTML or not (currently not used).
	 * @param integer   $weight Percentage of posts that get an excerpt.
	 *
	 * @return string The excerpt, or an empty string.
	 */
	public function post_excerpt( $qty = [ 25, 75 ], $html = false, $weight = 60 ) {
		$words      = make( Utils::class )->get_qty_from_range( $qty );
		$paragraphs = $this->generator->randomElement( [ 1, 1, 1, 1, 1, 2, 2, 2, 3, 4 ] );

		for ( $i = 0; $i < $paragraphs; $i++ ) {
			$excerpt[ $i ] = $this->generator->sentence( $words );
		}

		$excerpt = implode( "\n\n", $excerpt );

		return $this->generator->optional( $weight / 100, '' )->randomElement( (array) $excerpt );
	}

	/**
	 * Picks a random author ID.
	 *
	 * @param int[]|int $haystack User IDs to pick from; empty picks from every user of the current site.
	 *
	 * @return int|string|null The user ID, or null when there is nothing to pick from.
	 */
	public function post_author( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = get_users(
				[
					'blog_id'     => get_current_blog_id(),
					'count_total' => false,
					'fields'      => 'ID', // When you pass only one field it returns an array of the values.
				]
			);
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Picks a parent post ID, or 0 for a top-level post.
	 *
	 * @param int[]|int $haystack Post IDs to pick from.
	 * @param int       $weight   Percentage (0-100) of posts that get a parent.
	 *
	 * @return int|null The parent post ID, 0, or null when `$haystack` is empty.
	 */
	public function post_parent( $haystack = [], $weight = 70 ) {
		return $this->generator->optional( $weight / 100, 0 )->randomElement( (array) $haystack );
	}

	/**
	 * Picks a ping status.
	 *
	 * @param string[]|string $haystack Statuses to pick from; empty uses `closed` and `open`.
	 *
	 * @return string The ping status.
	 */
	public function ping_status( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = static::$default['ping_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Picks a comment status.
	 *
	 * @param string[]|string $haystack Statuses to pick from; empty uses `closed` and `open`.
	 *
	 * @return string The comment status.
	 */
	public function comment_status( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = static::$default['comment_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Picks a menu order.
	 *
	 * @param int[]|int $haystack Values to pick from; empty returns 0.
	 *
	 * @return int The menu order.
	 */
	public function menu_order( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			return 0;
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	/**
	 * Builds a post password with a callback.
	 *
	 * @param callable|null $generator Callback that returns the password; null means no password.
	 * @param array         $args      Arguments passed to the callback.
	 *
	 * @return string The password, or an empty string when there is no callback.
	 */
	public function post_password( $generator = null, $args = [] ) {
		if ( is_null( $generator ) ) {
			return '';
		}

		return call_user_func_array( $generator, $args );
	}

	/**
	 * Picks the terms to assign to a post, per taxonomy.
	 *
	 * Each rule in `$config` names taxonomies and, optionally, the term IDs to use (otherwise every
	 * term of the taxonomy), a `qty` count or range, and a `rate` percentage of posts that get terms.
	 * Without `qty` or `rate`, the defaults come from the `fakerpress/provider/WP_Post/tax_input.ranges`
	 * and `fakerpress/provider/WP_Post/tax_input.rates` filters.
	 *
	 * @param array[]|null $config Taxonomy rules, each with `taxonomies`, and optional `terms`, `qty` and `rate`.
	 *
	 * @return array<string, int[]|null> Term IDs keyed by taxonomy (null when the rate skipped it), or an empty array without rules.
	 */
	public function tax_input( $config = null ) {
		$output = [];
		if ( is_null( $config ) ) {
			return $output;
		}

		// The percentage of change in which the terms will be applied.
		$rates = apply_filters(
			'fakerpress/provider/WP_Post/tax_input.rates',
			[
				'category'  => 50,
				'post_tag'  => 45,
				'__default' => 35,
			] 
		);

		// The amount of terms that might have, provide a number for exact and [ int, int ] to range.
		$ranges = apply_filters(
			'fakerpress/provider/WP_Post/tax_input.ranges',
			[
				'category'  => [ 1, 3 ],
				'post_tag'  => [ 0, 15 ],
				'__default' => [ 0, 3 ],
			] 
		);

		foreach ( $config as $settings ) {
			$settings               = (object) $settings;
			$settings->taxonomies ??= [];
			$settings->terms      ??= [];

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
					$terms = get_terms(
						[
							'taxonomy'   => $taxonomy,
							'fields'     => 'ids',
							'hide_empty' => false,
						]
					);
				} else {
					$terms = $settings->terms;
				}

				// Get all the term ids.
				$terms = array_filter( array_map( 'absint', $terms ) );

				if ( ! isset( $settings->qty ) ) {
					$qty = make( Utils::class )->get_qty_from_range( ( $ranges[ $taxonomy ] ?? $ranges['__default'] ), $terms );
				} else {
					$qty = (int) make( Utils::class )->get_qty_from_range( $settings->qty, $terms );
				}

				if ( ! isset( $settings->rate ) ) {
					$rate = $rates[ $taxonomy ] ?? $rates['__default'];
				} else {
					$rate = (int) $settings->rate;
				}

				// Select the elements based on qty.
				$output[ $taxonomy ] = $this->generator->optional( ( (int) $rate ) / 100, null )->randomElements( $terms, (int) $qty );
			}
		}

		return $output;
	}
}
