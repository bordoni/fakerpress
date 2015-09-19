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

	public function meta_type_wp_query( $query, $weight = 50 ) {
		$weight = $weight / 100;

		$args = wp_parse_args( $query, array() );
		$args['fields'] = 'ids';

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ){
			return null;
		}

		$value = $this->generator->optional( $weight, null )->randomElement( (array) $query->posts );

		return $value;
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

	public function meta_type_timezone( $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->timezone;

		return $value;
	}

	public function meta_type_company( $template, $weight = 50 ) {
		$weight = $weight / 100;

		$template = explode( '|', $template );

		$tags = array(
			'suffix',
			'company',
			'bs',
			'catch_phrase',
		);

		$text = array();

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ){
				list( $element, $term ) = $_parsed;
				switch ( $term ) {
					case 'suffix':
						$text[] = $this->generator->companySuffix;
						break;
					case 'company':
						$text[] = $this->generator->company;
						break;
					case 'bs':
						$text[] = $this->generator->bs;
						break;
					case 'catch_phrase':
						$text[] = $this->generator->catchPhrase;
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		$value = $this->generator->optional( $weight, null )->randomElement( (array) implode( '', $text ) );

		return $value;
	}

	public function meta_type_person( $template, $gender = 'female', $weight = 50 ) {
		$weight = $weight / 100;

		$template = explode( '|', $template );

		$tags = array(
			'title',
			'first_name',
			'last_name',
			'suffix',
		);

		$text = array();

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ){
				list( $element, $term ) = $_parsed;
				switch ( $term ) {
					case 'title':
						$text[] = $this->generator->title( $gender );
						break;
					case 'first_name':
						$text[] = $this->generator->firstName( $gender );
						break;
					case 'last_name':
						$text[] = $this->generator->lastName;
						break;
					case 'suffix':
						$text[] = $this->generator->suffix;
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		$value = $this->generator->optional( $weight, null )->randomElement( (array) implode( '', $text ) );

		return $value;
	}

	public function meta_type_geo( $template, $weight = 50 ) {
		$weight = $weight / 100;
		$template = explode( '|', $template );
		$tags = array(
			'country',
			'city_prefix',
			'city_suffix',
			'city',
			'state',
			'state_abbr',
			'address',
			'secondary_address',
			'building_number',
			'street_name',
			'street_address',
			'postalcode',
			'latitude',
			'longitude',
		);

		$text = array();

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ){
				list( $element, $term ) = $_parsed;
				switch ( $term ) {
					case 'country':
						$text[] = $this->generator->country;
						break;
					case 'city_prefix':
						$text[] = $this->generator->cityPrefix;
						break;
					case 'city_suffix':
						$text[] = $this->generator->citySuffix;
						break;
					case 'city':
						$text[] = $this->generator->city;
						break;
					case 'state':
						$text[] = $this->generator->state;
						break;
					case 'state_abbr':
						$text[] = $this->generator->stateAbbr;
						break;
					case 'address':
						$text[] = $this->generator->address;
						break;
					case 'secondary_address':
						$text[] = $this->generator->secondaryAddress;
						break;
					case 'building_number':
						$text[] = $this->generator->buildingNumber;
						break;
					case 'street_name':
						$text[] = $this->generator->streetName;
						break;
					case 'street_address':
						$text[] = $this->generator->streetAddress;
						break;
					case 'postalcode':
						$text[] = $this->generator->postcode;
						break;
					case 'latitude':
						$text[] = $this->generator->latitude;
						break;
					case 'longitude':
						$text[] = $this->generator->longitude;
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		$value = $this->generator->optional( $weight, null )->randomElement( (array) implode( '', $text ) );

		return $value;
	}

	public function meta_type_date( $interval, $format = 'Y-m-d H:i:s', $weight = 50 ) {
		$interval = (array) $interval;

		// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
		try {
			$min = new \Carbon\Carbon( $interval['min'] );
		} catch ( \Exception $e ) {
			$min = new \Carbon\Carbon( 'today' );
			$min = $min->startOfDay();
		}

		if ( ! empty( $interval ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $interval['max'] );
			} catch ( \Exception $e ) {}
		}

		if ( ! isset( $max ) ) {
			$max = new \Carbon\Carbon( 'now' );
		}

		// If max has no Time set it to the end of the day
		$max_has_time = array_filter( array( $max->hour, $max->minute, $max->second ) );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ){
			$max = $max->endOfDay();
		}

		$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );

		$value = $this->generator->optional( $weight, null )->randomElement( (array) $selected );

		return $value;
	}

	public function meta_type_ip( $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->ipv4;

		return $value;
	}

	public function meta_type_domain( $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->domainName;

		return $value;
	}

	public function meta_type_email( $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->email;

		return $value;
	}

	public function meta_type_user_agent( $weight = 50 ) {
		$weight = $weight / 100;

		$value = $this->generator->optional( $weight, null )->userAgent;

		return $value;
	}

	public function meta_type_raw( $weight = 100, $value = null, $default = null ) {
		if ( $weight >= $this->generator->numberBetween( 0, 100 ) ) {
			return $value;
		} else {
			return $default;
		}

	}

}
