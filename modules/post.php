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
		$post_id = wp_insert_post( $this->params );

		return $post_id;
	}
}
