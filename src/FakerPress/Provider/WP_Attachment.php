<?php

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\Provider\Image\LoremPicsum;
use FakerPress\Provider\Image\Placeholder;
use function FakerPress\carbon;

class WP_Attachment extends Base {
	public function post_type() {
		return 'attachment';
	}

	protected static $default = [
		'ping_status'    => [ 'closed', 'open' ],
		'comment_status' => [ 'closed', 'open' ],
	];

	/**
	 * Hold the default width and height for the diff providers.
	 *
	 * @since  0.1.0
	 * @since  0.5.0 now it's a public static var.
	 *
	 * @var array
	 */
	public static $type_defaults = [
		Placeholder::ID => [
			'width'  => [ 200, 640 ],
			'height' => 1.25,
		],
		LoremPicsum::ID       => [
			'width'  => [ 1024, 1440 ],
			'height' => 1.5,
		],
	];

	public function attachment_url( $type = LoremPicsum::ID, $args = [] ) {
		$url = '';

		// Check if defaults exists
		if ( ! isset( self::$type_defaults[ $type ] ) ) {
			return $url;
		}

		$args = wp_parse_args( $args, static::$type_defaults[ $type ] );

		if ( Placeholder::ID === $type ) {
			$url = call_user_func_array( [ $this->generator, Placeholder::ID ], (array) $args );
		} elseif ( LoremPicsum::ID === $type ) {
			$url = call_user_func_array( [ $this->generator, LoremPicsum::ID ], (array) $args );
		}

		return $url;
	}

	public function post_title( $qty_words = 5 ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		return $title;
	}

	public function post_content( $html = true, $args = [] ) {
		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
	}

	public function post_author( $haystack = [] ) {
		if ( empty( $haystack ) ) {
			$haystack = get_users(
				[
					'blog_id'     => get_current_blog_id(),
					'count_total' => false,
					'fields'      => 'ID', // When you pass only one field it returns an array of the values
				]
			);
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_status() {
		return 'inherit';
	}

	public function post_parent( $haystack = [], $rate = 70 ) {
		return $this->generator->numberBetween( 0, 100 ) < absint( $rate ) ? 0 : $this->generator->randomElement( (array) $haystack );
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
