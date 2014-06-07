<?php
namespace Faker\Provider;

class WP_Comment extends Base {

	public function comment_content( $comment_content = null, $max_chars = 200 ) {
		if ( $comment_content == null ){
			$comment_content = $this->generator->text( $max_chars );
			$comment_content = substr( $comment_content, 0, strlen( $comment_content ) - 1 );
		}
		
		return $comment_content;
	}

	public function user_id( $users = array() ) {
		// We only need to query if there is no users passed
		if ( empty( $users ) ){
			$users = get_users(
				array(
					'blog_id' => $GLOBALS['blog_id'],
					'count_total' => false,
					'fields' => 'ID', // When you pass only one field it returns an array of the values
				)
			);
		}

		// Cast $users as an array and always return an absolute integer
		$user_id = absint( $this->generator->randomElement( (array) $users ) );

		return $user_id;
	}

	public function comment_author( $comment_author = null ) {

		return absint( $comment_author );
	}

	public function comment_parent( $comment_parent = null ) {
		
		return absint( $comment_parent );
	}

	public function comment_author_IP( $ip = null ) {

		if ( $ip == null ){
			$ip = $this->generator->ipv4;
		}

		return $ip;
	}

	public function comment_agent( $user_agent = null ) {

		if ( $user_agent == null ){
			$user_agent = $this->generator->userAgent;
		}

		return $user_agent;
	}

	public function comment_approved( $comment_approved = 1 ) {

		return $comment_approved;
	}

	public function comment_post_ID( $comment_post_ID = null ) {
		if ( $comment_post_ID == null ){
			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'suppress_filters' => true,
			);

			$posts = get_posts( $args );

			if ( $posts ){
				foreach ( $posts as $post ){
					$post_id[] = $post->ID;
				}
			}

			if ( $post_id ){
				$comment_post_ID = absint( $this->generator->randomElement( $post_id, 1 ) );
			}
		}

		return $comment_post_ID;
	}

	public function comment_author_email( $author_email = null, $max_chars = 200 ) {
		if ( $author_email == null ){
			$author_email = $this->generator->safeEmail;
			$author_email = substr( $author_email, 0, strlen( $author_email ) - 1 );
		}

		return $author_email;
	}

	public function comment_author_url( $author_url = null, $max_chars = 200 ) {
		if ( $author_url == null ){
			$author_url = $this->generator->url;
			$author_url = substr( $author_url, 0, strlen( $author_url ) - 1 );
		}

		return $author_url;
	}

	public function comment_date( $min = 'now', $max = null ){
		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $min );
		} catch (Exception $e) {
			return null;
		}

		if ( ! is_null( $max ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
			} catch (Exception $e) {
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