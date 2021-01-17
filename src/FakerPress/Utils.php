<?php
namespace FakerPress;
use Faker;

class Utils {
	/**
	 * Static Singleton Holder
	 * @var self|null
	 */
	protected static $instance;

	/**
	 * Static Singleton Factory Method
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Formats an array of HTML attributes into a string.
	 *
	 * @since  0.5.1
	 *
	 * @param  array  $attributes Attributes used to build the string.
	 *
	 * @return string             Formatted attributes.
	 */
	public static function attr( $attributes = []  ) {
		if ( is_scalar( $attributes ) ) {
			return '';
		}

		$html       = [];
		$attributes = (array) $attributes;

		foreach ( $attributes as $key => $value ) {
			if ( is_null( $value ) || false === $value ) {
				continue;
			}

			if ( 'label' === $key ) {
				continue;
			}

			if ( '_' === substr( $key, 0, 1 ) ) {
				$key = substr_replace( $key, 'data-', 0, 1 );
			}

			if ( 'class' === $key && ! is_array( $value ) ) {
				$value = (array) $value;
			}

			$attr = $key;

			if ( ! is_scalar( $value ) ) {
				if ( 'class' === $key ) {
					$value = array_map( [ static::class, 'abbr' ], (array) $value );

					// Make sure buttons also get the `button` class
					if ( in_array( 'fp-type-button', $value ) ) {
						$value[] = 'button';
					}

					$value = array_map( 'sanitize_html_class', $value );
					$value = implode( ' ', $value );
				} else {
					$value = htmlspecialchars( json_encode( $value ), ENT_QUOTES, 'UTF-8' );
				}
			}
			if ( ! is_bool( $value ) || true !== $value ) {
				$attr .= '="' . $value . '"';
			}

			$html[ $key ] = $attr;
		}

		return ' ' . implode( ' ', $html ) . ' ';
	}

	/**
	 * Adds a abbreviation for the plugin to a string.
	 * Used for prepending HTML classes.
	 *
	 * @since  0.5.1
	 *
	 * @param  string $str String to which we are adding the abbr.
	 *
	 * @return string      String with the abbr prepended to.
	 */
	public static function abbr( $str = '' ) {
		return 'fp-' . $str;
	}

	/**
	 * Remove the Period on the end of the sentence from Faker.
	 *
	 * @param  string  $sentence  Which sentence we should remove the period from.
	 *
	 * @return string
	 */
	public function remove_sentence_period( $sentence ) {
		return rtrim( $sentence, '.' );
	}

	/**
	 * Determines if the provided value should be regarded as 'true'.
	 *
	 * @since  0.4.10
	 *
	 * @param  mixed $var
	 *
	 * @return bool
	 */
	public function is_truthy( $var ) {
		if ( is_bool( $var ) ) {
			return $var;
		}

		/**
		 * Provides an opportunity to modify strings that will be
		 * deemed to evaluate to true.
		 *
		 * @since  0.4.10
		 *
		 * @param  array $truthy_strings
		 */
		$truthy_strings = (array) apply_filters( 'fakerpress.is_truthy_strings', [
			'1',
			'enable',
			'enabled',
			'on',
			'y',
			'yes',
			'true',
		] );
		// Makes sure we are dealing with lowercase for testing
		if ( is_string( $var ) ) {
			$var = strtolower( $var );
		}

		// If $var is a string, it is only true if it is contained in the above array
		if ( in_array( $var, $truthy_strings, true ) ) {
			return true;
		}

		// All other strings will be treated as false
		if ( is_string( $var ) ) {
			return false;
		}

		// For other types (ints, floats etc) cast to bool
		return (bool) $var;
	}

	/**
	 * From range return a random Integer
	 * Providing the $total param will limit the returning integer to the total number of elements
	 *
	 * @param  array|int       $qty   The range or integer
	 * @param  null|int|array  $total {
	 *      @example null  Will not limit the Range to a maximum int
	 *      @example int   Limits the range to this maximum
	 *      @example array Counts the elements in array and limit to that
	 * }
	 * @return int
	 */
	public function get_qty_from_range( $range, $total = null ) {
		if ( is_array( $range ) ) {
			// Remove non-wanted items
			$range = array_filter( $range );

			// Grabs Min
			$min = reset( $range );

			// Grabs Max if range has 2 elements
			if ( count( $range ) > 1 ) {
				$max = end( $range );
			} else {
				// Otherwise just set qty to the Min
				$qty = $min;
			}
		} elseif ( is_numeric( $range ) ) {
			// If we have a numeric we just set it
			$qty = $range;
		} else {
			// All the other cases are a qty 0
			return 0;
		}

		// Now we treat the Range and select a random number
		if ( ! isset( $qty ) ) {
			$qty = Faker\Provider\Base::numberBetween( $min, $max );
		}

		// If an array for the total was provided, turn it to a integer
		if ( is_array( $total ) ){
			$total = count( $total );
		}

		// If we have a numeric total, make sure we don't go over that
		if ( is_numeric( $total ) ){
			$qty = min( $qty, $total );
		}

		// We just make sure we are dealing with a absolute number
		return absint( $qty );
	}

	/**
	 * Based on the English version of a country gets it's 2 or 3 alpha code
	 * @param  string|null  $country If you want to get all the codes, pass null
	 * @param  integer $type         How many characters is the code, 2 or 3
	 * @return string                It will be Uppercase
	 */
	public function get_country_alpha_code( $country_name = null, $type = 2 ) {
		$countries = [
			[
				'name' => esc_attr__( 'Afghanistan', 'fakerpress' ),
				'alpha2code' => 'AF',
				'alpha3code' => 'AFG',
			],
			[
				'name' => esc_attr__( 'Åland Islands', 'fakerpress' ),
				'alpha2code' => 'AX',
				'alpha3code' => 'ALA',
			],
			[
				'name' => esc_attr__( 'Albania', 'fakerpress' ),
				'alpha2code' => 'AL',
				'alpha3code' => 'ALB',
			],
			[
				'name' => esc_attr__( 'Algeria', 'fakerpress' ),
				'alpha2code' => 'DZ',
				'alpha3code' => 'DZA',
			],
			[
				'name' => esc_attr__( 'American Samoa', 'fakerpress' ),
				'alpha2code' => 'AS',
				'alpha3code' => 'ASM',
			],
			[
				'name' => esc_attr__( 'Andorra', 'fakerpress' ),
				'alpha2code' => 'AD',
				'alpha3code' => 'AND',
			],
			[
				'name' => esc_attr__( 'Angola', 'fakerpress' ),
				'alpha2code' => 'AO',
				'alpha3code' => 'AGO',
			],
			[
				'name' => esc_attr__( 'Anguilla', 'fakerpress' ),
				'alpha2code' => 'AI',
				'alpha3code' => 'AIA',
			],
			[
				'name' => esc_attr__( 'Antigua and Barbuda', 'fakerpress' ),
				'alpha2code' => 'AG',
				'alpha3code' => 'ATG',
			],
			[
				'name' => esc_attr__( 'Argentina', 'fakerpress' ),
				'alpha2code' => 'AR',
				'alpha3code' => 'ARG',
			],
			[
				'name' => esc_attr__( 'Armenia', 'fakerpress' ),
				'alpha2code' => 'AM',
				'alpha3code' => 'ARM',
			],
			[
				'name' => esc_attr__( 'Aruba', 'fakerpress' ),
				'alpha2code' => 'AW',
				'alpha3code' => 'ABW',
			],
			[
				'name' => esc_attr__( 'Australia', 'fakerpress' ),
				'alpha2code' => 'AU',
				'alpha3code' => 'AUS',
			],
			[
				'name' => esc_attr__( 'Austria', 'fakerpress' ),
				'alpha2code' => 'AT',
				'alpha3code' => 'AUT',
			],
			[
				'name' => esc_attr__( 'Azerbaijan', 'fakerpress' ),
				'alpha2code' => 'AZ',
				'alpha3code' => 'AZE',
			],
			[
				'name' => esc_attr__( 'The Bahamas', 'fakerpress' ),
				'alpha2code' => 'BS',
				'alpha3code' => 'BHS',
			],
			[
				'name' => esc_attr__( 'Bahrain', 'fakerpress' ),
				'alpha2code' => 'BH',
				'alpha3code' => 'BHR',
			],
			[
				'name' => esc_attr__( 'Bangladesh', 'fakerpress' ),
				'alpha2code' => 'BD',
				'alpha3code' => 'BGD',
			],
			[
				'name' => esc_attr__( 'Barbados', 'fakerpress' ),
				'alpha2code' => 'BB',
				'alpha3code' => 'BRB',
			],
			[
				'name' => esc_attr__( 'Belarus', 'fakerpress' ),
				'alpha2code' => 'BY',
				'alpha3code' => 'BLR',
			],
			[
				'name' => esc_attr__( 'Belgium', 'fakerpress' ),
				'alpha2code' => 'BE',
				'alpha3code' => 'BEL',
			],
			[
				'name' => esc_attr__( 'Belize', 'fakerpress' ),
				'alpha2code' => 'BZ',
				'alpha3code' => 'BLZ',
			],
			[
				'name' => esc_attr__( 'Benin', 'fakerpress' ),
				'alpha2code' => 'BJ',
				'alpha3code' => 'BEN',
			],
			[
				'name' => esc_attr__( 'Bermuda', 'fakerpress' ),
				'alpha2code' => 'BM',
				'alpha3code' => 'BMU',
			],
			[
				'name' => esc_attr__( 'Bhutan', 'fakerpress' ),
				'alpha2code' => 'BT',
				'alpha3code' => 'BTN',
			],
			[
				'name' => esc_attr__( 'Bolivia', 'fakerpress' ),
				'alpha2code' => 'BO',
				'alpha3code' => 'BOL',
			],
			[
				'name' => esc_attr__( 'Bonaire', 'fakerpress' ),
				'alpha2code' => 'BQ',
				'alpha3code' => 'BES',
			],
			[
				'name' => esc_attr__( 'Bosnia and Herzegovina', 'fakerpress' ),
				'alpha2code' => 'BA',
				'alpha3code' => 'BIH',
			],
			[
				'name' => esc_attr__( 'Botswana', 'fakerpress' ),
				'alpha2code' => 'BW',
				'alpha3code' => 'BWA',
			],
			[
				'name' => esc_attr__( 'Bouvet Island', 'fakerpress' ),
				'alpha2code' => 'BV',
				'alpha3code' => 'BVT',
			],
			[
				'name' => esc_attr__( 'Brazil', 'fakerpress' ),
				'alpha2code' => 'BR',
				'alpha3code' => 'BRA',
			],
			[
				'name' => esc_attr__( 'British Indian Ocean Territory', 'fakerpress' ),
				'alpha2code' => 'IO',
				'alpha3code' => 'IOT',
			],
			[
				'name' => esc_attr__( 'United States Minor Outlying Islands', 'fakerpress' ),
				'alpha2code' => 'UM',
				'alpha3code' => 'UMI',
			],
			[
				'name' => esc_attr__( 'British Virgin Islands', 'fakerpress' ),
				'alpha2code' => 'VG',
				'alpha3code' => 'VGB',
			],
			[
				'name' => esc_attr__( 'Brunei', 'fakerpress' ),
				'alpha2code' => 'BN',
				'alpha3code' => 'BRN',
			],
			[
				'name' => esc_attr__( 'Bulgaria', 'fakerpress' ),
				'alpha2code' => 'BG',
				'alpha3code' => 'BGR',
			],
			[
				'name' => esc_attr__( 'Burkina Faso', 'fakerpress' ),
				'alpha2code' => 'BF',
				'alpha3code' => 'BFA',
			],
			[
				'name' => esc_attr__( 'Burundi', 'fakerpress' ),
				'alpha2code' => 'BI',
				'alpha3code' => 'BDI',
			],
			[
				'name' => esc_attr__( 'Cambodia', 'fakerpress' ),
				'alpha2code' => 'KH',
				'alpha3code' => 'KHM',
			],
			[
				'name' => esc_attr__( 'Cameroon', 'fakerpress' ),
				'alpha2code' => 'CM',
				'alpha3code' => 'CMR',
			],
			[
				'name' => esc_attr__( 'Canada', 'fakerpress' ),
				'alpha2code' => 'CA',
				'alpha3code' => 'CAN',
			],
			[
				'name' => esc_attr__( 'Cape Verde', 'fakerpress' ),
				'alpha2code' => 'CV',
				'alpha3code' => 'CPV',
			],
			[
				'name' => esc_attr__( 'Cayman Islands', 'fakerpress' ),
				'alpha2code' => 'KY',
				'alpha3code' => 'CYM',
			],
			[
				'name' => esc_attr__( 'Central African Republic', 'fakerpress' ),
				'alpha2code' => 'CF',
				'alpha3code' => 'CAF',
			],
			[
				'name' => esc_attr__( 'Chad', 'fakerpress' ),
				'alpha2code' => 'TD',
				'alpha3code' => 'TCD',
			],
			[
				'name' => esc_attr__( 'Chile', 'fakerpress' ),
				'alpha2code' => 'CL',
				'alpha3code' => 'CHL',
			],
			[
				'name' => esc_attr__( 'China', 'fakerpress' ),
				'alpha2code' => 'CN',
				'alpha3code' => 'CHN',
			],
			[
				'name' => esc_attr__( 'Christmas Island', 'fakerpress' ),
				'alpha2code' => 'CX',
				'alpha3code' => 'CXR',
			],
			[
				'name' => esc_attr__( 'Cocos (Keeling) Islands', 'fakerpress' ),
				'alpha2code' => 'CC',
				'alpha3code' => 'CCK',
			],
			[
				'name' => esc_attr__( 'Colombia', 'fakerpress' ),
				'alpha2code' => 'CO',
				'alpha3code' => 'COL',
			],
			[
				'name' => esc_attr__( 'Comoros', 'fakerpress' ),
				'alpha2code' => 'KM',
				'alpha3code' => 'COM',
			],
			[
				'name' => esc_attr__( 'Republic of the Congo', 'fakerpress' ),
				'alpha2code' => 'CG',
				'alpha3code' => 'COG',
			],
			[
				'name' => esc_attr__( 'Democratic Republic of the Congo', 'fakerpress' ),
				'alpha2code' => 'CD',
				'alpha3code' => 'COD',
			],
			[
				'name' => esc_attr__( 'Cook Islands', 'fakerpress' ),
				'alpha2code' => 'CK',
				'alpha3code' => 'COK',
			],
			[
				'name' => esc_attr__( 'Costa Rica', 'fakerpress' ),
				'alpha2code' => 'CR',
				'alpha3code' => 'CRI',
			],
			[
				'name' => esc_attr__( 'Croatia', 'fakerpress' ),
				'alpha2code' => 'HR',
				'alpha3code' => 'HRV',
			],
			[
				'name' => esc_attr__( 'Cuba', 'fakerpress' ),
				'alpha2code' => 'CU',
				'alpha3code' => 'CUB',
			],
			[
				'name' => esc_attr__( 'Curaçao', 'fakerpress' ),
				'alpha2code' => 'CW',
				'alpha3code' => 'CUW',
			],
			[
				'name' => esc_attr__( 'Cyprus', 'fakerpress' ),
				'alpha2code' => 'CY',
				'alpha3code' => 'CYP',
			],
			[
				'name' => esc_attr__( 'Czech Republic', 'fakerpress' ),
				'alpha2code' => 'CZ',
				'alpha3code' => 'CZE',
			],
			[
				'name' => esc_attr__( 'Denmark', 'fakerpress' ),
				'alpha2code' => 'DK',
				'alpha3code' => 'DNK',
			],
			[
				'name' => esc_attr__( 'Djibouti', 'fakerpress' ),
				'alpha2code' => 'DJ',
				'alpha3code' => 'DJI',
			],
			[
				'name' => esc_attr__( 'Dominica', 'fakerpress' ),
				'alpha2code' => 'DM',
				'alpha3code' => 'DMA',
			],
			[
				'name' => esc_attr__( 'Dominican Republic', 'fakerpress' ),
				'alpha2code' => 'DO',
				'alpha3code' => 'DOM',
			],
			[
				'name' => esc_attr__( 'Ecuador', 'fakerpress' ),
				'alpha2code' => 'EC',
				'alpha3code' => 'ECU',
			],
			[
				'name' => esc_attr__( 'Egypt', 'fakerpress' ),
				'alpha2code' => 'EG',
				'alpha3code' => 'EGY',
			],
			[
				'name' => esc_attr__( 'El Salvador', 'fakerpress' ),
				'alpha2code' => 'SV',
				'alpha3code' => 'SLV',
			],
			[
				'name' => esc_attr__( 'Equatorial Guinea', 'fakerpress' ),
				'alpha2code' => 'GQ',
				'alpha3code' => 'GNQ',
			],
			[
				'name' => esc_attr__( 'Eritrea', 'fakerpress' ),
				'alpha2code' => 'ER',
				'alpha3code' => 'ERI',
			],
			[
				'name' => esc_attr__( 'Estonia', 'fakerpress' ),
				'alpha2code' => 'EE',
				'alpha3code' => 'EST',
			],
			[
				'name' => esc_attr__( 'Ethiopia', 'fakerpress' ),
				'alpha2code' => 'ET',
				'alpha3code' => 'ETH',
			],
			[
				'name' => esc_attr__( 'Falkland Islands', 'fakerpress' ),
				'alpha2code' => 'FK',
				'alpha3code' => 'FLK',
			],
			[
				'name' => esc_attr__( 'Faroe Islands', 'fakerpress' ),
				'alpha2code' => 'FO',
				'alpha3code' => 'FRO',
			],
			[
				'name' => esc_attr__( 'Fiji', 'fakerpress' ),
				'alpha2code' => 'FJ',
				'alpha3code' => 'FJI',
			],
			[
				'name' => esc_attr__( 'Finland', 'fakerpress' ),
				'alpha2code' => 'FI',
				'alpha3code' => 'FIN',
			],
			[
				'name' => esc_attr__( 'France', 'fakerpress' ),
				'alpha2code' => 'FR',
				'alpha3code' => 'FRA',
			],
			[
				'name' => esc_attr__( 'French Guiana', 'fakerpress' ),
				'alpha2code' => 'GF',
				'alpha3code' => 'GUF',
			],
			[
				'name' => esc_attr__( 'French Polynesia', 'fakerpress' ),
				'alpha2code' => 'PF',
				'alpha3code' => 'PYF',
			],
			[
				'name' => esc_attr__( 'French Southern and Antarctic Lands', 'fakerpress' ),
				'alpha2code' => 'TF',
				'alpha3code' => 'ATF',
			],
			[
				'name' => esc_attr__( 'Gabon', 'fakerpress' ),
				'alpha2code' => 'GA',
				'alpha3code' => 'GAB',
			],
			[
				'name' => esc_attr__( 'The Gambia', 'fakerpress' ),
				'alpha2code' => 'GM',
				'alpha3code' => 'GMB',
			],
			[
				'name' => esc_attr__( 'Georgia', 'fakerpress' ),
				'alpha2code' => 'GE',
				'alpha3code' => 'GEO',
			],
			[
				'name' => esc_attr__( 'Germany', 'fakerpress' ),
				'alpha2code' => 'DE',
				'alpha3code' => 'DEU',
			],
			[
				'name' => esc_attr__( 'Ghana', 'fakerpress' ),
				'alpha2code' => 'GH',
				'alpha3code' => 'GHA',
			],
			[
				'name' => esc_attr__( 'Gibraltar', 'fakerpress' ),
				'alpha2code' => 'GI',
				'alpha3code' => 'GIB',
			],
			[
				'name' => esc_attr__( 'Greece', 'fakerpress' ),
				'alpha2code' => 'GR',
				'alpha3code' => 'GRC',
			],
			[
				'name' => esc_attr__( 'Greenland', 'fakerpress' ),
				'alpha2code' => 'GL',
				'alpha3code' => 'GRL',
			],
			[
				'name' => esc_attr__( 'Grenada', 'fakerpress' ),
				'alpha2code' => 'GD',
				'alpha3code' => 'GRD',
			],
			[
				'name' => esc_attr__( 'Guadeloupe', 'fakerpress' ),
				'alpha2code' => 'GP',
				'alpha3code' => 'GLP',
			],
			[
				'name' => esc_attr__( 'Guam', 'fakerpress' ),
				'alpha2code' => 'GU',
				'alpha3code' => 'GUM',
			],
			[
				'name' => esc_attr__( 'Guatemala', 'fakerpress' ),
				'alpha2code' => 'GT',
				'alpha3code' => 'GTM',
			],
			[
				'name' => esc_attr__( 'Guernsey', 'fakerpress' ),
				'alpha2code' => 'GG',
				'alpha3code' => 'GGY',
			],
			[
				'name' => esc_attr__( 'Guinea', 'fakerpress' ),
				'alpha2code' => 'GN',
				'alpha3code' => 'GIN',
			],
			[
				'name' => esc_attr__( 'Guinea-Bissau', 'fakerpress' ),
				'alpha2code' => 'GW',
				'alpha3code' => 'GNB',
			],
			[
				'name' => esc_attr__( 'Guyana', 'fakerpress' ),
				'alpha2code' => 'GY',
				'alpha3code' => 'GUY',
			],
			[
				'name' => esc_attr__( 'Haiti', 'fakerpress' ),
				'alpha2code' => 'HT',
				'alpha3code' => 'HTI',
			],
			[
				'name' => esc_attr__( 'Heard Island and McDonald Islands', 'fakerpress' ),
				'alpha2code' => 'HM',
				'alpha3code' => 'HMD',
			],
			[
				'name' => esc_attr__( 'Honduras', 'fakerpress' ),
				'alpha2code' => 'HN',
				'alpha3code' => 'HND',
			],
			[
				'name' => esc_attr__( 'Hong Kong', 'fakerpress' ),
				'alpha2code' => 'HK',
				'alpha3code' => 'HKG',
			],
			[
				'name' => esc_attr__( 'Hungary', 'fakerpress' ),
				'alpha2code' => 'HU',
				'alpha3code' => 'HUN',
			],
			[
				'name' => esc_attr__( 'Iceland', 'fakerpress' ),
				'alpha2code' => 'IS',
				'alpha3code' => 'ISL',
			],
			[
				'name' => esc_attr__( 'India', 'fakerpress' ),
				'alpha2code' => 'IN',
				'alpha3code' => 'IND',
			],
			[
				'name' => esc_attr__( 'Indonesia', 'fakerpress' ),
				'alpha2code' => 'ID',
				'alpha3code' => 'IDN',
			],
			[
				'name' => esc_attr__( 'Ivory Coast', 'fakerpress' ),
				'alpha2code' => 'CI',
				'alpha3code' => 'CIV',
			],
			[
				'name' => esc_attr__( 'Iran', 'fakerpress' ),
				'alpha2code' => 'IR',
				'alpha3code' => 'IRN',
			],
			[
				'name' => esc_attr__( 'Iraq', 'fakerpress' ),
				'alpha2code' => 'IQ',
				'alpha3code' => 'IRQ',
			],
			[
				'name' => esc_attr__( 'Republic of Ireland', 'fakerpress' ),
				'alpha2code' => 'IE',
				'alpha3code' => 'IRL',
			],
			[
				'name' => esc_attr__( 'Isle of Man', 'fakerpress' ),
				'alpha2code' => 'IM',
				'alpha3code' => 'IMN',
			],
			[
				'name' => esc_attr__( 'Israel', 'fakerpress' ),
				'alpha2code' => 'IL',
				'alpha3code' => 'ISR',
			],
			[
				'name' => esc_attr__( 'Italy', 'fakerpress' ),
				'alpha2code' => 'IT',
				'alpha3code' => 'ITA',
			],
			[
				'name' => esc_attr__( 'Jamaica', 'fakerpress' ),
				'alpha2code' => 'JM',
				'alpha3code' => 'JAM',
			],
			[
				'name' => esc_attr__( 'Japan', 'fakerpress' ),
				'alpha2code' => 'JP',
				'alpha3code' => 'JPN',
			],
			[
				'name' => esc_attr__( 'Jersey', 'fakerpress' ),
				'alpha2code' => 'JE',
				'alpha3code' => 'JEY',
			],
			[
				'name' => esc_attr__( 'Jordan', 'fakerpress' ),
				'alpha2code' => 'JO',
				'alpha3code' => 'JOR',
			],
			[
				'name' => esc_attr__( 'Kazakhstan', 'fakerpress' ),
				'alpha2code' => 'KZ',
				'alpha3code' => 'KAZ',
			],
			[
				'name' => esc_attr__( 'Kenya', 'fakerpress' ),
				'alpha2code' => 'KE',
				'alpha3code' => 'KEN',
			],
			[
				'name' => esc_attr__( 'Kiribati', 'fakerpress' ),
				'alpha2code' => 'KI',
				'alpha3code' => 'KIR',
			],
			[
				'name' => esc_attr__( 'Kuwait', 'fakerpress' ),
				'alpha2code' => 'KW',
				'alpha3code' => 'KWT',
			],
			[
				'name' => esc_attr__( 'Kyrgyzstan', 'fakerpress' ),
				'alpha2code' => 'KG',
				'alpha3code' => 'KGZ',
			],
			[
				'name' => esc_attr__( 'Laos', 'fakerpress' ),
				'alpha2code' => 'LA',
				'alpha3code' => 'LAO',
			],
			[
				'name' => esc_attr__( 'Latvia', 'fakerpress' ),
				'alpha2code' => 'LV',
				'alpha3code' => 'LVA',
			],
			[
				'name' => esc_attr__( 'Lebanon', 'fakerpress' ),
				'alpha2code' => 'LB',
				'alpha3code' => 'LBN',
			],
			[
				'name' => esc_attr__( 'Lesotho', 'fakerpress' ),
				'alpha2code' => 'LS',
				'alpha3code' => 'LSO',
			],
			[
				'name' => esc_attr__( 'Liberia', 'fakerpress' ),
				'alpha2code' => 'LR',
				'alpha3code' => 'LBR',
			],
			[
				'name' => esc_attr__( 'Libya', 'fakerpress' ),
				'alpha2code' => 'LY',
				'alpha3code' => 'LBY',
			],
			[
				'name' => esc_attr__( 'Liechtenstein', 'fakerpress' ),
				'alpha2code' => 'LI',
				'alpha3code' => 'LIE',
			],
			[
				'name' => esc_attr__( 'Lithuania', 'fakerpress' ),
				'alpha2code' => 'LT',
				'alpha3code' => 'LTU',
			],
			[
				'name' => esc_attr__( 'Luxembourg', 'fakerpress' ),
				'alpha2code' => 'LU',
				'alpha3code' => 'LUX',
			],
			[
				'name' => esc_attr__( 'Macau', 'fakerpress' ),
				'alpha2code' => 'MO',
				'alpha3code' => 'MAC',
			],
			[
				'name' => esc_attr__( 'Republic of Macedonia', 'fakerpress' ),
				'alpha2code' => 'MK',
				'alpha3code' => 'MKD',
			],
			[
				'name' => esc_attr__( 'Madagascar', 'fakerpress' ),
				'alpha2code' => 'MG',
				'alpha3code' => 'MDG',
			],
			[
				'name' => esc_attr__( 'Malawi', 'fakerpress' ),
				'alpha2code' => 'MW',
				'alpha3code' => 'MWI',
			],
			[
				'name' => esc_attr__( 'Malaysia', 'fakerpress' ),
				'alpha2code' => 'MY',
				'alpha3code' => 'MYS',
			],
			[
				'name' => esc_attr__( 'Maldives', 'fakerpress' ),
				'alpha2code' => 'MV',
				'alpha3code' => 'MDV',
			],
			[
				'name' => esc_attr__( 'Mali', 'fakerpress' ),
				'alpha2code' => 'ML',
				'alpha3code' => 'MLI',
			],
			[
				'name' => esc_attr__( 'Malta', 'fakerpress' ),
				'alpha2code' => 'MT',
				'alpha3code' => 'MLT',
			],
			[
				'name' => esc_attr__( 'Marshall Islands', 'fakerpress' ),
				'alpha2code' => 'MH',
				'alpha3code' => 'MHL',
			],
			[
				'name' => esc_attr__( 'Martinique', 'fakerpress' ),
				'alpha2code' => 'MQ',
				'alpha3code' => 'MTQ',
			],
			[
				'name' => esc_attr__( 'Mauritania', 'fakerpress' ),
				'alpha2code' => 'MR',
				'alpha3code' => 'MRT',
			],
			[
				'name' => esc_attr__( 'Mauritius', 'fakerpress' ),
				'alpha2code' => 'MU',
				'alpha3code' => 'MUS',
			],
			[
				'name' => esc_attr__( 'Mayotte', 'fakerpress' ),
				'alpha2code' => 'YT',
				'alpha3code' => 'MYT',
			],
			[
				'name' => esc_attr__( 'Mexico', 'fakerpress' ),
				'alpha2code' => 'MX',
				'alpha3code' => 'MEX',
			],
			[
				'name' => esc_attr__( 'Federated States of Micronesia', 'fakerpress' ),
				'alpha2code' => 'FM',
				'alpha3code' => 'FSM',
			],
			[
				'name' => esc_attr__( 'Moldova', 'fakerpress' ),
				'alpha2code' => 'MD',
				'alpha3code' => 'MDA',
			],
			[
				'name' => esc_attr__( 'Monaco', 'fakerpress' ),
				'alpha2code' => 'MC',
				'alpha3code' => 'MCO',
			],
			[
				'name' => esc_attr__( 'Mongolia', 'fakerpress' ),
				'alpha2code' => 'MN',
				'alpha3code' => 'MNG',
			],
			[
				'name' => esc_attr__( 'Montenegro', 'fakerpress' ),
				'alpha2code' => 'ME',
				'alpha3code' => 'MNE',
			],
			[
				'name' => esc_attr__( 'Montserrat', 'fakerpress' ),
				'alpha2code' => 'MS',
				'alpha3code' => 'MSR',
			],
			[
				'name' => esc_attr__( 'Morocco', 'fakerpress' ),
				'alpha2code' => 'MA',
				'alpha3code' => 'MAR',
			],
			[
				'name' => esc_attr__( 'Mozambique', 'fakerpress' ),
				'alpha2code' => 'MZ',
				'alpha3code' => 'MOZ',
			],
			[
				'name' => esc_attr__( 'Myanmar', 'fakerpress' ),
				'alpha2code' => 'MM',
				'alpha3code' => 'MMR',
			],
			[
				'name' => esc_attr__( 'Namibia', 'fakerpress' ),
				'alpha2code' => 'NA',
				'alpha3code' => 'NAM',
			],
			[
				'name' => esc_attr__( 'Nauru', 'fakerpress' ),
				'alpha2code' => 'NR',
				'alpha3code' => 'NRU',
			],
			[
				'name' => esc_attr__( 'Nepal', 'fakerpress' ),
				'alpha2code' => 'NP',
				'alpha3code' => 'NPL',
			],
			[
				'name' => esc_attr__( 'Netherlands', 'fakerpress' ),
				'alpha2code' => 'NL',
				'alpha3code' => 'NLD',
			],
			[
				'name' => esc_attr__( 'New Caledonia', 'fakerpress' ),
				'alpha2code' => 'NC',
				'alpha3code' => 'NCL',
			],
			[
				'name' => esc_attr__( 'New Zealand', 'fakerpress' ),
				'alpha2code' => 'NZ',
				'alpha3code' => 'NZL',
			],
			[
				'name' => esc_attr__( 'Nicaragua', 'fakerpress' ),
				'alpha2code' => 'NI',
				'alpha3code' => 'NIC',
			],
			[
				'name' => esc_attr__( 'Niger', 'fakerpress' ),
				'alpha2code' => 'NE',
				'alpha3code' => 'NER',
			],
			[
				'name' => esc_attr__( 'Nigeria', 'fakerpress' ),
				'alpha2code' => 'NG',
				'alpha3code' => 'NGA',
			],
			[
				'name' => esc_attr__( 'Niue', 'fakerpress' ),
				'alpha2code' => 'NU',
				'alpha3code' => 'NIU',
			],
			[
				'name' => esc_attr__( 'Norfolk Island', 'fakerpress' ),
				'alpha2code' => 'NF',
				'alpha3code' => 'NFK',
			],
			[
				'name' => esc_attr__( 'North Korea', 'fakerpress' ),
				'alpha2code' => 'KP',
				'alpha3code' => 'PRK',
			],
			[
				'name' => esc_attr__( 'Northern Mariana Islands', 'fakerpress' ),
				'alpha2code' => 'MP',
				'alpha3code' => 'MNP',
			],
			[
				'name' => esc_attr__( 'Norway', 'fakerpress' ),
				'alpha2code' => 'NO',
				'alpha3code' => 'NOR',
			],
			[
				'name' => esc_attr__( 'Oman', 'fakerpress' ),
				'alpha2code' => 'OM',
				'alpha3code' => 'OMN',
			],
			[
				'name' => esc_attr__( 'Pakistan', 'fakerpress' ),
				'alpha2code' => 'PK',
				'alpha3code' => 'PAK',
			],
			[
				'name' => esc_attr__( 'Palau', 'fakerpress' ),
				'alpha2code' => 'PW',
				'alpha3code' => 'PLW',
			],
			[
				'name' => esc_attr__( 'Palestine', 'fakerpress' ),
				'alpha2code' => 'PS',
				'alpha3code' => 'PSE',
			],
			[
				'name' => esc_attr__( 'Panama', 'fakerpress' ),
				'alpha2code' => 'PA',
				'alpha3code' => 'PAN',
			],
			[
				'name' => esc_attr__( 'Papua New Guinea', 'fakerpress' ),
				'alpha2code' => 'PG',
				'alpha3code' => 'PNG',
			],
			[
				'name' => esc_attr__( 'Paraguay', 'fakerpress' ),
				'alpha2code' => 'PY',
				'alpha3code' => 'PRY',
			],
			[
				'name' => esc_attr__( 'Peru', 'fakerpress' ),
				'alpha2code' => 'PE',
				'alpha3code' => 'PER',
			],
			[
				'name' => esc_attr__( 'Philippines', 'fakerpress' ),
				'alpha2code' => 'PH',
				'alpha3code' => 'PHL',
			],
			[
				'name' => esc_attr__( 'Pitcairn Islands', 'fakerpress' ),
				'alpha2code' => 'PN',
				'alpha3code' => 'PCN',
			],
			[
				'name' => esc_attr__( 'Poland', 'fakerpress' ),
				'alpha2code' => 'PL',
				'alpha3code' => 'POL',
			],
			[
				'name' => esc_attr__( 'Portugal', 'fakerpress' ),
				'alpha2code' => 'PT',
				'alpha3code' => 'PRT',
			],
			[
				'name' => esc_attr__( 'Puerto Rico', 'fakerpress' ),
				'alpha2code' => 'PR',
				'alpha3code' => 'PRI',
			],
			[
				'name' => esc_attr__( 'Qatar', 'fakerpress' ),
				'alpha2code' => 'QA',
				'alpha3code' => 'QAT',
			],
			[
				'name' => esc_attr__( 'Republic of Kosovo', 'fakerpress' ),
				'alpha2code' => 'XK',
				'alpha3code' => 'KOS',
			],
			[
				'name' => esc_attr__( 'Réunion', 'fakerpress' ),
				'alpha2code' => 'RE',
				'alpha3code' => 'REU',
			],
			[
				'name' => esc_attr__( 'Romania', 'fakerpress' ),
				'alpha2code' => 'RO',
				'alpha3code' => 'ROU',
			],
			[
				'name' => esc_attr__( 'Russia', 'fakerpress' ),
				'alpha2code' => 'RU',
				'alpha3code' => 'RUS',
			],
			[
				'name' => esc_attr__( 'Rwanda', 'fakerpress' ),
				'alpha2code' => 'RW',
				'alpha3code' => 'RWA',
			],
			[
				'name' => esc_attr__( 'Saint Barthélemy', 'fakerpress' ),
				'alpha2code' => 'BL',
				'alpha3code' => 'BLM',
			],
			[
				'name' => esc_attr__( 'Saint Helena', 'fakerpress' ),
				'alpha2code' => 'SH',
				'alpha3code' => 'SHN',
			],
			[
				'name' => esc_attr__( 'Saint Kitts and Nevis', 'fakerpress' ),
				'alpha2code' => 'KN',
				'alpha3code' => 'KNA',
			],
			[
				'name' => esc_attr__( 'Saint Lucia', 'fakerpress' ),
				'alpha2code' => 'LC',
				'alpha3code' => 'LCA',
			],
			[
				'name' => esc_attr__( 'Saint Martin', 'fakerpress' ),
				'alpha2code' => 'MF',
				'alpha3code' => 'MAF',
			],
			[
				'name' => esc_attr__( 'Saint Pierre and Miquelon', 'fakerpress' ),
				'alpha2code' => 'PM',
				'alpha3code' => 'SPM',
			],
			[
				'name' => esc_attr__( 'Saint Vincent and the Grenadines', 'fakerpress' ),
				'alpha2code' => 'VC',
				'alpha3code' => 'VCT',
			],
			[
				'name' => esc_attr__( 'Samoa', 'fakerpress' ),
				'alpha2code' => 'WS',
				'alpha3code' => 'WSM',
			],
			[
				'name' => esc_attr__( 'San Marino', 'fakerpress' ),
				'alpha2code' => 'SM',
				'alpha3code' => 'SMR',
			],
			[
				'name' => esc_attr__( 'São Tomé and Príncipe', 'fakerpress' ),
				'alpha2code' => 'ST',
				'alpha3code' => 'STP',
			],
			[
				'name' => esc_attr__( 'Saudi Arabia', 'fakerpress' ),
				'alpha2code' => 'SA',
				'alpha3code' => 'SAU',
			],
			[
				'name' => esc_attr__( 'Senegal', 'fakerpress' ),
				'alpha2code' => 'SN',
				'alpha3code' => 'SEN',
			],
			[
				'name' => esc_attr__( 'Serbia', 'fakerpress' ),
				'alpha2code' => 'RS',
				'alpha3code' => 'SRB',
			],
			[
				'name' => esc_attr__( 'Seychelles', 'fakerpress' ),
				'alpha2code' => 'SC',
				'alpha3code' => 'SYC',
			],
			[
				'name' => esc_attr__( 'Sierra Leone', 'fakerpress' ),
				'alpha2code' => 'SL',
				'alpha3code' => 'SLE',
			],
			[
				'name' => esc_attr__( 'Singapore', 'fakerpress' ),
				'alpha2code' => 'SG',
				'alpha3code' => 'SGP',
			],
			[
				'name' => esc_attr__( 'Sint Maarten', 'fakerpress' ),
				'alpha2code' => 'SX',
				'alpha3code' => 'SXM',
			],
			[
				'name' => esc_attr__( 'Slovakia', 'fakerpress' ),
				'alpha2code' => 'SK',
				'alpha3code' => 'SVK',
			],
			[
				'name' => esc_attr__( 'Slovenia', 'fakerpress' ),
				'alpha2code' => 'SI',
				'alpha3code' => 'SVN',
			],
			[
				'name' => esc_attr__( 'Solomon Islands', 'fakerpress' ),
				'alpha2code' => 'SB',
				'alpha3code' => 'SLB',
			],
			[
				'name' => esc_attr__( 'Somalia', 'fakerpress' ),
				'alpha2code' => 'SO',
				'alpha3code' => 'SOM',
			],
			[
				'name' => esc_attr__( 'South Africa', 'fakerpress' ),
				'alpha2code' => 'ZA',
				'alpha3code' => 'ZAF',
			],
			[
				'name' => esc_attr__( 'South Georgia', 'fakerpress' ),
				'alpha2code' => 'GS',
				'alpha3code' => 'SGS',
			],
			[
				'name' => esc_attr__( 'South Korea', 'fakerpress' ),
				'alpha2code' => 'KR',
				'alpha3code' => 'KOR',
			],
			[
				'name' => esc_attr__( 'South Sudan', 'fakerpress' ),
				'alpha2code' => 'SS',
				'alpha3code' => 'SSD',
			],
			[
				'name' => esc_attr__( 'Spain', 'fakerpress' ),
				'alpha2code' => 'ES',
				'alpha3code' => 'ESP',
			],
			[
				'name' => esc_attr__( 'Sri Lanka', 'fakerpress' ),
				'alpha2code' => 'LK',
				'alpha3code' => 'LKA',
			],
			[
				'name' => esc_attr__( 'Sudan', 'fakerpress' ),
				'alpha2code' => 'SD',
				'alpha3code' => 'SDN',
			],
			[
				'name' => esc_attr__( 'Suriname', 'fakerpress' ),
				'alpha2code' => 'SR',
				'alpha3code' => 'SUR',
			],
			[
				'name' => esc_attr__( 'Svalbard and Jan Mayen', 'fakerpress' ),
				'alpha2code' => 'SJ',
				'alpha3code' => 'SJM',
			],
			[
				'name' => esc_attr__( 'Swaziland', 'fakerpress' ),
				'alpha2code' => 'SZ',
				'alpha3code' => 'SWZ',
			],
			[
				'name' => esc_attr__( 'Sweden', 'fakerpress' ),
				'alpha2code' => 'SE',
				'alpha3code' => 'SWE',
			],
			[
				'name' => esc_attr__( 'Switzerland', 'fakerpress' ),
				'alpha2code' => 'CH',
				'alpha3code' => 'CHE',
			],
			[
				'name' => esc_attr__( 'Syria', 'fakerpress' ),
				'alpha2code' => 'SY',
				'alpha3code' => 'SYR',
			],
			[
				'name' => esc_attr__( 'Taiwan', 'fakerpress' ),
				'alpha2code' => 'TW',
				'alpha3code' => 'TWN',
			],
			[
				'name' => esc_attr__( 'Tajikistan', 'fakerpress' ),
				'alpha2code' => 'TJ',
				'alpha3code' => 'TJK',
			],
			[
				'name' => esc_attr__( 'Tanzania', 'fakerpress' ),
				'alpha2code' => 'TZ',
				'alpha3code' => 'TZA',
			],
			[
				'name' => esc_attr__( 'Thailand', 'fakerpress' ),
				'alpha2code' => 'TH',
				'alpha3code' => 'THA',
			],
			[
				'name' => esc_attr__( 'East Timor', 'fakerpress' ),
				'alpha2code' => 'TL',
				'alpha3code' => 'TLS',
			],
			[
				'name' => esc_attr__( 'Togo', 'fakerpress' ),
				'alpha2code' => 'TG',
				'alpha3code' => 'TGO',
			],
			[
				'name' => esc_attr__( 'Tokelau', 'fakerpress' ),
				'alpha2code' => 'TK',
				'alpha3code' => 'TKL',
			],
			[
				'name' => esc_attr__( 'Tonga', 'fakerpress' ),
				'alpha2code' => 'TO',
				'alpha3code' => 'TON',
			],
			[
				'name' => esc_attr__( 'Trinidad and Tobago', 'fakerpress' ),
				'alpha2code' => 'TT',
				'alpha3code' => 'TTO',
			],
			[
				'name' => esc_attr__( 'Tunisia', 'fakerpress' ),
				'alpha2code' => 'TN',
				'alpha3code' => 'TUN',
			],
			[
				'name' => esc_attr__( 'Turkey', 'fakerpress' ),
				'alpha2code' => 'TR',
				'alpha3code' => 'TUR',
			],
			[
				'name' => esc_attr__( 'Turkmenistan', 'fakerpress' ),
				'alpha2code' => 'TM',
				'alpha3code' => 'TKM',
			],
			[
				'name' => esc_attr__( 'Turks and Caicos Islands', 'fakerpress' ),
				'alpha2code' => 'TC',
				'alpha3code' => 'TCA',
			],
			[
				'name' => esc_attr__( 'Tuvalu', 'fakerpress' ),
				'alpha2code' => 'TV',
				'alpha3code' => 'TUV',
			],
			[
				'name' => esc_attr__( 'Uganda', 'fakerpress' ),
				'alpha2code' => 'UG',
				'alpha3code' => 'UGA',
			],
			[
				'name' => esc_attr__( 'Ukraine', 'fakerpress' ),
				'alpha2code' => 'UA',
				'alpha3code' => 'UKR',
			],
			[
				'name' => esc_attr__( 'United Arab Emirates', 'fakerpress' ),
				'alpha2code' => 'AE',
				'alpha3code' => 'ARE',
			],
			[
				'name' => esc_attr__( 'United Kingdom', 'fakerpress' ),
				'alpha2code' => 'GB',
				'alpha3code' => 'GBR',
			],
			[
				'name' => esc_attr__( 'United States', 'fakerpress' ),
				'alpha2code' => 'US',
				'alpha3code' => 'USA',
			],
			[
				'name' => esc_attr__( 'Uruguay', 'fakerpress' ),
				'alpha2code' => 'UY',
				'alpha3code' => 'URY',
			],
			[
				'name' => esc_attr__( 'Uzbekistan', 'fakerpress' ),
				'alpha2code' => 'UZ',
				'alpha3code' => 'UZB',
			],
			[
				'name' => esc_attr__( 'Vanuatu', 'fakerpress' ),
				'alpha2code' => 'VU',
				'alpha3code' => 'VUT',
			],
			[
				'name' => esc_attr__( 'Venezuela', 'fakerpress' ),
				'alpha2code' => 'VE',
				'alpha3code' => 'VEN',
			],
			[
				'name' => esc_attr__( 'Vietnam', 'fakerpress' ),
				'alpha2code' => 'VN',
				'alpha3code' => 'VNM',
			],
			[
				'name' => esc_attr__( 'Wallis and Futuna', 'fakerpress' ),
				'alpha2code' => 'WF',
				'alpha3code' => 'WLF',
			],
			[
				'name' => esc_attr__( 'Western Sahara', 'fakerpress' ),
				'alpha2code' => 'EH',
				'alpha3code' => 'ESH',
			],
			[
				'name' => esc_attr__( 'Yemen', 'fakerpress' ),
				'alpha2code' => 'YE',
				'alpha3code' => 'YEM',
			],
			[
				'name' => esc_attr__( 'Zambia', 'fakerpress' ),
				'alpha2code' => 'ZM',
				'alpha3code' => 'ZMB',
			],
			[
				'name' => esc_attr__( 'Zimbabwe', 'fakerpress' ),
				'alpha2code' => 'ZW',
				'alpha3code' => 'ZWE',
			],
		];

		foreach ( $countries as $index => $country ) {
			if ( $country['name'] === $country_name && ! empty( $country[ 'alpha' . $type . 'code' ] ) ) {
				$code = $country[ 'alpha' . $type . 'code' ];
				break;
			}
		}
		return $code;
	}
}