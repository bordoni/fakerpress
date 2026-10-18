<?php
/**
 * Faker provider for WordPress user fields.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\Utils;
use function FakerPress\make;

/**
 * Generates the fields of a fake user: names, login, email, URL, password, role and dates.
 */
class WP_User extends Base {

	/**
	 * Returns a first name, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $first_name First name to use as is; null generates one.
	 * @param string[]    $gender     Genders to pick from when generating the name.
	 *
	 * @return string|null
	 */
	public function first_name( ?string $first_name = null, array $gender = [ 'male', 'female' ] ): ?string {
		return $first_name ?? $this->generator->firstName( $this->generator->randomElements( $gender, 1 ) );
	}

	/**
	 * Returns a last name, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $last_name Last name to use as is; null generates one.
	 *
	 * @return string|null
	 */
	public function last_name( ?string $last_name = null ): ?string {
		return $last_name ?? $this->generator->lastName();
	}

	/**
	 * Returns a random username, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $login Login to use as is; null generates one.
	 *
	 * @return string|null
	 */
	public function user_login( ?string $login = null ): ?string {
		return $login ?? $this->generator->userName();
	}

	/**
	 * Returns a random nicename, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $nicename Nicename to use as is; null generates a username.
	 *
	 * @return string|null
	 */
	public function user_nicename( ?string $nicename = null ): ?string {
		return $nicename ?? $this->generator->userName();
	}

	/**
	 * Returns a random URL, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 * @since 0.10.0   Keeps generated URLs within WordPress's 100-character limit.
	 *
	 * @param string|null $url URL to use as is; null generates one of at most 100 characters.
	 *
	 * @return string|null
	 */
	public function user_url( ?string $url = null ): ?string {
		if ( null !== $url ) {
			return $url;
		}

		// WordPress rejects user URLs over 100 characters (user_url_too_long), which silently drops the user.
		for ( $attempt = 0; $attempt < 5; $attempt++ ) {
			$url = $this->generator->url();
			if ( mb_strlen( $url ) <= 100 ) {
				return $url;
			}
		}

		return 'https://' . $this->generator->domainName() . '/';
	}

	/**
	 * Returns a random email, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $email Email to use as is; null generates a safe example.* address.
	 *
	 * @return string|null
	 */
	public function user_email( ?string $email = null ): ?string {
		return $email ?? $this->generator->safeEmail();
	}

	/**
	 * Returns a random display name, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $display_name Display name to use as is; null generates a first name.
	 * @param string[]    $gender       Genders to pick from when generating the name.
	 *
	 * @return string|null
	 */
	public function display_name( ?string $display_name = null, array $gender = [ 'male', 'female' ] ): ?string {
		return $display_name ?? $this->generator->firstName( $this->generator->randomElements( $gender, 1 ) );
	}

	/**
	 * Returns a random nickname, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $nickname Nickname to use as is; null generates a username.
	 *
	 * @return string|null
	 */
	public function nickname( ?string $nickname = null ): ?string {
		return $nickname ?? $this->generator->userName();
	}

	/**
	 * Returns a random password, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $pass Password to use as is; null generates one.
	 * @param int         $qty  Length of the generated password.
	 *
	 * @return string|null
	 */
	public function user_pass( ?string $pass = null, int $qty = 16 ): ?string {
		if ( is_null( $pass ) ) {
			if ( function_exists( 'wp_generate_password' ) ) {
				$pass = wp_generate_password( $qty, true );
			} else {
				$pass = $this->generator->password( $qty );
			}
		}
		return $pass;
	}

	/**
	 * Returns a random description.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param bool  $html Whether to return HTML or plain text.
	 * @param array $args Generation arguments; `qty` is the number of elements or paragraphs, or a [ min, max ] range.
	 *
	 * @return string|null
	 */
	public function description( $html = true, $args = [] ): ?string {
		$defaults = [
			'qty' => [ 5, 15 ],
		];
		$args     = wp_parse_args( $args, $defaults );

		if ( true === $html ) {
			$content = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$content = implode( "\r\n\r\n", $this->generator->paragraphs( make( Utils::class )->get_qty_from_range( $args['qty'] ) ) );
		}

		return $content;
	}

	/**
	 * Returns a random role, if nothing was passed, it will pick a random one from the available roles.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string[]|null $role Roles to pick from; null picks from the editable roles.
	 *
	 * @return string|null The role, or null when the list is empty.
	 */
	public function role( ?array $role = [] ): ?string {
		return $this->generator->randomElement( $role ?? array_keys( get_editable_roles() ) );
	}

	/**
	 * Generates a registration date.
	 *
	 * @param string      $min Date string for the start of the range, or the exact date when `$max` is null.
	 * @param string|null $max Date string for the end of the range; null returns `$min` as is.
	 *
	 * @return string|null The date as `Y-m-d H:i:s` (or `$min` cast to string), or null when a date string is invalid.
	 */
	public function user_registered( $min = 'now', $max = null ) {
		try {
			$min = new Chronos( $min );
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! is_null( $max ) ) {
			// Unfortunately there is not such solution to this problem, we need to try and catch with DateTime.
			try {
				$max = new Chronos( $max );
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
