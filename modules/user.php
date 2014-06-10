<?php
namespace FakerPress\Module;

class User extends Base {

	public $provider = '\Faker\Provider\WP_User';

	public function save() {
		//return wp_insert_user( $this->args );
	}
}
