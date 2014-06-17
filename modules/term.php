<?php
namespace FakerPress\Module;

class Term extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
	);

	public $provider = '\Faker\Provider\WP_Term';

	public function save() {
		$args = array(
			'description' => $this->params['description'],
			'parent' => $this->params['parent_term'],
		);
		return wp_insert_term( $this->params['name'], $this->params['taxonomy'], $args );
	}
}
