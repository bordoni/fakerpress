<?php
namespace Faker\Provider;

class WP_Attachment extends Base {
	public function post_type() {
		return 'attachment';
	}

	protected static $default = array(
		'ping_status' => array( 'closed', 'open' ),
		'comment_status' => array( 'closed', 'open' ),
	);

	private static $type_defaults = array(
		'placeholdit' => array(
			'width' => array( 200, 640 ),
			'height' => 1.25,
		),
		'lorempixel' => array(
			'width' => array( 200, 640 ),
			'height' => 1.25,
		),
		'unsplashit' => array(
			'width' => array( 1024, 1440 ),
			'height' => 1.5,
		),
		'500px' => array()
	);

	public function attachment_url( $type = '500px', $args = array() ) {
		$url = '';

		// Check if defaults exists
		if ( ! isset( self::$type_defaults[ $type ] ) ){
			return $url;
		}

		$args = wp_parse_args( $args, self::$type_defaults[ $type ] );

		if ( 'placeholdit' === $type ){
			$url = call_user_func_array( array( $this->generator, 'placeholdit' ), (array) $args );
		} elseif ( 'unsplashit' === $type ){
			$url = call_user_func_array( array( $this->generator, 'unsplashit' ), (array) $args );
		} elseif ( 'lorempixel' === $type ){
			$url = call_user_func_array( array( $this->generator, 'lorempixel' ), (array) $args );
		} elseif ( '500px' === $type ){
			$url = call_user_func_array( array( $this->generator, 'image_500px' ), (array) $args );
		}

		return $url;
	}

	public function post_title( $qty_words = 5 ) {
		$title = $this->generator->sentence( $qty_words );
		$title = substr( $title, 0, strlen( $title ) - 1 );

		return $title;
	}

	public function post_content( $html = true, $args = array() ) {
		if ( true === $html ){
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $content;
	}

	public function post_author( $haystack = array() ) {
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

	public function post_status() {
		return 'inherit';
	}

	public function post_parent( $haystack = array(), $rate = 70 ) {
		return $this->generator->numberBetween( 0, 100 ) < absint( $rate ) ? 0 : $this->generator->randomElement( (array) $haystack );
	}

	public function ping_status( $haystack = array() ) {
		if ( empty( $haystack ) ){
			$haystack = static::$default['ping_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function comment_status( $haystack = array() ) {
		if ( empty( $haystack ) ){
			$haystack = static::$default['comment_status'];
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function menu_order( $haystack = array() ) {
		if ( empty( $haystack ) ){
			return 0;
		}

		return $this->generator->randomElement( (array) $haystack );
	}

	public function post_password( $generator = null, $args = array() ) {
		if ( is_null( $generator ) ){
			return '';
		}

		return call_user_func_array( $generator, $args );
	}

	public function post_date( $min = 'now', $max = null ) {
		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $min );
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! is_null( $max ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
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