<?php
namespace FakerPress\Module;

class User extends Base {

	public $provider = '\Faker\Provider\WP_User';

	public function save() {
		$user_id = wp_insert_user( $this->params );

		// Only set role if needed
		if ( ! is_null( $this->params['role'] ) ){
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $this->params['role'] );
		}

		// Relate this post to FakerPress to make it possible to delete
		add_user_meta( $user_id, $this->flag, 1 );

		return $user_id;
	}
}