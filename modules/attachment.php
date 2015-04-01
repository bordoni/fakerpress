<?php
namespace FakerPress\Module;

class Attachment extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_Attachment';

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ) );
	}

	public function do_save() {
		$post_id = wp_insert_post( $this->params );

		// Relate this post to FakerPress to make it possible to delete
		add_post_meta( $post_id, $this->flag, 1 );

		return $post_id;
	}
}
