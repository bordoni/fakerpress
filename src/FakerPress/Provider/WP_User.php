<?php
namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress;
use FakerPress\Utils;
use function FakerPress\make;

class WP_User extends Base {

	/**
	 * Returns a first name, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $first_name
	 * @param string[]    $gender
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
	 * @param string|null $last_name
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
	 * @param string|null $login
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
	 * @param string|null $nicename
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
	 *
	 * @param string|null $url
	 *
	 * @return string|null
	 */
	public function user_url( ?string $url = null ): ?string {
		return $url ?? $this->generator->url();
	}

	/**
	 * Returns a random email, if nothing was passed, it will generate a random one.
	 *
	 * @since 0.1.0
	 * @since 0.6.2 Introduced type safety.
	 *
	 * @param string|null $email
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
	 * @param string|null $display_name
	 * @param string[]    $gender
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
	 * @param string|null $nickname
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
	 * @param string|null $pass
	 * @param int		 $qty
	 *
	 * @return string|null
	 */
	public function user_pass( ?string $pass = null, int $qty = 10 ): ?string {
		if ( is_null( $pass ) ) {
			if ( function_exists( 'wp_generate_password' ) ) {
				$pass = wp_generate_password( $qty );
			} else {
				$pass = $this->generator->randomNumber( $qty - 1 ) . $this->generator->randomLetter();
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
	 * @param bool $html Whether to return HTML or plain text.
	 * @param array $args
	 *
	 * @return string|null
	 */
	public function description( $html = true, $args = [] ): ?string {
		$defaults = [
			'qty' => [ 5, 15 ],
		];
		$args = wp_parse_args( $args, $defaults );

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
	 * @param string[] $role
	 *
	 * @return string
	 */
	public function role( ?array $role = [] ): ?string {
		return $this->generator->randomElement( $role ?? array_keys( get_editable_roles() ) );
	}

	public function user_registered( $min = 'now', $max = null ) {
		try {
			$min = new \FakerPress\ThirdParty\Carbon\Carbon( $min );
		} catch ( \Exception $e ) {
			return null;
		}

		if ( ! is_null( $max ) ) {
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \FakerPress\ThirdParty\Carbon\Carbon( $max );
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
