<?php
namespace Faker\Provider;

class WP_User extends Base {

	public function user_login() {
		return $this->generator->userName;
	}
}