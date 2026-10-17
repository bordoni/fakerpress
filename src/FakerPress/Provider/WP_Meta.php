<?php
/**
 * Faker provider for custom field (meta) values.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress;
use FakerPress\Utils;
use function FakerPress\make;

/**
 * Generates meta values for every meta field type the generator offers (numbers, text, HTML, dates, attachments, ...).
 *
 * Every `meta_type_*()` method takes a `$weight`: the percentage (0-100) of calls that return a value,
 * the rest return null (or an empty string where noted) so the meta is left out.
 */
class WP_Meta extends Base {
	/**
	 * The object the meta is generated for, with its type `name` (post, user, term, comment) and `id`.
	 *
	 * Starts as an array and becomes an object once set_meta_object() runs.
	 *
	 * @var array|object
	 */
	public $meta_object = [
		'name' => 'post',
		'id'   => 0,
	];

	/**
	 * Sets the object the meta values are generated for.
	 *
	 * @param string $name Object type: post, user, term or comment.
	 * @param int    $id   Object ID.
	 *
	 * @return void
	 */
	public function set_meta_object( $name, $id ) {
		$this->meta_object = (object) $this->meta_object;

		$this->meta_object->name = $name;
		$this->meta_object->id   = $id;
	}

	/**
	 * Resolves a quantity from a single number or a [ min, max ] range, capped at the number of elements.
	 *
	 * Empty values in the range are ignored, and the result is never below the first non-empty value.
	 *
	 * @since 0.1.0
	 *
	 * @param string[]|int[]|int|string $qty      Range of numbers or a single number.
	 * @param array|null                $elements Elements the quantity is picked from; caps the result at their count.
	 *
	 * @return int
	 */
	private function meta_parse_qty( $qty, $elements = null ) {
		$qty  = array_values( (array) $qty );
		$_qty = array_filter( $qty );
		$min  = reset( $_qty );

		$qty = (int) ( is_array( $qty ) && count( $_qty ) > 1 ? call_user_func_array( [ $this->generator, 'numberBetween' ], $qty ) : reset( $_qty ) );
		if ( $qty < $min ) {
			$qty = $min;
		}

		if ( is_array( $elements ) && $qty > count( $elements ) ) {
			$qty = count( $elements );
		}

		return $qty;
	}

	/**
	 * Turns a separator typed in the admin into the real string, so `\n`, `\r` and `\t` become line breaks and tabs.
	 *
	 * @param string $separator Separator as typed.
	 *
	 * @return string The separator.
	 */
	private function meta_parse_separator( $separator ) {
		$separator = stripcslashes( $separator );

		$search    = [
			'\n',
			'\r',
			'\t',
		];
		$replace   = [
			"\n",
			"\r",
			"\t",
		];
		$separator = str_replace( $search, $replace, $separator );

		return $separator;
	}

	/**
	 * Given a number or an array of numbers, return a random number between them, and take into account the weight.
	 *
	 * @since 1.0.0
	 *
	 * @param string[]|int[]|int|string $number Range of numbers or a single number.
	 * @param string|int                $weight Weight of the number.
	 *
	 * @return string|int|null
	 */
	public function meta_type_numbers( $number = [ 0, 9 ], $weight = 50 ) {
		$number = array_values( array_map( 'absint', (array) $number ) );

		// If the number is an array, then we assume it's a range.
		if ( count( $number ) > 1 ) {
			$number = $this->generator->numberBetween( ...$number );
		}

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) $number );
	}

	/**
	 * Picks random elements from a comma-separated list and joins them.
	 *
	 * @param string    $elements  Comma-separated elements to pick from.
	 * @param int[]|int $qty       How many elements to pick, or a [ min, max ] range.
	 * @param string    $separator Separator used to join the picked elements.
	 * @param int       $weight    Percentage of calls that return a value.
	 *
	 * @return string|null The joined elements, or null.
	 */
	public function meta_type_elements( $elements = '', $qty = 1, $separator = ',', $weight = 50 ) {
		$separator = $this->meta_parse_separator( $separator );

		$elements = explode( ',', $elements );
		$qty      = $this->meta_parse_qty( $qty, $elements );

		$value = $this->generator->optional( $weight / 100, null )->randomElements( (array) $elements, $qty );
		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	/**
	 * Generates a random lowercase letter.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The letter, or null.
	 */
	public function meta_type_letter( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->randomLetter();
	}

	/**
	 * Generates a few random words: a sentence without its final full stop.
	 *
	 * @param int[]|int $qty    Approximate number of words, or a [ min, max ] range.
	 * @param int       $weight Percentage of calls that return a value.
	 *
	 * @return string The words, or an empty string.
	 */
	public function meta_type_words( $qty = 8, $weight = 50 ) {
		$qty      = $this->meta_parse_qty( $qty );
		$sentence = $this->generator->optional( $weight / 100, '' )->sentence( $qty );

		return make( Utils::class )->remove_sentence_period( $sentence );
	}

	/**
	 * Generates sentences or paragraphs and joins them.
	 *
	 * @param string    $type      `sentences`, or anything else for paragraphs.
	 * @param int[]|int $qty       How many sentences or paragraphs, or a [ min, max ] range.
	 * @param string    $separator Separator used to join them.
	 * @param int       $weight    Percentage of calls that return a value.
	 *
	 * @return string|null The text, or null.
	 */
	public function meta_type_text( $type = 'sentences', $qty = 3, $separator = "\r\n\r\n", $weight = 50 ) {
		$separator = $this->meta_parse_separator( $separator );
		$qty       = $this->meta_parse_qty( $qty );

		if ( 'sentences' === $type ) {
			$value = $this->generator->optional( $weight / 100, null )->sentences( $qty );
		} else {
			$value = $this->generator->optional( $weight / 100, null )->paragraphs( $qty );
		}

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( $separator, $value );
	}

	/**
	 * Generates random HTML built from the given tags.
	 *
	 * @param string    $elements Comma-separated HTML tag names to use.
	 * @param int[]|int $qty      How many elements, or a [ min, max ] range.
	 * @param int       $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The HTML, or null.
	 */
	public function meta_type_html( $elements, $qty = 6, $weight = 50 ) {
		$qty      = $this->meta_parse_qty( $qty );
		$elements = explode( ',', $elements );

		$value = $this->generator->optional( $weight / 100, null )->html_elements(
			[
				'elements' => $elements,
				'qty'      => $qty,
			] 
		);

		if ( is_null( $value ) ) {
			return $value;
		}

		return implode( "\n", $value );
	}

	/**
	 * Picks a random post ID from a WP_Query.
	 *
	 * Attachment queries without a `post_status` look at every status, since attachments are `inherit`.
	 *
	 * @param array|string $query  WP_Query arguments, as an array or a query string.
	 * @param int          $weight Percentage of calls that return a value.
	 *
	 * @return int|null The post ID, or null when nothing matched.
	 */
	public function meta_type_wp_query( $query, $weight = 50 ) {
		$args           = wp_parse_args( $query, [] );
		$args['fields'] = 'ids';

		// Make easier for Attachment Queries.
		if ( isset( $args['post_type'] ) && ! isset( $args['post_status'] ) && in_array( 'attachment', (array) $args['post_type'] ) ) {
			$args['post_status'] = 'any';
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return null;
		}

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) $query->posts );
	}

	/**
	 * Creates an attachment from a random image provider and returns its ID or URL.
	 *
	 * For post meta the attachment is attached to that post. The attachment is always created,
	 * even when the weight then leaves the meta value out.
	 *
	 * @param string    $type      `url` for the attachment URL, anything else for its ID.
	 * @param string    $providers Comma-separated image provider IDs to pick from.
	 * @param int       $weight    Percentage of calls that return a value.
	 * @param int[]|int $width     Image width, or a [ min, max ] range; empty uses the provider default.
	 * @param int[]|int $height    Image height, or a [ min, max ] range; empty uses the provider default.
	 *
	 * @return int|string|false|null The attachment ID or URL, or null when creating it failed or the weight left it out.
	 */
	public function meta_type_attachment( $type, $providers, $weight = 50, $width = [], $height = [] ) {
		$providers = array_map( 'esc_attr', array_map( 'trim', explode( ',', $providers ) ) );

		$provider = $this->generator->randomElement( $providers );

		$attachment = make( FakerPress\Module\Attachment::class );

		$arguments = [];

		// Specially for Meta we do the Randomization here.
		if ( ! empty( $width ) && $this->meta_parse_qty( $width ) ) {
			$arguments['width'] = $this->meta_parse_qty( $width );
		}

		// Specially for Meta we do the Randomization here.
		if ( ! empty( $height ) && $this->meta_parse_qty( $height ) ) {
			$arguments['height'] = $this->meta_parse_qty( $height );
		}

		// Generate the Attachment.
		$attachment->set( 'attachment_url', $provider, $arguments );

		// If it's meta for a post we need to mark the attachment as child of that post.
		if ( 'post' === $this->meta_object->name ) {
			$attachment->set( 'post_parent', $this->meta_object->id, 1 );
		}

		// Actually save the Attachment and get it's ID.
		$value = $attachment->generate()->save();

		// If there was an error, just bail now.
		if ( ! $value || is_wp_error( $value ) ) {
			return null;
		}

		// If asked URL, change to URL.
		if ( 'url' === $type ) {
			$value = wp_get_attachment_url( $value );
		}

		// Apply Weight.
		$value = $this->generator->optional( $weight / 100, null )->randomElement( (array) $value );

		return $value;
	}

	/**
	 * Fills a template, replacing each `#` with a digit and each `?` with a letter (Faker's bothify()).
	 *
	 * @param string $template Template, e.g. `John ##??`.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The filled template, or null.
	 */
	public function meta_type_lexify( $template, $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->bothify( (string) $template );
	}

	/**
	 * Fills a template, replacing each `*` with a random ASCII character.
	 *
	 * @param string $template Template, e.g. `John ****`.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The filled template, or null.
	 */
	public function meta_type_asciify( $template, $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->asciify( (string) $template );
	}

	/**
	 * Generates a string that matches a regular expression.
	 *
	 * @param string $template Regular expression, e.g. `[A-Z0-9._%+-]+@[A-Z0-9.-]`.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The generated string, or null.
	 */
	public function meta_type_regexify( $template, $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->regexify( (string) $template );
	}

	/**
	 * Generates a random timezone identifier, e.g. `Europe/Lisbon`.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The timezone, or null.
	 */
	public function meta_type_timezone( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->timezone();
	}

	/**
	 * Fills a `|`-separated company template.
	 *
	 * Each part is either a `{% tag %}` (suffix, company, bs, catch_phrase) replaced with generated
	 * text, or literal text kept as is (an empty part becomes a space).
	 *
	 * @param string $template Template, e.g. `{% company %}|&nbsp;|{% suffix %}`.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The filled template, or null.
	 */
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
				[ $element, $term ] = $_parsed;
				switch ( $term ) {
					case 'suffix':
						$text[] = $this->generator->companySuffix();
						break;
					case 'company':
						$text[] = $this->generator->company();
						break;
					case 'bs':
						$text[] = $this->generator->bs();
						break;
					case 'catch_phrase':
						$text[] = $this->generator->catchPhrase();
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) implode( '', $text ) );
	}

	/**
	 * Fills a `|`-separated person name template.
	 *
	 * Each part is either a `{% tag %}` (title, first_name, last_name, suffix) replaced with generated
	 * text, or literal text kept as is (an empty part becomes a space).
	 *
	 * @param string $template Template, e.g. `{% title %}|{% first_name %}|{% last_name %}`.
	 * @param string $gender   `male` or `female`, used for the title and first name.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The filled template, or null.
	 */
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
				[ $element, $term ] = $_parsed;
				switch ( $term ) {
					case 'title':
						$text[] = $this->generator->title( $gender );
						break;
					case 'first_name':
						$text[] = $this->generator->firstName( $gender );
						break;
					case 'last_name':
						$text[] = $this->generator->lastName();
						break;
					case 'suffix':
						$text[] = $this->generator->suffix();
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) implode( '', $text ) );
	}

	/**
	 * Fills a `|`-separated address template.
	 *
	 * Each part is either a `{% tag %}` (country, country_code, country_abbr, city, state, address,
	 * postalcode, latitude, longitude, ...) replaced with generated text, or literal text kept as is
	 * (an empty part becomes a space).
	 *
	 * @param string $template Template, e.g. `{% latitude %}|,|{% longitude %}`.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The filled template, or null.
	 */
	public function meta_type_geo( $template, $weight = 50 ) {
		$template = explode( '|', $template );
		$tags     = [
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
				[ $element, $term ] = $_parsed;
				switch ( $term ) {
					case 'country':
						$text[] = $this->generator->country();
						break;
					case 'country_code':
						$text[] = Utils::get_country_alpha_code( $this->generator->country(), 2 );
						break;
					case 'country_abbr':
						$text[] = Utils::get_country_alpha_code( $this->generator->country(), 3 );
						break;
					case 'city_prefix':
						$text[] = $this->generator->cityPrefix();
						break;
					case 'city_suffix':
						$text[] = $this->generator->citySuffix();
						break;
					case 'city':
						$text[] = $this->generator->city();
						break;
					case 'state':
						$text[] = $this->generator->state();
						break;
					case 'state_abbr':
						$text[] = $this->generator->stateAbbr();
						break;
					case 'address':
						$text[] = $this->generator->address();
						break;
					case 'secondary_address':
						$text[] = $this->generator->secondaryAddress();
						break;
					case 'building_number':
						$text[] = $this->generator->buildingNumber();
						break;
					case 'street_name':
						$text[] = $this->generator->streetName();
						break;
					case 'street_address':
						$text[] = $this->generator->streetAddress();
						break;
					case 'postalcode':
						$text[] = $this->generator->postcode();
						break;
					case 'latitude':
						$text[] = $this->generator->latitude();
						break;
					case 'longitude':
						$text[] = $this->generator->longitude();
						break;
				}
			} else {
				$text[] = ( empty( $tag ) ? ' ' : $tag );
			}
		}

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) implode( '', $text ) );
	}

	/**
	 * Generates a random date within an interval.
	 *
	 * An invalid start falls back to the start of today, an invalid end to now, and an end
	 * without a time is pushed to the end of that day.
	 *
	 * @param array  $interval Date strings under `min` and `max`.
	 * @param string $format   PHP date format for the result.
	 * @param int    $weight   Percentage of calls that return a value.
	 *
	 * @return string|null The formatted date, or null.
	 */
	public function meta_type_date( $interval, $format = 'Y-m-d H:i:s', $weight = 50 ) {
		$interval = (array) $interval;

		// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
		try {
			$min = new Chronos( $interval['min'] );
		} catch ( \Exception $e ) {
			$min = new Chronos( 'today' );
			$min = $min->startOfDay();
		}

		if ( ! empty( $interval ) ) {
			// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
			try {
				$max = new Chronos( $interval['max'] );
			} catch ( \Exception $e ) {
				// Invalid end date, fall back to "now" below.
				$max = null;
			}
		}

		if ( ! isset( $max ) ) {
			$max = new Chronos( 'now' );
		}

		// If max has no Time set it to the end of the day.
		$max_has_time = array_filter( [ $max->hour, $max->minute, $max->second ] );
		$max_has_time = ! empty( $max_has_time );
		if ( ! $max_has_time ) {
			$max = $max->endOfDay();
		}

		$selected = $this->generator->dateTimeBetween( (string) $min, (string) $max )->format( $format );

		return $this->generator->optional( $weight / 100, null )->randomElement( (array) $selected );
	}

	/**
	 * Generates a random IPv4 address.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The IP address, or null.
	 */
	public function meta_type_ip( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->ipv4();
	}

	/**
	 * Generates a random domain name.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The domain name, or null.
	 */
	public function meta_type_domain( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->domainName();
	}

	/**
	 * Generates a random email address.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The email address, or null.
	 */
	public function meta_type_email( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->email();
	}

	/**
	 * Generates a random browser user agent.
	 *
	 * @param int $weight Percentage of calls that return a value.
	 *
	 * @return string|null The user agent, or null.
	 */
	public function meta_type_user_agent( $weight = 50 ) {
		return $this->generator->optional( $weight / 100, null )->userAgent();
	}

	/**
	 * Returns a fixed value, or the default for the calls the weight leaves out.
	 *
	 * @param int   $weight  Percentage of calls that return `$value`.
	 * @param mixed $value   Value to return.
	 * @param mixed $default Value returned otherwise.
	 *
	 * @return mixed `$value` or `$default`.
	 */
	public function meta_type_raw( $weight = 100, $value = null, $default = null ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Public API, renaming the parameter breaks callers that pass it as a named argument.
		if ( (int) $weight >= $this->generator->numberBetween( 0, 100 ) ) {
			return $value;
		} else {
			return $default;
		}
	}
}
