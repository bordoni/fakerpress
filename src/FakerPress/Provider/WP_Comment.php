<?php
/**
 * Faker provider for WordPress comment fields.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\Utils;
use function FakerPress\make;

/**
 * Generates the fields of a fake comment: content, author details, post, type and date.
 */
class WP_Comment extends Base {

	/**
	 * Generates the comment content.
	 *
	 * @param bool  $html Whether to build HTML elements (true) or plain paragraphs.
	 * @param array $args Arguments for HTML::html_elements(); `qty` (default [ 5, 15 ]) also sets the paragraph count for plain text.
	 *
	 * @return string The content.
	 */
	public function comment_content( $html = true, $args = [] ) {
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
	 * Picks the ID of the user who wrote the comment.
	 *
	 * @param int[]|int $users User IDs to pick from; an empty array picks from every user of the current site.
	 *
	 * @return int The user ID, or 0 when there is nothing to pick from.
	 */
	public function user_id( $users = [] ) {
		// We only need to query if there is no users passed.
		if ( is_array( $users ) && empty( $users ) ) {
			$users = get_users(
				[
					'blog_id'     => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields'      => 'ID', // When you pass only one field it returns an array of the values.
				]
			);
		}

		// Cast $users as an array and always return an absolute integer.
		return absint( $this->generator->randomElement( (array) $users ) );
	}

	/**
	 * Generates a random author name.
	 *
	 * @param string|null $comment_author Not used; a random name is always generated.
	 *
	 * @return string The author name.
	 */
	public function comment_author( $comment_author = null ) {
		// Lacks the method to random a bunch of elements.
		return $this->generator->name();
	}

	/**
	 * Returns the parent comment ID as an absolute integer.
	 *
	 * @param int|string|null $comment_parent Parent comment ID; null gives 0 (no parent).
	 *
	 * @return int The parent comment ID.
	 */
	public function comment_parent( $comment_parent = null ) {
		// Lacks the method to random a bunch of elements.
		return absint( $comment_parent );
	}

	/**
	 * Generate a random comment type with the values given
	 * Converts 'default' into an empty string for default post comments
	 *
	 * @since 0.4.8
	 *
	 * @param array|string $comment_type Possible comment types to pick from.
	 *
	 * @return string The comment type, empty for a regular comment.
	 */
	public function comment_type( $comment_type = null ) {
		// Fetch a Random element from the possible comment types.
		$comment_type = $this->generator->randomElement( (array) $comment_type );

		if ( 'default' === $comment_type || is_null( $comment_type ) ) {
			$comment_type = '';
		}

		return $comment_type;
	}

	/**
	 * Returns the author IP, generating a random IPv4 address when none is given.
	 *
	 * @param string|null $ip IP address to use as is; null generates one.
	 *
	 * @return string The IP address.
	 */
	public function comment_author_IP( $ip = null ) {
		if ( is_null( $ip ) ) {
			$ip = $this->generator->ipv4();
		}

		return $ip;
	}

	/**
	 * Returns the author's browser user agent, generating one when none is given.
	 *
	 * @param string|null $user_agent User agent to use as is; null generates one.
	 *
	 * @return string The user agent.
	 */
	public function comment_agent( $user_agent = null ) {
		if ( is_null( $user_agent ) ) {
			$user_agent = $this->generator->userAgent();
		}

		return $user_agent;
	}

	/**
	 * Returns the approval status as given.
	 *
	 * @param int|string $comment_approved Approval status (1, 0, `spam`, ...).
	 *
	 * @return int|string The approval status.
	 */
	public function comment_approved( $comment_approved = 1 ) {

		return $comment_approved;
	}

	// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- $comment_post_ID mirrors the WordPress comment field and is a public parameter name.

	/**
	 * Generates a Post ID for the Comment
	 *
	 * @since 0.1.0
	 * @since 0.4.8 Argument `$args` to allow custom Post Types
	 *
	 * @param array|int|null $comment_post_ID Post ID to use as is; null picks a random published post.
	 * @param array          $args            WP_Query args for Searching these Posts.
	 *
	 * @return array|int|null The post ID, or null when no post matched.
	 */
	public function comment_post_ID( $comment_post_ID = null, $args = [] ) {

		if ( is_null( $comment_post_ID ) ) {
			// We should be able to pass these arguments.
			$defaults = [
				'posts_per_page'   => -1,
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'suppress_filters' => true, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.SuppressFilters_suppress_filters -- Existing behaviour, the random post pool ignores third-party query filters.
			];

			// Apply the defaults.
			$args = wp_parse_args( $args, $defaults );

			// We need only the IDs here.
			$args['fields'] = 'ids';

			$query = new \WP_Query( $args );

			if ( $query->found_posts ) {
				$comment_post_ID = absint( $this->generator->randomElement( $query->posts, 1 ) );
			}

			// We need to check if there is no posts, should we include the comment anyways?
		}

		return $comment_post_ID;
	}

	// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

	/**
	 * Returns the author email, generating one when none is given.
	 *
	 * The generated address has its last character removed.
	 *
	 * @param string|null $author_email Email to use as is; null generates one.
	 *
	 * @return string The email.
	 */
	public function comment_author_email( $author_email = null ) {
		if ( is_null( $author_email ) ) {
			$author_email = $this->generator->safeEmail();
			$author_email = substr( $author_email, 0, strlen( $author_email ) - 1 );
		}

		return $author_email;
	}

	/**
	 * Returns the author URL, generating one when none is given.
	 *
	 * The generated URL has its last character removed.
	 *
	 * @param string|null $author_url URL to use as is; null generates one.
	 *
	 * @return string The URL.
	 */
	public function comment_author_url( $author_url = null ) {
		if ( is_null( $author_url ) ) {
			$author_url = $this->generator->url();
			$author_url = substr( $author_url, 0, strlen( $author_url ) - 1 );
		}

		return $author_url;
	}

	/**
	 * Generates a comment date.
	 *
	 * @param string      $min Date string for the start of the range, or the exact date when `$max` is null.
	 * @param string|null $max Date string for the end of the range; null returns `$min` as is.
	 *
	 * @return string|null The date as `Y-m-d H:i:s` (or `$min` cast to string), or null when a date string is invalid.
	 */
	public function comment_date( $min = 'now', $max = null ) {
		// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
		try {
			$min = new Chronos( $min );
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! is_null( $max ) ) {
			// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
			try {
				$max = new Chronos( $max );
			} catch ( \Exception $e ) {
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
