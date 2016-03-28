<?php
namespace FakerPress;
use Faker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

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
	 * Prevents the class to be called from "new" structure
	 *
	 * @return void
	 */
	private function __construct() {

	}


	/**
	 * From range return a random Integer
	 * Providing the $elements param will limit the returning integer to the total number of elements
	 *
	 * @param  array|int $qty   The range or integer
	 * @param  null|int|array $elements {
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
		$countries = array(
			array(
				'name' => esc_attr__( 'Afghanistan', 'fakerpress' ),
				'alpha2code' => 'AF',
				'alpha3code' => 'AFG',
			),
			array(
				'name' => esc_attr__( 'Åland Islands', 'fakerpress' ),
				'alpha2code' => 'AX',
				'alpha3code' => 'ALA',
			),
			array(
				'name' => esc_attr__( 'Albania', 'fakerpress' ),
				'alpha2code' => 'AL',
				'alpha3code' => 'ALB',
			),
			array(
				'name' => esc_attr__( 'Algeria', 'fakerpress' ),
				'alpha2code' => 'DZ',
				'alpha3code' => 'DZA',
			),
			array(
				'name' => esc_attr__( 'American Samoa', 'fakerpress' ),
				'alpha2code' => 'AS',
				'alpha3code' => 'ASM',
			),
			array(
				'name' => esc_attr__( 'Andorra', 'fakerpress' ),
				'alpha2code' => 'AD',
				'alpha3code' => 'AND',
			),
			array(
				'name' => esc_attr__( 'Angola', 'fakerpress' ),
				'alpha2code' => 'AO',
				'alpha3code' => 'AGO',
			),
			array(
				'name' => esc_attr__( 'Anguilla', 'fakerpress' ),
				'alpha2code' => 'AI',
				'alpha3code' => 'AIA',
			),
			array(
				'name' => esc_attr__( 'Antigua and Barbuda', 'fakerpress' ),
				'alpha2code' => 'AG',
				'alpha3code' => 'ATG',
			),
			array(
				'name' => esc_attr__( 'Argentina', 'fakerpress' ),
				'alpha2code' => 'AR',
				'alpha3code' => 'ARG',
			),
			array(
				'name' => esc_attr__( 'Armenia', 'fakerpress' ),
				'alpha2code' => 'AM',
				'alpha3code' => 'ARM',
			),
			array(
				'name' => esc_attr__( 'Aruba', 'fakerpress' ),
				'alpha2code' => 'AW',
				'alpha3code' => 'ABW',
			),
			array(
				'name' => esc_attr__( 'Australia', 'fakerpress' ),
				'alpha2code' => 'AU',
				'alpha3code' => 'AUS',
			),
			array(
				'name' => esc_attr__( 'Austria', 'fakerpress' ),
				'alpha2code' => 'AT',
				'alpha3code' => 'AUT',
			),
			array(
				'name' => esc_attr__( 'Azerbaijan', 'fakerpress' ),
				'alpha2code' => 'AZ',
				'alpha3code' => 'AZE',
			),
			array(
				'name' => esc_attr__( 'The Bahamas', 'fakerpress' ),
				'alpha2code' => 'BS',
				'alpha3code' => 'BHS',
			),
			array(
				'name' => esc_attr__( 'Bahrain', 'fakerpress' ),
				'alpha2code' => 'BH',
				'alpha3code' => 'BHR',
			),
			array(
				'name' => esc_attr__( 'Bangladesh', 'fakerpress' ),
				'alpha2code' => 'BD',
				'alpha3code' => 'BGD',
			),
			array(
				'name' => esc_attr__( 'Barbados', 'fakerpress' ),
				'alpha2code' => 'BB',
				'alpha3code' => 'BRB',
			),
			array(
				'name' => esc_attr__( 'Belarus', 'fakerpress' ),
				'alpha2code' => 'BY',
				'alpha3code' => 'BLR',
			),
			array(
				'name' => esc_attr__( 'Belgium', 'fakerpress' ),
				'alpha2code' => 'BE',
				'alpha3code' => 'BEL',
			),
			array(
				'name' => esc_attr__( 'Belize', 'fakerpress' ),
				'alpha2code' => 'BZ',
				'alpha3code' => 'BLZ',
			),
			array(
				'name' => esc_attr__( 'Benin', 'fakerpress' ),
				'alpha2code' => 'BJ',
				'alpha3code' => 'BEN',
			),
			array(
				'name' => esc_attr__( 'Bermuda', 'fakerpress' ),
				'alpha2code' => 'BM',
				'alpha3code' => 'BMU',
			),
			array(
				'name' => esc_attr__( 'Bhutan', 'fakerpress' ),
				'alpha2code' => 'BT',
				'alpha3code' => 'BTN',
			),
			array(
				'name' => esc_attr__( 'Bolivia', 'fakerpress' ),
				'alpha2code' => 'BO',
				'alpha3code' => 'BOL',
			),
			array(
				'name' => esc_attr__( 'Bonaire', 'fakerpress' ),
				'alpha2code' => 'BQ',
				'alpha3code' => 'BES',
			),
			array(
				'name' => esc_attr__( 'Bosnia and Herzegovina', 'fakerpress' ),
				'alpha2code' => 'BA',
				'alpha3code' => 'BIH',
			),
			array(
				'name' => esc_attr__( 'Botswana', 'fakerpress' ),
				'alpha2code' => 'BW',
				'alpha3code' => 'BWA',
			),
			array(
				'name' => esc_attr__( 'Bouvet Island', 'fakerpress' ),
				'alpha2code' => 'BV',
				'alpha3code' => 'BVT',
			),
			array(
				'name' => esc_attr__( 'Brazil', 'fakerpress' ),
				'alpha2code' => 'BR',
				'alpha3code' => 'BRA',
			),
			array(
				'name' => esc_attr__( 'British Indian Ocean Territory', 'fakerpress' ),
				'alpha2code' => 'IO',
				'alpha3code' => 'IOT',
			),
			array(
				'name' => esc_attr__( 'United States Minor Outlying Islands', 'fakerpress' ),
				'alpha2code' => 'UM',
				'alpha3code' => 'UMI',
			),
			array(
				'name' => esc_attr__( 'British Virgin Islands', 'fakerpress' ),
				'alpha2code' => 'VG',
				'alpha3code' => 'VGB',
			),
			array(
				'name' => esc_attr__( 'Brunei', 'fakerpress' ),
				'alpha2code' => 'BN',
				'alpha3code' => 'BRN',
			),
			array(
				'name' => esc_attr__( 'Bulgaria', 'fakerpress' ),
				'alpha2code' => 'BG',
				'alpha3code' => 'BGR',
			),
			array(
				'name' => esc_attr__( 'Burkina Faso', 'fakerpress' ),
				'alpha2code' => 'BF',
				'alpha3code' => 'BFA',
			),
			array(
				'name' => esc_attr__( 'Burundi', 'fakerpress' ),
				'alpha2code' => 'BI',
				'alpha3code' => 'BDI',
			),
			array(
				'name' => esc_attr__( 'Cambodia', 'fakerpress' ),
				'alpha2code' => 'KH',
				'alpha3code' => 'KHM',
			),
			array(
				'name' => esc_attr__( 'Cameroon', 'fakerpress' ),
				'alpha2code' => 'CM',
				'alpha3code' => 'CMR',
			),
			array(
				'name' => esc_attr__( 'Canada', 'fakerpress' ),
				'alpha2code' => 'CA',
				'alpha3code' => 'CAN',
			),
			array(
				'name' => esc_attr__( 'Cape Verde', 'fakerpress' ),
				'alpha2code' => 'CV',
				'alpha3code' => 'CPV',
			),
			array(
				'name' => esc_attr__( 'Cayman Islands', 'fakerpress' ),
				'alpha2code' => 'KY',
				'alpha3code' => 'CYM',
			),
			array(
				'name' => esc_attr__( 'Central African Republic', 'fakerpress' ),
				'alpha2code' => 'CF',
				'alpha3code' => 'CAF',
			),
			array(
				'name' => esc_attr__( 'Chad', 'fakerpress' ),
				'alpha2code' => 'TD',
				'alpha3code' => 'TCD',
			),
			array(
				'name' => esc_attr__( 'Chile', 'fakerpress' ),
				'alpha2code' => 'CL',
				'alpha3code' => 'CHL',
			),
			array(
				'name' => esc_attr__( 'China', 'fakerpress' ),
				'alpha2code' => 'CN',
				'alpha3code' => 'CHN',
			),
			array(
				'name' => esc_attr__( 'Christmas Island', 'fakerpress' ),
				'alpha2code' => 'CX',
				'alpha3code' => 'CXR',
			),
			array(
				'name' => esc_attr__( 'Cocos (Keeling) Islands', 'fakerpress' ),
				'alpha2code' => 'CC',
				'alpha3code' => 'CCK',
			),
			array(
				'name' => esc_attr__( 'Colombia', 'fakerpress' ),
				'alpha2code' => 'CO',
				'alpha3code' => 'COL',
			),
			array(
				'name' => esc_attr__( 'Comoros', 'fakerpress' ),
				'alpha2code' => 'KM',
				'alpha3code' => 'COM',
			),
			array(
				'name' => esc_attr__( 'Republic of the Congo', 'fakerpress' ),
				'alpha2code' => 'CG',
				'alpha3code' => 'COG',
			),
			array(
				'name' => esc_attr__( 'Democratic Republic of the Congo', 'fakerpress' ),
				'alpha2code' => 'CD',
				'alpha3code' => 'COD',
			),
			array(
				'name' => esc_attr__( 'Cook Islands', 'fakerpress' ),
				'alpha2code' => 'CK',
				'alpha3code' => 'COK',
			),
			array(
				'name' => esc_attr__( 'Costa Rica', 'fakerpress' ),
				'alpha2code' => 'CR',
				'alpha3code' => 'CRI',
			),
			array(
				'name' => esc_attr__( 'Croatia', 'fakerpress' ),
				'alpha2code' => 'HR',
				'alpha3code' => 'HRV',
			),
			array(
				'name' => esc_attr__( 'Cuba', 'fakerpress' ),
				'alpha2code' => 'CU',
				'alpha3code' => 'CUB',
			),
			array(
				'name' => esc_attr__( 'Curaçao', 'fakerpress' ),
				'alpha2code' => 'CW',
				'alpha3code' => 'CUW',
			),
			array(
				'name' => esc_attr__( 'Cyprus', 'fakerpress' ),
				'alpha2code' => 'CY',
				'alpha3code' => 'CYP',
			),
			array(
				'name' => esc_attr__( 'Czech Republic', 'fakerpress' ),
				'alpha2code' => 'CZ',
				'alpha3code' => 'CZE',
			),
			array(
				'name' => esc_attr__( 'Denmark', 'fakerpress' ),
				'alpha2code' => 'DK',
				'alpha3code' => 'DNK',
			),
			array(
				'name' => esc_attr__( 'Djibouti', 'fakerpress' ),
				'alpha2code' => 'DJ',
				'alpha3code' => 'DJI',
			),
			array(
				'name' => esc_attr__( 'Dominica', 'fakerpress' ),
				'alpha2code' => 'DM',
				'alpha3code' => 'DMA',
			),
			array(
				'name' => esc_attr__( 'Dominican Republic', 'fakerpress' ),
				'alpha2code' => 'DO',
				'alpha3code' => 'DOM',
			),
			array(
				'name' => esc_attr__( 'Ecuador', 'fakerpress' ),
				'alpha2code' => 'EC',
				'alpha3code' => 'ECU',
			),
			array(
				'name' => esc_attr__( 'Egypt', 'fakerpress' ),
				'alpha2code' => 'EG',
				'alpha3code' => 'EGY',
			),
			array(
				'name' => esc_attr__( 'El Salvador', 'fakerpress' ),
				'alpha2code' => 'SV',
				'alpha3code' => 'SLV',
			),
			array(
				'name' => esc_attr__( 'Equatorial Guinea', 'fakerpress' ),
				'alpha2code' => 'GQ',
				'alpha3code' => 'GNQ',
			),
			array(
				'name' => esc_attr__( 'Eritrea', 'fakerpress' ),
				'alpha2code' => 'ER',
				'alpha3code' => 'ERI',
			),
			array(
				'name' => esc_attr__( 'Estonia', 'fakerpress' ),
				'alpha2code' => 'EE',
				'alpha3code' => 'EST',
			),
			array(
				'name' => esc_attr__( 'Ethiopia', 'fakerpress' ),
				'alpha2code' => 'ET',
				'alpha3code' => 'ETH',
			),
			array(
				'name' => esc_attr__( 'Falkland Islands', 'fakerpress' ),
				'alpha2code' => 'FK',
				'alpha3code' => 'FLK',
			),
			array(
				'name' => esc_attr__( 'Faroe Islands', 'fakerpress' ),
				'alpha2code' => 'FO',
				'alpha3code' => 'FRO',
			),
			array(
				'name' => esc_attr__( 'Fiji', 'fakerpress' ),
				'alpha2code' => 'FJ',
				'alpha3code' => 'FJI',
			),
			array(
				'name' => esc_attr__( 'Finland', 'fakerpress' ),
				'alpha2code' => 'FI',
				'alpha3code' => 'FIN',
			),
			array(
				'name' => esc_attr__( 'France', 'fakerpress' ),
				'alpha2code' => 'FR',
				'alpha3code' => 'FRA',
			),
			array(
				'name' => esc_attr__( 'French Guiana', 'fakerpress' ),
				'alpha2code' => 'GF',
				'alpha3code' => 'GUF',
			),
			array(
				'name' => esc_attr__( 'French Polynesia', 'fakerpress' ),
				'alpha2code' => 'PF',
				'alpha3code' => 'PYF',
			),
			array(
				'name' => esc_attr__( 'French Southern and Antarctic Lands', 'fakerpress' ),
				'alpha2code' => 'TF',
				'alpha3code' => 'ATF',
			),
			array(
				'name' => esc_attr__( 'Gabon', 'fakerpress' ),
				'alpha2code' => 'GA',
				'alpha3code' => 'GAB',
			),
			array(
				'name' => esc_attr__( 'The Gambia', 'fakerpress' ),
				'alpha2code' => 'GM',
				'alpha3code' => 'GMB',
			),
			array(
				'name' => esc_attr__( 'Georgia', 'fakerpress' ),
				'alpha2code' => 'GE',
				'alpha3code' => 'GEO',
			),
			array(
				'name' => esc_attr__( 'Germany', 'fakerpress' ),
				'alpha2code' => 'DE',
				'alpha3code' => 'DEU',
			),
			array(
				'name' => esc_attr__( 'Ghana', 'fakerpress' ),
				'alpha2code' => 'GH',
				'alpha3code' => 'GHA',
			),
			array(
				'name' => esc_attr__( 'Gibraltar', 'fakerpress' ),
				'alpha2code' => 'GI',
				'alpha3code' => 'GIB',
			),
			array(
				'name' => esc_attr__( 'Greece', 'fakerpress' ),
				'alpha2code' => 'GR',
				'alpha3code' => 'GRC',
			),
			array(
				'name' => esc_attr__( 'Greenland', 'fakerpress' ),
				'alpha2code' => 'GL',
				'alpha3code' => 'GRL',
			),
			array(
				'name' => esc_attr__( 'Grenada', 'fakerpress' ),
				'alpha2code' => 'GD',
				'alpha3code' => 'GRD',
			),
			array(
				'name' => esc_attr__( 'Guadeloupe', 'fakerpress' ),
				'alpha2code' => 'GP',
				'alpha3code' => 'GLP',
			),
			array(
				'name' => esc_attr__( 'Guam', 'fakerpress' ),
				'alpha2code' => 'GU',
				'alpha3code' => 'GUM',
			),
			array(
				'name' => esc_attr__( 'Guatemala', 'fakerpress' ),
				'alpha2code' => 'GT',
				'alpha3code' => 'GTM',
			),
			array(
				'name' => esc_attr__( 'Guernsey', 'fakerpress' ),
				'alpha2code' => 'GG',
				'alpha3code' => 'GGY',
			),
			array(
				'name' => esc_attr__( 'Guinea', 'fakerpress' ),
				'alpha2code' => 'GN',
				'alpha3code' => 'GIN',
			),
			array(
				'name' => esc_attr__( 'Guinea-Bissau', 'fakerpress' ),
				'alpha2code' => 'GW',
				'alpha3code' => 'GNB',
			),
			array(
				'name' => esc_attr__( 'Guyana', 'fakerpress' ),
				'alpha2code' => 'GY',
				'alpha3code' => 'GUY',
			),
			array(
				'name' => esc_attr__( 'Haiti', 'fakerpress' ),
				'alpha2code' => 'HT',
				'alpha3code' => 'HTI',
			),
			array(
				'name' => esc_attr__( 'Heard Island and McDonald Islands', 'fakerpress' ),
				'alpha2code' => 'HM',
				'alpha3code' => 'HMD',
			),
			array(
				'name' => esc_attr__( 'Honduras', 'fakerpress' ),
				'alpha2code' => 'HN',
				'alpha3code' => 'HND',
			),
			array(
				'name' => esc_attr__( 'Hong Kong', 'fakerpress' ),
				'alpha2code' => 'HK',
				'alpha3code' => 'HKG',
			),
			array(
				'name' => esc_attr__( 'Hungary', 'fakerpress' ),
				'alpha2code' => 'HU',
				'alpha3code' => 'HUN',
			),
			array(
				'name' => esc_attr__( 'Iceland', 'fakerpress' ),
				'alpha2code' => 'IS',
				'alpha3code' => 'ISL',
			),
			array(
				'name' => esc_attr__( 'India', 'fakerpress' ),
				'alpha2code' => 'IN',
				'alpha3code' => 'IND',
			),
			array(
				'name' => esc_attr__( 'Indonesia', 'fakerpress' ),
				'alpha2code' => 'ID',
				'alpha3code' => 'IDN',
			),
			array(
				'name' => esc_attr__( 'Ivory Coast', 'fakerpress' ),
				'alpha2code' => 'CI',
				'alpha3code' => 'CIV',
			),
			array(
				'name' => esc_attr__( 'Iran', 'fakerpress' ),
				'alpha2code' => 'IR',
				'alpha3code' => 'IRN',
			),
			array(
				'name' => esc_attr__( 'Iraq', 'fakerpress' ),
				'alpha2code' => 'IQ',
				'alpha3code' => 'IRQ',
			),
			array(
				'name' => esc_attr__( 'Republic of Ireland', 'fakerpress' ),
				'alpha2code' => 'IE',
				'alpha3code' => 'IRL',
			),
			array(
				'name' => esc_attr__( 'Isle of Man', 'fakerpress' ),
				'alpha2code' => 'IM',
				'alpha3code' => 'IMN',
			),
			array(
				'name' => esc_attr__( 'Israel', 'fakerpress' ),
				'alpha2code' => 'IL',
				'alpha3code' => 'ISR',
			),
			array(
				'name' => esc_attr__( 'Italy', 'fakerpress' ),
				'alpha2code' => 'IT',
				'alpha3code' => 'ITA',
			),
			array(
				'name' => esc_attr__( 'Jamaica', 'fakerpress' ),
				'alpha2code' => 'JM',
				'alpha3code' => 'JAM',
			),
			array(
				'name' => esc_attr__( 'Japan', 'fakerpress' ),
				'alpha2code' => 'JP',
				'alpha3code' => 'JPN',
			),
			array(
				'name' => esc_attr__( 'Jersey', 'fakerpress' ),
				'alpha2code' => 'JE',
				'alpha3code' => 'JEY',
			),
			array(
				'name' => esc_attr__( 'Jordan', 'fakerpress' ),
				'alpha2code' => 'JO',
				'alpha3code' => 'JOR',
			),
			array(
				'name' => esc_attr__( 'Kazakhstan', 'fakerpress' ),
				'alpha2code' => 'KZ',
				'alpha3code' => 'KAZ',
			),
			array(
				'name' => esc_attr__( 'Kenya', 'fakerpress' ),
				'alpha2code' => 'KE',
				'alpha3code' => 'KEN',
			),
			array(
				'name' => esc_attr__( 'Kiribati', 'fakerpress' ),
				'alpha2code' => 'KI',
				'alpha3code' => 'KIR',
			),
			array(
				'name' => esc_attr__( 'Kuwait', 'fakerpress' ),
				'alpha2code' => 'KW',
				'alpha3code' => 'KWT',
			),
			array(
				'name' => esc_attr__( 'Kyrgyzstan', 'fakerpress' ),
				'alpha2code' => 'KG',
				'alpha3code' => 'KGZ',
			),
			array(
				'name' => esc_attr__( 'Laos', 'fakerpress' ),
				'alpha2code' => 'LA',
				'alpha3code' => 'LAO',
			),
			array(
				'name' => esc_attr__( 'Latvia', 'fakerpress' ),
				'alpha2code' => 'LV',
				'alpha3code' => 'LVA',
			),
			array(
				'name' => esc_attr__( 'Lebanon', 'fakerpress' ),
				'alpha2code' => 'LB',
				'alpha3code' => 'LBN',
			),
			array(
				'name' => esc_attr__( 'Lesotho', 'fakerpress' ),
				'alpha2code' => 'LS',
				'alpha3code' => 'LSO',
			),
			array(
				'name' => esc_attr__( 'Liberia', 'fakerpress' ),
				'alpha2code' => 'LR',
				'alpha3code' => 'LBR',
			),
			array(
				'name' => esc_attr__( 'Libya', 'fakerpress' ),
				'alpha2code' => 'LY',
				'alpha3code' => 'LBY',
			),
			array(
				'name' => esc_attr__( 'Liechtenstein', 'fakerpress' ),
				'alpha2code' => 'LI',
				'alpha3code' => 'LIE',
			),
			array(
				'name' => esc_attr__( 'Lithuania', 'fakerpress' ),
				'alpha2code' => 'LT',
				'alpha3code' => 'LTU',
			),
			array(
				'name' => esc_attr__( 'Luxembourg', 'fakerpress' ),
				'alpha2code' => 'LU',
				'alpha3code' => 'LUX',
			),
			array(
				'name' => esc_attr__( 'Macau', 'fakerpress' ),
				'alpha2code' => 'MO',
				'alpha3code' => 'MAC',
			),
			array(
				'name' => esc_attr__( 'Republic of Macedonia', 'fakerpress' ),
				'alpha2code' => 'MK',
				'alpha3code' => 'MKD',
			),
			array(
				'name' => esc_attr__( 'Madagascar', 'fakerpress' ),
				'alpha2code' => 'MG',
				'alpha3code' => 'MDG',
			),
			array(
				'name' => esc_attr__( 'Malawi', 'fakerpress' ),
				'alpha2code' => 'MW',
				'alpha3code' => 'MWI',
			),
			array(
				'name' => esc_attr__( 'Malaysia', 'fakerpress' ),
				'alpha2code' => 'MY',
				'alpha3code' => 'MYS',
			),
			array(
				'name' => esc_attr__( 'Maldives', 'fakerpress' ),
				'alpha2code' => 'MV',
				'alpha3code' => 'MDV',
			),
			array(
				'name' => esc_attr__( 'Mali', 'fakerpress' ),
				'alpha2code' => 'ML',
				'alpha3code' => 'MLI',
			),
			array(
				'name' => esc_attr__( 'Malta', 'fakerpress' ),
				'alpha2code' => 'MT',
				'alpha3code' => 'MLT',
			),
			array(
				'name' => esc_attr__( 'Marshall Islands', 'fakerpress' ),
				'alpha2code' => 'MH',
				'alpha3code' => 'MHL',
			),
			array(
				'name' => esc_attr__( 'Martinique', 'fakerpress' ),
				'alpha2code' => 'MQ',
				'alpha3code' => 'MTQ',
			),
			array(
				'name' => esc_attr__( 'Mauritania', 'fakerpress' ),
				'alpha2code' => 'MR',
				'alpha3code' => 'MRT',
			),
			array(
				'name' => esc_attr__( 'Mauritius', 'fakerpress' ),
				'alpha2code' => 'MU',
				'alpha3code' => 'MUS',
			),
			array(
				'name' => esc_attr__( 'Mayotte', 'fakerpress' ),
				'alpha2code' => 'YT',
				'alpha3code' => 'MYT',
			),
			array(
				'name' => esc_attr__( 'Mexico', 'fakerpress' ),
				'alpha2code' => 'MX',
				'alpha3code' => 'MEX',
			),
			array(
				'name' => esc_attr__( 'Federated States of Micronesia', 'fakerpress' ),
				'alpha2code' => 'FM',
				'alpha3code' => 'FSM',
			),
			array(
				'name' => esc_attr__( 'Moldova', 'fakerpress' ),
				'alpha2code' => 'MD',
				'alpha3code' => 'MDA',
			),
			array(
				'name' => esc_attr__( 'Monaco', 'fakerpress' ),
				'alpha2code' => 'MC',
				'alpha3code' => 'MCO',
			),
			array(
				'name' => esc_attr__( 'Mongolia', 'fakerpress' ),
				'alpha2code' => 'MN',
				'alpha3code' => 'MNG',
			),
			array(
				'name' => esc_attr__( 'Montenegro', 'fakerpress' ),
				'alpha2code' => 'ME',
				'alpha3code' => 'MNE',
			),
			array(
				'name' => esc_attr__( 'Montserrat', 'fakerpress' ),
				'alpha2code' => 'MS',
				'alpha3code' => 'MSR',
			),
			array(
				'name' => esc_attr__( 'Morocco', 'fakerpress' ),
				'alpha2code' => 'MA',
				'alpha3code' => 'MAR',
			),
			array(
				'name' => esc_attr__( 'Mozambique', 'fakerpress' ),
				'alpha2code' => 'MZ',
				'alpha3code' => 'MOZ',
			),
			array(
				'name' => esc_attr__( 'Myanmar', 'fakerpress' ),
				'alpha2code' => 'MM',
				'alpha3code' => 'MMR',
			),
			array(
				'name' => esc_attr__( 'Namibia', 'fakerpress' ),
				'alpha2code' => 'NA',
				'alpha3code' => 'NAM',
			),
			array(
				'name' => esc_attr__( 'Nauru', 'fakerpress' ),
				'alpha2code' => 'NR',
				'alpha3code' => 'NRU',
			),
			array(
				'name' => esc_attr__( 'Nepal', 'fakerpress' ),
				'alpha2code' => 'NP',
				'alpha3code' => 'NPL',
			),
			array(
				'name' => esc_attr__( 'Netherlands', 'fakerpress' ),
				'alpha2code' => 'NL',
				'alpha3code' => 'NLD',
			),
			array(
				'name' => esc_attr__( 'New Caledonia', 'fakerpress' ),
				'alpha2code' => 'NC',
				'alpha3code' => 'NCL',
			),
			array(
				'name' => esc_attr__( 'New Zealand', 'fakerpress' ),
				'alpha2code' => 'NZ',
				'alpha3code' => 'NZL',
			),
			array(
				'name' => esc_attr__( 'Nicaragua', 'fakerpress' ),
				'alpha2code' => 'NI',
				'alpha3code' => 'NIC',
			),
			array(
				'name' => esc_attr__( 'Niger', 'fakerpress' ),
				'alpha2code' => 'NE',
				'alpha3code' => 'NER',
			),
			array(
				'name' => esc_attr__( 'Nigeria', 'fakerpress' ),
				'alpha2code' => 'NG',
				'alpha3code' => 'NGA',
			),
			array(
				'name' => esc_attr__( 'Niue', 'fakerpress' ),
				'alpha2code' => 'NU',
				'alpha3code' => 'NIU',
			),
			array(
				'name' => esc_attr__( 'Norfolk Island', 'fakerpress' ),
				'alpha2code' => 'NF',
				'alpha3code' => 'NFK',
			),
			array(
				'name' => esc_attr__( 'North Korea', 'fakerpress' ),
				'alpha2code' => 'KP',
				'alpha3code' => 'PRK',
			),
			array(
				'name' => esc_attr__( 'Northern Mariana Islands', 'fakerpress' ),
				'alpha2code' => 'MP',
				'alpha3code' => 'MNP',
			),
			array(
				'name' => esc_attr__( 'Norway', 'fakerpress' ),
				'alpha2code' => 'NO',
				'alpha3code' => 'NOR',
			),
			array(
				'name' => esc_attr__( 'Oman', 'fakerpress' ),
				'alpha2code' => 'OM',
				'alpha3code' => 'OMN',
			),
			array(
				'name' => esc_attr__( 'Pakistan', 'fakerpress' ),
				'alpha2code' => 'PK',
				'alpha3code' => 'PAK',
			),
			array(
				'name' => esc_attr__( 'Palau', 'fakerpress' ),
				'alpha2code' => 'PW',
				'alpha3code' => 'PLW',
			),
			array(
				'name' => esc_attr__( 'Palestine', 'fakerpress' ),
				'alpha2code' => 'PS',
				'alpha3code' => 'PSE',
			),
			array(
				'name' => esc_attr__( 'Panama', 'fakerpress' ),
				'alpha2code' => 'PA',
				'alpha3code' => 'PAN',
			),
			array(
				'name' => esc_attr__( 'Papua New Guinea', 'fakerpress' ),
				'alpha2code' => 'PG',
				'alpha3code' => 'PNG',
			),
			array(
				'name' => esc_attr__( 'Paraguay', 'fakerpress' ),
				'alpha2code' => 'PY',
				'alpha3code' => 'PRY',
			),
			array(
				'name' => esc_attr__( 'Peru', 'fakerpress' ),
				'alpha2code' => 'PE',
				'alpha3code' => 'PER',
			),
			array(
				'name' => esc_attr__( 'Philippines', 'fakerpress' ),
				'alpha2code' => 'PH',
				'alpha3code' => 'PHL',
			),
			array(
				'name' => esc_attr__( 'Pitcairn Islands', 'fakerpress' ),
				'alpha2code' => 'PN',
				'alpha3code' => 'PCN',
			),
			array(
				'name' => esc_attr__( 'Poland', 'fakerpress' ),
				'alpha2code' => 'PL',
				'alpha3code' => 'POL',
			),
			array(
				'name' => esc_attr__( 'Portugal', 'fakerpress' ),
				'alpha2code' => 'PT',
				'alpha3code' => 'PRT',
			),
			array(
				'name' => esc_attr__( 'Puerto Rico', 'fakerpress' ),
				'alpha2code' => 'PR',
				'alpha3code' => 'PRI',
			),
			array(
				'name' => esc_attr__( 'Qatar', 'fakerpress' ),
				'alpha2code' => 'QA',
				'alpha3code' => 'QAT',
			),
			array(
				'name' => esc_attr__( 'Republic of Kosovo', 'fakerpress' ),
				'alpha2code' => 'XK',
				'alpha3code' => 'KOS',
			),
			array(
				'name' => esc_attr__( 'Réunion', 'fakerpress' ),
				'alpha2code' => 'RE',
				'alpha3code' => 'REU',
			),
			array(
				'name' => esc_attr__( 'Romania', 'fakerpress' ),
				'alpha2code' => 'RO',
				'alpha3code' => 'ROU',
			),
			array(
				'name' => esc_attr__( 'Russia', 'fakerpress' ),
				'alpha2code' => 'RU',
				'alpha3code' => 'RUS',
			),
			array(
				'name' => esc_attr__( 'Rwanda', 'fakerpress' ),
				'alpha2code' => 'RW',
				'alpha3code' => 'RWA',
			),
			array(
				'name' => esc_attr__( 'Saint Barthélemy', 'fakerpress' ),
				'alpha2code' => 'BL',
				'alpha3code' => 'BLM',
			),
			array(
				'name' => esc_attr__( 'Saint Helena', 'fakerpress' ),
				'alpha2code' => 'SH',
				'alpha3code' => 'SHN',
			),
			array(
				'name' => esc_attr__( 'Saint Kitts and Nevis', 'fakerpress' ),
				'alpha2code' => 'KN',
				'alpha3code' => 'KNA',
			),
			array(
				'name' => esc_attr__( 'Saint Lucia', 'fakerpress' ),
				'alpha2code' => 'LC',
				'alpha3code' => 'LCA',
			),
			array(
				'name' => esc_attr__( 'Saint Martin', 'fakerpress' ),
				'alpha2code' => 'MF',
				'alpha3code' => 'MAF',
			),
			array(
				'name' => esc_attr__( 'Saint Pierre and Miquelon', 'fakerpress' ),
				'alpha2code' => 'PM',
				'alpha3code' => 'SPM',
			),
			array(
				'name' => esc_attr__( 'Saint Vincent and the Grenadines', 'fakerpress' ),
				'alpha2code' => 'VC',
				'alpha3code' => 'VCT',
			),
			array(
				'name' => esc_attr__( 'Samoa', 'fakerpress' ),
				'alpha2code' => 'WS',
				'alpha3code' => 'WSM',
			),
			array(
				'name' => esc_attr__( 'San Marino', 'fakerpress' ),
				'alpha2code' => 'SM',
				'alpha3code' => 'SMR',
			),
			array(
				'name' => esc_attr__( 'São Tomé and Príncipe', 'fakerpress' ),
				'alpha2code' => 'ST',
				'alpha3code' => 'STP',
			),
			array(
				'name' => esc_attr__( 'Saudi Arabia', 'fakerpress' ),
				'alpha2code' => 'SA',
				'alpha3code' => 'SAU',
			),
			array(
				'name' => esc_attr__( 'Senegal', 'fakerpress' ),
				'alpha2code' => 'SN',
				'alpha3code' => 'SEN',
			),
			array(
				'name' => esc_attr__( 'Serbia', 'fakerpress' ),
				'alpha2code' => 'RS',
				'alpha3code' => 'SRB',
			),
			array(
				'name' => esc_attr__( 'Seychelles', 'fakerpress' ),
				'alpha2code' => 'SC',
				'alpha3code' => 'SYC',
			),
			array(
				'name' => esc_attr__( 'Sierra Leone', 'fakerpress' ),
				'alpha2code' => 'SL',
				'alpha3code' => 'SLE',
			),
			array(
				'name' => esc_attr__( 'Singapore', 'fakerpress' ),
				'alpha2code' => 'SG',
				'alpha3code' => 'SGP',
			),
			array(
				'name' => esc_attr__( 'Sint Maarten', 'fakerpress' ),
				'alpha2code' => 'SX',
				'alpha3code' => 'SXM',
			),
			array(
				'name' => esc_attr__( 'Slovakia', 'fakerpress' ),
				'alpha2code' => 'SK',
				'alpha3code' => 'SVK',
			),
			array(
				'name' => esc_attr__( 'Slovenia', 'fakerpress' ),
				'alpha2code' => 'SI',
				'alpha3code' => 'SVN',
			),
			array(
				'name' => esc_attr__( 'Solomon Islands', 'fakerpress' ),
				'alpha2code' => 'SB',
				'alpha3code' => 'SLB',
			),
			array(
				'name' => esc_attr__( 'Somalia', 'fakerpress' ),
				'alpha2code' => 'SO',
				'alpha3code' => 'SOM',
			),
			array(
				'name' => esc_attr__( 'South Africa', 'fakerpress' ),
				'alpha2code' => 'ZA',
				'alpha3code' => 'ZAF',
			),
			array(
				'name' => esc_attr__( 'South Georgia', 'fakerpress' ),
				'alpha2code' => 'GS',
				'alpha3code' => 'SGS',
			),
			array(
				'name' => esc_attr__( 'South Korea', 'fakerpress' ),
				'alpha2code' => 'KR',
				'alpha3code' => 'KOR',
			),
			array(
				'name' => esc_attr__( 'South Sudan', 'fakerpress' ),
				'alpha2code' => 'SS',
				'alpha3code' => 'SSD',
			),
			array(
				'name' => esc_attr__( 'Spain', 'fakerpress' ),
				'alpha2code' => 'ES',
				'alpha3code' => 'ESP',
			),
			array(
				'name' => esc_attr__( 'Sri Lanka', 'fakerpress' ),
				'alpha2code' => 'LK',
				'alpha3code' => 'LKA',
			),
			array(
				'name' => esc_attr__( 'Sudan', 'fakerpress' ),
				'alpha2code' => 'SD',
				'alpha3code' => 'SDN',
			),
			array(
				'name' => esc_attr__( 'Suriname', 'fakerpress' ),
				'alpha2code' => 'SR',
				'alpha3code' => 'SUR',
			),
			array(
				'name' => esc_attr__( 'Svalbard and Jan Mayen', 'fakerpress' ),
				'alpha2code' => 'SJ',
				'alpha3code' => 'SJM',
			),
			array(
				'name' => esc_attr__( 'Swaziland', 'fakerpress' ),
				'alpha2code' => 'SZ',
				'alpha3code' => 'SWZ',
			),
			array(
				'name' => esc_attr__( 'Sweden', 'fakerpress' ),
				'alpha2code' => 'SE',
				'alpha3code' => 'SWE',
			),
			array(
				'name' => esc_attr__( 'Switzerland', 'fakerpress' ),
				'alpha2code' => 'CH',
				'alpha3code' => 'CHE',
			),
			array(
				'name' => esc_attr__( 'Syria', 'fakerpress' ),
				'alpha2code' => 'SY',
				'alpha3code' => 'SYR',
			),
			array(
				'name' => esc_attr__( 'Taiwan', 'fakerpress' ),
				'alpha2code' => 'TW',
				'alpha3code' => 'TWN',
			),
			array(
				'name' => esc_attr__( 'Tajikistan', 'fakerpress' ),
				'alpha2code' => 'TJ',
				'alpha3code' => 'TJK',
			),
			array(
				'name' => esc_attr__( 'Tanzania', 'fakerpress' ),
				'alpha2code' => 'TZ',
				'alpha3code' => 'TZA',
			),
			array(
				'name' => esc_attr__( 'Thailand', 'fakerpress' ),
				'alpha2code' => 'TH',
				'alpha3code' => 'THA',
			),
			array(
				'name' => esc_attr__( 'East Timor', 'fakerpress' ),
				'alpha2code' => 'TL',
				'alpha3code' => 'TLS',
			),
			array(
				'name' => esc_attr__( 'Togo', 'fakerpress' ),
				'alpha2code' => 'TG',
				'alpha3code' => 'TGO',
			),
			array(
				'name' => esc_attr__( 'Tokelau', 'fakerpress' ),
				'alpha2code' => 'TK',
				'alpha3code' => 'TKL',
			),
			array(
				'name' => esc_attr__( 'Tonga', 'fakerpress' ),
				'alpha2code' => 'TO',
				'alpha3code' => 'TON',
			),
			array(
				'name' => esc_attr__( 'Trinidad and Tobago', 'fakerpress' ),
				'alpha2code' => 'TT',
				'alpha3code' => 'TTO',
			),
			array(
				'name' => esc_attr__( 'Tunisia', 'fakerpress' ),
				'alpha2code' => 'TN',
				'alpha3code' => 'TUN',
			),
			array(
				'name' => esc_attr__( 'Turkey', 'fakerpress' ),
				'alpha2code' => 'TR',
				'alpha3code' => 'TUR',
			),
			array(
				'name' => esc_attr__( 'Turkmenistan', 'fakerpress' ),
				'alpha2code' => 'TM',
				'alpha3code' => 'TKM',
			),
			array(
				'name' => esc_attr__( 'Turks and Caicos Islands', 'fakerpress' ),
				'alpha2code' => 'TC',
				'alpha3code' => 'TCA',
			),
			array(
				'name' => esc_attr__( 'Tuvalu', 'fakerpress' ),
				'alpha2code' => 'TV',
				'alpha3code' => 'TUV',
			),
			array(
				'name' => esc_attr__( 'Uganda', 'fakerpress' ),
				'alpha2code' => 'UG',
				'alpha3code' => 'UGA',
			),
			array(
				'name' => esc_attr__( 'Ukraine', 'fakerpress' ),
				'alpha2code' => 'UA',
				'alpha3code' => 'UKR',
			),
			array(
				'name' => esc_attr__( 'United Arab Emirates', 'fakerpress' ),
				'alpha2code' => 'AE',
				'alpha3code' => 'ARE',
			),
			array(
				'name' => esc_attr__( 'United Kingdom', 'fakerpress' ),
				'alpha2code' => 'GB',
				'alpha3code' => 'GBR',
			),
			array(
				'name' => esc_attr__( 'United States', 'fakerpress' ),
				'alpha2code' => 'US',
				'alpha3code' => 'USA',
			),
			array(
				'name' => esc_attr__( 'Uruguay', 'fakerpress' ),
				'alpha2code' => 'UY',
				'alpha3code' => 'URY',
			),
			array(
				'name' => esc_attr__( 'Uzbekistan', 'fakerpress' ),
				'alpha2code' => 'UZ',
				'alpha3code' => 'UZB',
			),
			array(
				'name' => esc_attr__( 'Vanuatu', 'fakerpress' ),
				'alpha2code' => 'VU',
				'alpha3code' => 'VUT',
			),
			array(
				'name' => esc_attr__( 'Venezuela', 'fakerpress' ),
				'alpha2code' => 'VE',
				'alpha3code' => 'VEN',
			),
			array(
				'name' => esc_attr__( 'Vietnam', 'fakerpress' ),
				'alpha2code' => 'VN',
				'alpha3code' => 'VNM',
			),
			array(
				'name' => esc_attr__( 'Wallis and Futuna', 'fakerpress' ),
				'alpha2code' => 'WF',
				'alpha3code' => 'WLF',
			),
			array(
				'name' => esc_attr__( 'Western Sahara', 'fakerpress' ),
				'alpha2code' => 'EH',
				'alpha3code' => 'ESH',
			),
			array(
				'name' => esc_attr__( 'Yemen', 'fakerpress' ),
				'alpha2code' => 'YE',
				'alpha3code' => 'YEM',
			),
			array(
				'name' => esc_attr__( 'Zambia', 'fakerpress' ),
				'alpha2code' => 'ZM',
				'alpha3code' => 'ZMB',
			),
			array(
				'name' => esc_attr__( 'Zimbabwe', 'fakerpress' ),
				'alpha2code' => 'ZW',
				'alpha3code' => 'ZWE',
			),
		);

		foreach ( $countries as $index => $country ) {
			if ( $country['name'] === $country_name && ! empty( $country[ 'alpha' . $type . 'code' ] ) ) {
				$code = $country[ 'alpha' . $type . 'code' ];
				break;
			}
		}
		return $code;
	}
}