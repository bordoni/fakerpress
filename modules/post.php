<?php
namespace FakerPress\Module;

class Post extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_Post';

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 2 );
	}

	public function do_save( $return_val, $module ){
		$post_id = wp_insert_post( $module->params );

		// Relate this post to FakerPress to make it possible to delete
		add_post_meta( $post_id, $module->flag, 1 );

		return $post_id;
	}
}
