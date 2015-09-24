<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
use FakerPress\Plugin;


class Meta extends Base {

	public $dependencies = array(
		'\Faker\Provider\Base',
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
		'\Faker\Provider\Internet',
		'\Faker\Provider\UserAgent',
		'\Faker\Provider\en_US\Company',
		'\Faker\Provider\en_US\Address',
		'\Faker\Provider\en_US\Person',
	);

	public $provider = '\Faker\Provider\WP_Meta';

	public $page = false;

	// Default Object is Posts
	public $object_name = 'post';
	public $object_id = 0;

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 3 );
	}

	public function reset() {
		parent::reset();

		$this->object_id = 0;
	}

	public function object( $id = 0, $name = 'post' ) {
		$this->object_id = $id;
		$this->object_name = $name;

		return $this;
	}

	public function generate() {
		// Allow a bunch of params
		$arguments = func_get_args();

		// Remove $key and $name
		$type = array_shift( $arguments );
		$name = array_shift( $arguments );
		$args = array_shift( $arguments );

		$this->data['meta_key'] = null;
		$this->data['meta_value'] = null;

		if ( empty( $type ) ){
			return $this;
		}

		if ( empty( $name ) ){
			return $this;
		}

		$this->data['meta_key'] = $name;

		unset( $args['name'], $args['type'] );

		if ( is_callable( array( $this->faker, 'meta_type_' . $type ) ) ){
			$this->data['meta_value'] = call_user_func_array( array( $this->faker, 'meta_type_' . $type ), $args );
		} else {
			$this->data['meta_value'] = reset( $args );
		}

		return $this;
	}

	public function do_save( $return_val, $data, $module ) {
		$status = false;

		if ( ! isset( $data['meta_value'] ) ){
			return false;
		}

		if ( empty( $data['meta_key'] ) ){
			return false;
		}

		if ( ! is_null( $data['meta_value'] ) ){
			$status = update_metadata( $this->object_name, $this->object_id, $data['meta_key'], $data['meta_value'] );
		}
		return $status;
	}
}
