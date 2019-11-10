<?php
namespace Faker\Provider;
use FakerPress;
use FakerPress\Utils;

class WP_Meta extends Base {
	public $meta_object = [
		'name' => 'post',
		'id' => 0,
	];

	public function set_meta_object( $name, $id ) {
		$this->meta_object = (object) $this->meta_object;

		$this->meta_object->name = $name;
		$this->meta_object->id = $id;
	}

	private function meta_parse_qty( $qty, $elements = null ) {
		$_qty = array_filter( (array) $qty );
		$min = reset( $_qty );

		$qty = (int) ( is_array( $qty ) && count( $_qty ) > 1 ? call_user_func_array( [ $this->generator, 'numberBetween' ], $qty ) : reset( $_qty ) );
		if ( $qty < $min ) {
			$qty = $min;
		}

		if ( is_array( $elements ) && $qty > count( $elements ) ) {
			$qty = count( $elements );
		}

		return $qty;
	}

	private function meta_parse_separator( $separator ) {
		$separator = stripcslashes( $separator );

		$search = [
			'\n',
			'\r',
			'\t',
		];
		$replace = [
			"\n",
			"\r",
			"\t",
		];
		$separator = str_replace( $search, $replace, $separator );
		return $separator;
	}

	public function meta_type_numbers( $number = [ 0, 9 ], $weight = 50 ) {
		$number = ( is_array( $number ) ? call_user_func_array( [ $this->generator, 'numberBetween' ], $number ) : $number );

		return $this->generator->optional( (int) $weight, null )->randomElement( (array) $number );
	}

	public function meta_type_elements( $elements = '', $qty = 1, $separator = ',', $weight = 50 ) {
		$separator = $this->meta_parse_separator( $separator );

		$elements = explode( ',', $elements );
		$qty = $this->meta_parse_qty( $qty, $elements );

		$value = $this->generator->optional( (int) $weight, null )->randomElements( (array) $elements, $qty );
		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	public function meta_type_letter( $weight = 50 ) {
		return $this->generator->optional( (int) $weight, null )->randomLetter();
	}

	public function meta_type_words( $qty = 8, $weight = 50 ) {
		$qty = $this->meta_parse_qty( $qty );
		$sentence = $this->generator->optional( (int) $weight, '' )->sentence( $qty );

		return Utils::instance()->remove_sentence_period( $sentence );
	}

	public function meta_type_text( $type = 'sentences', $qty = 3, $separator = "\r\n\r\n", $weight = 50 ) {
		$separator = $this->meta_parse_separator( $separator );
		$qty = $this->meta_parse_qty( $qty );

		if ( 'sentences' === $type ) {
			$value = $this->generator->optional( (int) $weight, null )->sentences( $qty );
		} else {
			$value = $this->generator->optional( (int) $weight, null )->paragraphs( $qty );
		}

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	public function meta_type_html( $elements, $qty = 6, $weight = 50 ) {
		$qty = $this->meta_parse_qty( $qty );
		$elements = explode( ',', $elements );

		$value = $this->generator->optional( (int) $weight, null )->html_elements( [
			'elements' => $elements,
			'qty' => $qty,
		] );

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( "\n", $value );
	}

	public function meta_type_wp_query( $query, $weight = 50 ) {
		$args = wp_parse_args( $query, [] );
		$args['fields'] = 'ids';

		// Make easier for Attachment Queries
		if ( isset( $args['post_type'] ) && ! isset( $args['post_status'] ) && in_array( 'attachment', (array) $args['post_type'] ) ) {
			$args['post_status'] = 'any';
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return null;
		}

		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) $query->posts );

		return $value;
	}

	public function meta_type_attachment( $type, $providers, $weight = 50, $width = [], $height = [] ) {
		$providers = array_map( 'esc_attr', array_map( 'trim', explode( ',', $providers ) ) );
		$provider = $this->generator->randomElement( $providers );

		$attachment = FakerPress\Module\Attachment::instance();

		$arguments = [];

		// Specially for Meta we do the Randomization here
		if ( ! empty( $width )&& $this->meta_parse_qty( $width ) ) {
			$arguments['width'] = $this->meta_parse_qty( $width );
		}

		// Specially for Meta we do the Randomization here
		if ( ! empty( $height ) && $this->meta_parse_qty( $height ) ) {
			$arguments['height'] = $this->meta_parse_qty( $height );
		}

		// Generate the Attachment
		$attachment->set( 'attachment_url', $provider, $arguments );

		// If it's meta for a post we need to mark the attachment as child of that post
		if ( 'post' === $this->meta_object->name ) {
			$attachment->set( 'post_parent', $this->meta_object->id, 1 );
		}

		// Actually save the Attachment and get it's ID
		$value = $attachment->generate()->save();

		// If there was an error, just bail now
		if ( ! $value ) {
			return null;
		}

		// If asked URL, change to URL
		if ( 'url' === $type ) {
			$value = wp_get_attachment_url( $value );
		}

		// Apply Weight
		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) $value );

		return $value;
	}

	public function meta_type_lexify( $template, $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->bothify( (string) $template );

		return $value;
	}

	public function meta_type_asciify( $template, $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->asciify( (string) $template );

		return $value;
	}

	public function meta_type_regexify( $template, $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->regexify( (string) $template );

		return $value;
	}

	public function meta_type_timezone( $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->timezone;

		return $value;
	}

	public function meta_type_company( $template, $weight = 50 ) {
		$template = explode( '|', $template );

		$tags = [
			'suffix',
			'company',
			'bs',
			'catch_phrase',
		];

		$text = [];

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ) {
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

		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) implode( '', $text ) );

		return $value;
	}

	public function meta_type_person( $template, $gender = 'female', $weight = 50 ) {
		$template = explode( '|', $template );

		$tags = [
			'title',
			'first_name',
			'last_name',
			'suffix',
		];

		$text = [];

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ) {
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

		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) implode( '', $text ) );

		return $value;
	}

	public function meta_type_geo( $template, $weight = 50 ) {
		$template = explode( '|', $template );
		$tags = [
			'country',
			'country_code',
			'country_abbr',
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
		];

		$text = [];

		foreach ( $template as $key => $tag ) {
			preg_match( '|^\{\% *([^\ ]*) *\%\}$|i', $tag, $_parsed );
			if ( ! empty( $_parsed ) ) {
				list( $element, $term ) = $_parsed;
				switch ( $term ) {
					case 'country':
						$text[] = $this->generator->country;
						break;
					case 'country_code':
						$text[] = \FakerPress\Utils::get_country_alpha_code( $this->generator->country, 2 );
						break;
					case 'country_abbr':
						$text[] = \FakerPress\Utils::get_country_alpha_code( $this->generator->country, 3 );
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

		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) implode( '', $text ) );

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

		if ( ! empty( $interval ) ) {
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $interval['max'] );
			} catch ( \Exception $e ) {}
		}

		if ( ! isset( $max ) ) {
			$max = new \Carbon\Carbon( 'now' );
		}

		// If max has no Time set it to the end of the day
		$max_has_time = array_filter( [ $max->hour, $max->minute, $max->second ] );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ) {
			$max = $max->endOfDay();
		}

		$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );

		$value = $this->generator->optional( (int) $weight, null )->randomElement( (array) $selected );

		return $value;
	}

	public function meta_type_ip( $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->ipv4;

		return $value;
	}

	public function meta_type_domain( $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->domainName;

		return $value;
	}

	public function meta_type_email( $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->email;

		return $value;
	}

	public function meta_type_user_agent( $weight = 50 ) {
		$value = $this->generator->optional( (int) $weight, null )->userAgent;

		return $value;
	}

	public function meta_type_raw( $weight = 100, $value = null, $default = null ) {
		if ( (int) $weight >= $this->generator->numberBetween( 0, 100 ) ) {
			return $value;
		} else {
			return $default;
		}

	}

}
