<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_Comment';

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$comment_id = wp_insert_comment( $params );

		if ( ! is_numeric( $comment_id ) ){
			return false;
		}

		foreach ( $metas as $key => $value ) {
			update_comment_meta( $comment_id, $key, $value );
		}

		return $comment_id;
	}
}
