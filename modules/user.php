<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $provider = '\Faker\Provider\WP_User';

	public function save() {
		var_dump( $this->args );
		//return wp_insert_user( $this->args );
	}
}
