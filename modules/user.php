<?php
namespace FakerPress\Module;

class User extends Base {

	public $provider = '\Faker\Provider\WP_User';

	public function save() {
		$user_id = wp_insert_user( $this->params );
		if ( ! is_null( $this->params['role'] ) ){
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $this->params['role'] );
		}
		return $user_id;
	}
}