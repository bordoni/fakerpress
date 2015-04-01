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
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$post_id = wp_insert_post( $params );

		if ( ! is_numeric( $post_id ) ){
			return false;
		}

		foreach ( $metas as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return $post_id;
	}
}
