<?php
namespace FakerPress\Module;

class Post extends Base {

	public $faked = array(
		'post_title',
		'post_content',
		'post_type',
		'user_id',
		'post_date',
		'post_status',
		'ping_status',
	);

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\Html',
		'\Faker\Provider\WP_Post',
	);

	public function init() {

	}

	public function save() {
		return wp_insert_post( $this->args );
	}
}
