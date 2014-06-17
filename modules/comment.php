<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $provider = '\Faker\Provider\WP_Comment';

	public function save() {
		// Here you should use the `$this->args['param_name']`
		//var_dump( $this->args );
		return wp_insert_comment( $this->params );
	}
}
