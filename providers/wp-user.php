<?php
namespace Faker\Provider;

class WP_User extends Base {

	public function user_login( $login = null ) {
		if ( is_null( $login ) ){
			$login = $this->generator->userName;
		}
		return $login;
	}

	public function user_pass( $pass = null, $qty = 10 ) {
		if ( is_null( $pass ) ){
			// By the way we should be using the WordPress wp_generate_password
			$pass = $this->generator->randomNumber( $qty - 1 ) + $this->generator->randomLetter();
		}
		return $pass;
	}

	public function role( $role = null ) {
		if ( is_null( $role ) ){
			$role = array_keys( get_editable_roles() );
		}

		return $this->generator->randomElement( $role );
	}

	public function user_nicename( $nicename = null ) {
		if ( is_null( $nicename ) ){
			$nicename = $this->generator->userName;
		}
		return $nicename;
	}

	public function user_url( $url = null ) {
		if ( is_null( $url ) ){
			$url = $this->generator->url;
		}
		return $url;
	}

	public function user_email( $email = null ) {
		if ( is_null( $email ) ){
			$email = $this->generator->safeEmail;
		}
		return $email;
	}

	public function display_name( $display_name = null, $gender = array( 'male', 'female' ) ) {
		if ( is_null( $display_name ) ){
			$display_name = $this->generator->firstName( $this->generator->randomElements( $gender, 1 ) );
		}
		return $display_name;
	}

	public function nickname( $nickname = null ) {
		if ( is_null( $nickname ) ) {
			$nickname = $this->generator->userName;
		}
		return $nickname;
	}

	public function first_name( $first_name = null, $gender = array( 'male', 'female' ) ) {
		if ( is_null( $first_name ) ){
			$first_name = $this->generator->firstName( $this->generator->randomElements( $gender, 1 ) );
		}
		return $first_name;
	}

	public function last_name( $last_name = null ) {
		if ( is_null( $last_name ) ) {
			$last_name = $this->generator->lastName;
		}
		return $last_name;
	}

	public function description( $html = true, $args = array() ) {
		if ( true === $html ){
			$description = implode( "\n", $this->generator->html_elements( $args ) );
		} else {
			$description = implode( "\r\n\r\n", $this->generator->paragraphs( $this->generator->randomDigitNotNull() ) );
		}

		return $description;
	}

	public function user_registered( $min = 'now', $max = null ) {
		try {
			$min = new \Carbon\Carbon( $min );
		} catch (Exception $e) {
			return null;
		}

		if ( ! is_null( $max ) ){
			// Unfortunatelly there is not such solution to this problem, we need to try and catch with DateTime
			try {
				$max = new \Carbon\Carbon( $max );
			} catch (Exception $e) {
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