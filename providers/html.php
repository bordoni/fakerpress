<?php
namespace Faker\Provider;

class HTML extends Base {
	/**
	 * @param \Faker\Generator $generator
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;

		$provider = new Internet( $this->generator );
		$this->generator->addProvider( $provider );

		$provider = new PlaceHoldIt( $this->generator );
		$this->generator->addProvider( $provider );
	}

	static public $sets = array(
		'self_close' => array( 'img', 'hr' ),
		'header' => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
		'list' => array( 'ul', 'ol' ),
		'block' => array( 'div', 'p', 'blockquote' ),
		'item' => array( 'li' ),
		'inline' => array(
			'b', 'big', 'i', 'small', 'tt',
			'abbr', 'cite', 'code', 'em', 'strong',
			'a', 'bdo', 'br', 'img', 'q', 'span', 'sub', 'sup',
			'hr',
		)
	);

	public function html_elements( $args = array() ){
		$html = array();

		$defaults = array(
			'qty' => Base::randomNumber( 5, 25 ),
			'elements' => array_merge( self::$sets['header'], self::$sets['list'], self::$sets['block'] ),
			'attr' => array(),
			'exclude' => array( 'div' ),
		);
		$args = (object) wp_parse_args( $args, $defaults );

		for ( $i = 0; $i < $args->qty; $i++ ) {
			$exclude = $args->exclude;
			if ( isset( $element ) ){
				// Here we check if we need to exclude some elements from the next
				// This one is to exclude header elements from apearing one after the other, or in the end of the string
				if ( in_array( $element, self::$sets['header'] ) || $args->qty - 1 === $i ){
					$exclude = array_merge( (array) $exclude, self::$sets['header'] );
				} elseif ( $i > 1 && ( in_array( $els[$i - 1], self::$sets['list'] ) || in_array( $els[$i - 2], self::$sets['list'] ) ) ) {
					$exclude = array_merge( (array) $exclude, self::$sets['list'] );
				}
			}
			$els[] = $element = Base::randomElement( array_diff( $args->elements, $exclude ) );

			$html[] = $this->element( $element, $args->attr );
		}

		return (array) $html;
	}

	public function random_apply_element( $element = 'a', $max = 5, $text = null ){
		$words       = explode( ' ', $text );
		$total_words = count( $words );
		$sentences   = array();

		for ( $i = 0; $i < $total_words; $i++ ) {
			$group    = Base::randomNumber( 1, Base::randomNumber( 3, 9 ) );
			$sentence = array();

			for ( $k = 0 ; $k < $group; $k++ ) {
				$index = $i + $k;

				if ( ! isset( $words[$index] ) ){
					break;
				}

				$sentence[] = $words[$index];

			}
			$i += $k;

			$sentences[] = implode( ' ', $sentence );
		}

		$qty = $max - Base::randomNumber( 0, $max );

		if ( $qty === 0 ){
			return $text;
		}

		$indexes = floor( count( $sentences ) / $qty );

		for ( $i = 0; $i < $qty; $i++ ) {
			$index = ( $indexes * $i ) + Base::randomNumber( 0, $indexes );

			if ( isset( $sentences[$index] ) ){
				$sentences[$index] = $this->element( $element, array(), $sentences[$index] );
			}
		}

		return implode( ' ', $sentences );
	}

	public function element( $name = 'div', $attr = array(), $text = null ){
		$element = (object) array(
			'name' => sanitize_html_class( $name ),
			'attr' => $attr,
		);

		$element->one_liner = in_array( $element->name, self::$sets['self_close'] );

		$html = array();

		if ( $element->name === 'a' ){
			if ( ! isset( $element->attr['title'] ) ) {
				$element->attr['title'] = Lorem::sentence( Base::randomNumber( 1, Base::randomNumber( 3, 9 ) ) );
			}
			if ( ! isset( $element->attr['href'] ) ) {
				$element->attr['href'] = $this->generator->url();
			}
		}

		if ( $element->name === 'img' ){
			if ( ! isset( $element->attr['class'] ) ) {
				if ( $this->generator->randomDigit() > 4 ){
					$element->attr['class'][] = $this->generator->randomElement( array( 'aligncenter', 'alignleft', 'alignright' ) );
					$element->attr['class'] = implode( ' ', $element->attr['class'] );
				}
			}

			if ( ! isset( $element->attr['alt'] ) ) {
				$element->attr['alt'] = Lorem::sentence( Base::randomNumber( 1, Base::randomNumber( 3, 9 ) ) );
			}

			if ( ! isset( $element->attr['src'] ) ) {
				$element->attr['src'] = $this->generator->placeholdit();
			}
		}

		$attributes = array();
		foreach ( $element->attr as $key => $value ) {
			$attributes[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}

		$html[] = sprintf( '<%s%s%s>', $element->name, ( ! empty( $attributes ) ? ' ' : '' ) . implode( ' ', $attributes ), ( $element->one_liner ? ' /' : '' ) );

		if ( ! $element->one_liner ) {
			if ( ! is_null( $text ) ){
				$html[] = $text;
			} elseif ( in_array( $element->name, self::$sets['inline'] ) ){
				$text   = Lorem::text( Base::randomNumber( 5, 25 ) );
				$html[] = substr( $text, 0, strlen( $text ) - 1 );
			} elseif ( in_array( $element->name, self::$sets['item'] ) ){
				$text   = Lorem::text( Base::randomNumber( 10, 60 ) );
				$html[] = substr( $text, 0, strlen( $text ) - 1 );
			} elseif ( in_array( $element->name, self::$sets['list'] ) ){
				for ( $i = 0; $i < Base::randomNumber( 1, 15 ); $i++ ) {
					$html[] = $this->element( 'li' );
				}
			} elseif ( in_array( $element->name, self::$sets['header'] ) ){
				$text   = Lorem::text( Base::randomNumber( 60, 200 ) );
				$html[] = substr( $text, 0, strlen( $text ) - 1 );
			} else {
				$html[] = $this->random_apply_element( 'a', Base::randomNumber( 0, 10 ), Lorem::paragraph( Base::randomNumber( 2, 40 ) ) );
			}

			$html[] = sprintf( '</%s>', $element->name );
		}

		return implode( '', $html );
	}

}