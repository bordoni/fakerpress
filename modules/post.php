<?php
namespace FakerPress\Module;

class Post extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\Html',
	);

	public $provider = '\Faker\Provider\WP_Post';

	public function save() {
		return wp_insert_post( $this->params );
	}
}
