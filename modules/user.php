<?php
namespace FakerPress\Module;

class User extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_User';

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$user_id = wp_insert_user( $params );

		if ( ! is_numeric( $user_id ) ){
			return false;
		}

		// Only set role if needed
		if ( ! is_null( $params['role'] ) ){
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $params['role'] );
		}

		foreach ( $metas as $key => $value ) {
			update_user_meta( $user_id, $key, $value );
		}

		return $user_id;
	}
}