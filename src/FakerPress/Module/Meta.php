<?php

namespace FakerPress\Module;

use Faker;
use FakerPress;

/**
 * Meta Module which will generate one Meta Value at a time
 *
 * @since  0.3.0
 *
 */
class Meta extends Abstract_Module {

	/**
	 * Which Faker Dependencies this Module will need
	 *
	 * @since  0.3.0
	 *
	 * @var string[]
	 */
	protected $dependencies = [
		Faker\Provider\Base::class,
		Faker\Provider\Lorem::class,
		Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
		Faker\Provider\Internet::class,
		Faker\Provider\UserAgent::class,
		Faker\Provider\en_US\Company::class,
		Faker\Provider\en_US\Address::class,
		Faker\Provider\en_US\Person::class,
	];


	/**
	 * Which Faker Provider class we are using here
	 *
	 * @since  0.3.0
	 *
	 * @var string
	 */
	protected $provider_class = FakerPress\Provider\WP_Meta::class;

	/**
	 * Which type of object we are saving to
	 *
	 * @since  0.3.0
	 *
	 * @var string
	 */
	public $object_name = 'post';

	/**
	 * Which object we are saving to
	 *
	 * @since  0.3.0
	 *
	 * @var integer
	 */
	public $object_id = 0;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'meta';
	}

	/**
	 * @inheritDoc
	 */
	public function hook(): void {
	}

	/**
	 * @inheritDoc
	 */
	public function reset(): Interface_Module {
		parent::reset();

		$this->object_id = 0;

		return $this;
	}

	/**
	 * Configure which Object we will save Meta to
	 *
	 * @since  0.3.0
	 *
	 * @return self
	 */
	public function object( $id = 0, $name = 'post' ): Interface_Module {
		$this->object_id   = $id;
		$this->object_name = $name;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function generate(): Interface_Module {
		// Allow a bunch of params
		$arguments = func_get_args();

		// Remove $key and $name
		$type = array_shift( $arguments );
		$name = array_shift( $arguments );
		$args = array_shift( $arguments );

		$this->data['meta_key']   = null;
		$this->data['meta_value'] = null;

		if ( empty( $type ) ) {
			return $this;
		}

		if ( empty( $name ) ) {
			return $this;
		}

		$this->data['meta_key'] = $name;

		unset( $args['name'], $args['type'] );

		$faker = $this->get_faker();

		// Pass which object we are dealing with
		$faker->set_meta_object( $this->object_name, $this->object_id );

		if ( is_callable( [ $faker, 'meta_type_' . $type ] ) ) {
			$this->data['meta_value'] = call_user_func_array( [ $faker, 'meta_type_' . $type ], array_values( $args ) );
		} else {
			$this->data['meta_value'] = reset( $args );
		}

		/**
		 * Allow filtering for the value for a Meta
		 *
		 * @since  0.4.8
		 *
		 * @param mixed  $meta_value The Meta value that will be filtered
		 * @param string $meta_key   Which meta key we are currently filtering for
		 * @param string $meta_type  Which type of Meta we are dealing with
		 * @param self   $module     An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( "fakerpress.module.meta.value", $this->data['meta_value'], $this->data['meta_key'], $type, $this );

		/**
		 * Allow filtering for the Value of a specific meta value based on it's key
		 *
		 * @since  0.4.8
		 *
		 * @param mixed  $meta_value The Meta value that will be filtered
		 * @param string $meta_type  Which type of Meta we are dealing with
		 * @param self   $module     An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( "fakerpress.module.meta.{$this->data['meta_key']}.value", $this->data['meta_value'], $type, $this );

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		if ( ! isset( $data['meta_value'] ) ) {
			return false;
		}

		if ( empty( $data['meta_key'] ) ) {
			return false;
		}

		return update_metadata( $this->object_name, $this->object_id, $data['meta_key'], $data['meta_value'] );
	}

	/**
	 * @inheritDoc
	 */
	public static function fetch( array $args = [] ): array {
		// TODO: Implement fetch() method.
	}

	/**
	 * @inheritDoc
	 */
	public static function delete( $item ) {
		// TODO: Implement delete() method.
	}
}
