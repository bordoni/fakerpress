<?php
namespace FakerPress\Provider;

use Faker\Provider\Base;
use FakerPress\Utils;

class WP_Comment extends Base {

	public function comment_content( $html = true, $args = [] ) {
		$defaults = [
			'qty' => [ 5, 15 ],
		];
		$args = wp_parse_args( $args, $defaults );

		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( Utils::instance()->get_qty_from_range( $args['qty'] ) ) );
		}

		return $content;
	}

	public function user_id( $users = [] ) {
		// We only need to query if there is no users passed
		if ( is_array( $users ) && empty( $users ) ) {
			$users = get_users(
				[
					'blog_id' => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				]
			);
		}

		// Cast $users as an array and always return an absolute integer
		$user_id = absint( $this->generator->randomElement( (array) $users ) );

		return $user_id;
	}

	public function comment_author( $comment_author = null ) {
		// Lacks the method to random a bunch of elements
		return $this->generator->name();
	}

	public function comment_parent( $comment_parent = null ) {
		// Lacks the method to random a bunch of elements
		return absint( $comment_parent );
	}

	/**
	 * Generate a random comment type with the values given
	 * Converts 'default' into an empty string for default post comments
	 *
	 * @since  0.4.8
	 *
	 * @param  array|string $comment_type Possible comment types to pick from
	 *
	 * @return string
	 */
	public function comment_type( $comment_type = null ) {
		// Fetch a Random element from the possible comment types
		$comment_type = $this->generator->randomElement( (array) $comment_type );

		if ( 'default' === $comment_type || is_null( $comment_type ) ) {
			$comment_type = '';
		}

		return $comment_type;
	}

	public function comment_author_IP( $ip = null ) {
		if ( is_null( $ip ) ) {
			$ip = $this->generator->ipv4;
		}

		return $ip;
	}

	public function comment_agent( $user_agent = null ) {
		if ( is_null( $user_agent ) ) {
			$user_agent = $this->generator->userAgent;
		}

		return $user_agent;
	}

	public function comment_approved( $comment_approved = 1 ) {

		return $comment_approved;
	}

	/**
	 * Generates a Post ID for the Comment
	 *
	 * @since  0.1.0
	 * @since  0.4.8 Argument `$args` to allow custom Post Types
	 *
	 * @param  array|int $comment_post_ID Which ids you want to use
	 * @param  array     $args            WP_Query args for Searching these Posts
	 *
	 * @return int
	 */
	public function comment_post_ID( $comment_post_ID = null, $args = [] ) {

		if ( is_null( $comment_post_ID ) ) {
			// We should be able to pass these arguments
			$defaults = [
				'posts_per_page'   => -1,
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'suppress_filters' => true,
			];

			// Apply the defaults
			$args = wp_parse_args( $args, $defaults );

			// We need only the IDs here
			$args['fields'] = 'ids';

			$query = new \WP_Query( $args );

			if ( $query->found_posts ) {
				$comment_post_ID = absint( $this->generator->randomElement( $query->posts, 1 ) );
			}

			// We need to check if there is no posts, should we include the comment anyways?
		}

		return $comment_post_ID;
	}

	public function comment_author_email( $author_email = null ) {
		if ( is_null( $author_email ) ) {
			$author_email = $this->generator->safeEmail;
			$author_email = substr( $author_email, 0, strlen( $author_email ) - 1 );
		}

		return $author_email;
	}

	public function comment_author_url( $author_url = null ) {
		if ( is_null( $author_url ) ) {
			$author_url = $this->generator->url;
			$author_url = substr( $author_url, 0, strlen( $author_url ) - 1 );
		}

		return $author_url;
	}

	public function comment_date( $min = 'now', $max = null ) {
		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $min );
		} catch ( Exception $e ) {
			return null;
		}

		if ( ! is_null( $max ) ) {
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
			} catch ( Exception $e ) {
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