<?php
namespace Faker\Provider;

class WP_Meta extends Base {
	private function meta_parse_qty( $qty, $elements = null ){
		$_qty = array_filter( (array) $qty );
		$min = reset( $_qty );

		$qty = (int) ( is_array( $qty ) && count( $_qty ) > 1 ? call_user_func_array( array( $this->generator, 'numberBetween' ), $qty ) : reset( $_qty ) );
		if ( $qty < $min ){
			$qty = $min;
		}

		if ( is_array( $elements ) && $qty > count( $elements ) ){
			$qty = count( $elements );
		}

		return $qty;
	}

	private function meta_parse_separator( $separator ){
		$separator = stripcslashes( $separator );

		$search = array(
			'\n',
			'\r',
			'\t',
		);
		$replace = array(
			"\n",
			"\r",
			"\t",
		);
		$separator = str_replace( $search, $replace, $separator );
		return $separator;
	}

	public function meta_type_numbers( $number = array( 0, 9 ), $weight = 50 ) {
		$number = ( is_array( $number ) ? call_user_func_array( array( $this->generator, 'numberBetween' ), $number ) : $number );
		$weight = $weight / 100;

		return $this->generator->optional( $weight, null )->randomElement( (array) $number );
	}

	public function meta_type_elements( $elements = '', $qty = 1, $separator = ',', $weight = 50 ) {
		$weight = $weight / 100;
		$separator = $this->meta_parse_separator( $separator );

		$elements = explode( ',', $elements );
		$qty = $this->meta_parse_qty( $qty, $elements );

		$value = $this->generator->optional( $weight, null )->randomElements( (array) $elements, $qty );
		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	public function meta_type_letter( $weight = 50 ) {
		$weight = $weight / 100;

		return $this->generator->optional( $weight, null )->randomLetter();
	}

	public function meta_type_words( $qty = 8, $weight = 50 ) {
		$weight = $weight / 100;
		$qty = $this->meta_parse_qty( $qty );

		return $this->generator->optional( $weight, null )->sentence( $qty );
	}

	public function meta_type_text( $type = 'sentences', $qty = 3, $separator = "\r\n\r\n", $weight = 50 ) {
		$weight = $weight / 100;
		$separator = $this->meta_parse_separator( $separator );
		$qty = $this->meta_parse_qty( $qty );

		if ( 'sentences' === $type ){
			$value = $this->generator->optional( $weight, null )->sentences( $qty );
		} else {
			$value = $this->generator->optional( $weight, null )->paragraphs( $qty );
		}

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	public function meta_type_html( $elements, $qty = 6, $weight = 50 ) {
		$weight = $weight / 100;
		$qty = $this->meta_parse_qty( $qty );
		$elements = explode( ',', $elements );

		$value = $this->generator->optional( $weight, null )->html_elements( array(
			'elements' => $elements,
			'qty' => $qty,
		) );

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( "\n" , $value );
	}

	public function meta_type_lexify( $template, $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->bothify( (string) $template );

		return $value;
	}

	public function meta_type_asciify( $template, $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->asciify( (string) $template );

		return $value;
	}

	public function meta_type_regexify( $template, $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->regexify( (string) $template );

		return $value;
	}

}