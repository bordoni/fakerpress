<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Filter;
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

	public $meta = false;

	public $faked = array(
		'meta_key',
		'meta_value',
	);

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function object( $id = 0, $name = 'post' ){
		$this->object_id = $id;
		$this->object_name = $name;

		return $this;
	}

	public function build( $type, $name, $args = array() ){
		$this->params['meta_key'] = null;
		$this->params['meta_value'] = null;

		if ( empty( $type ) ){
			return $this;
		}

		if ( empty( $name ) ){
			return $this;
		}

		$this->params['meta_key'] = $name;

		unset( $args['name'], $args['type'] );

		if ( is_callable( array( $this->faker, 'meta_type_' . $type ) ) ){
			$this->params['meta_value'] = call_user_func_array( array( $this->faker, 'meta_type_' . $type ), $args );
		} else {
			$this->params['meta_value'] = reset( $args );
		}

		return $this;
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$status = false;

		if ( ! isset( $params['meta_value'] ) ){
			return false;
		}

		if ( empty( $params['meta_key'] ) ){
			return false;
		}

		if ( ! is_null( $params['meta_value'] ) ){
			$status = update_metadata( $this->object_name, $this->object_id, $params['meta_key'], $params['meta_value'] );
		}
		return $status;
	}
}
