<?php
/**
 * Faker provider for WordPress attachment fields.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\Provider\Image\LoremPicsum;
use FakerPress\Provider\Image\Placeholder;
use function FakerPress\carbon;

/**
 * Generates the fields of a fake attachment post, including the remote image URL to download.
 */
class WP_Attachment extends Base {
	/**
	 * Returns the post type for attachments.
	 *
	 * @return string Always `attachment`.
	 */
	public function post_type() {
		return 'attachment';
	}

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
	 * Hold the default width and height for the diff providers.
	 *
	 * @since 0.1.0
	 * @since 0.5.0 now it's a public static var.
	 *
	 * @var array
	 */
	public static $type_defaults = [
		Placeholder::ID => [
			'width'  => [ 200, 640 ],
			'height' => 1.25,
		],
		LoremPicsum::ID => [
			'width'  => [ 1024, 1440 ],
			'height' => 1.5,
		],
	];

	/**
	 * Builds the remote image URL for an image provider.
	 *
	 * @param string $type Image provider ID: Placeholder::ID or LoremPicsum::ID.
	 * @param array  $args Arguments for the provider formatter (`width`, `height`), merged over the provider defaults.
	 *
	 * @return string The image URL, or an empty string for an unknown provider.
	 */
	public function attachment_url( $type = LoremPicsum::ID, $args = [] ) {
		$url = '';

		// Check if defaults exists.
		if ( ! isset( self::$type_defaults[ $type ] ) ) {
			return $url;
		}

		$args = wp_parse_args( $args, static::$type_defaults[ $type ] );

		if ( Placeholder::ID === $type ) {
			$url = call_user_func_array( [ $this->generator, 'placeholder' ], (array) $args );
		} elseif ( LoremPicsum::ID === $type ) {
			$url = call_user_func_array( [ $this->generator, 'lorempicsum' ], (array) $args );
		}

		return $url;
	}

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
	 * Generates the attachment content (description).
	 *
	 * @param bool  $html Whether to build HTML elements (true) or plain paragraphs.
	 * @param array $args Arguments for HTML::html_elements(), only used for HTML content.
	 *
	 * @return string The content.
	 */
	public function post_content( $html = true, $args = [] ) {
		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
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
	 * Returns the post status for attachments.
	 *
	 * @return string Always `inherit`.
	 */
	public function post_status() {
		return 'inherit';
	}

	/**
	 * Picks a parent post ID, or 0 for an unattached file.
	 *
	 * @param int[]|int $haystack Post IDs to pick from.
	 * @param int       $rate     Percentage (0-100) of attachments that stay unattached.
	 *
	 * @return int|null The parent post ID, 0, or null when `$haystack` is empty.
	 */
	public function post_parent( $haystack = [], $rate = 70 ) {
		return $this->generator->numberBetween( 0, 100 ) < absint( $rate ) ? 0 : $this->generator->randomElement( (array) $haystack );
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
	 * Generates a post date.
	 *
	 * Relies on a `FakerPress\carbon()` helper to parse the date strings.
	 *
	 * @param string      $min Date string for the start of the range, or the exact date when `$max` is null.
	 * @param string|null $max Date string for the end of the range.
	 *
	 * @return string|null The date as `Y-m-d H:i:s` (or `$min` cast to string), or null when `$min` is invalid.
	 */
	public function post_date( $min = 'now', $max = null ) {
		$min = carbon( $min );
		if ( is_wp_error( $min ) ) {
			return null;
		}

		if ( ! is_null( $max ) ) {
			$max = carbon( $max );
		}

		if ( ! is_null( $max ) ) {
			$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( 'Y-m-d H:i:s' );
		} else {
			$selected = (string) $min;
		}

		return $selected;
	}
}
