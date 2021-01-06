<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
use FakerPress\Plugin;
use Faker;
use FakerPress;

/**
 * Meta Module which will generate one Meta Value at a time
 *
 * @since  0.3.0
 *
 */
class Meta extends Base {

	/**
	 * Which Faker Dependencies this Module will need
	 *
	 * @since  0.3.0
	 *
	 * @var array
	 */
	public $dependencies = [
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
	public $provider = FakerPress\Provider\WP_Meta::class;

	/**
	 * Wether or not FakerPress will generate a page for this
	 *
	 * @since  0.3.0
	 *
	 * @var boolean
	 */
	public $page = false;

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
	 * Initalize and Add the correct hooks into the Meta Module
	 *
	 * @since  0.3.0
	 *
	 * @return void
	 */
	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", [ $this, 'do_save' ], 10, 3 );
	}

	/**
	 * Resets which original object that we will save the meta to
	 *
	 * @since  0.3.0
	 *
	 * @return self
	 */
	public function reset() {
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
	public function object( $id = 0, $name = 'post' ) {
		$this->object_id = $id;
		$this->object_name = $name;

		return $this;
	}

	/**
	 * Generate the meta based on the Params given
	 *
	 * @since  0.3.0
	 *
	 * @param  string $type Type of Meta we are dealing with
	 * @param  string $name Name of the Meta, used to save
	 * @param  array  $args Arguments used to setup the Meta
	 *
	 * @return self
	 */
	public function generate() {
		// Allow a bunch of params
		$arguments = func_get_args();

		// Remove $key and $name
		$type = array_shift( $arguments );
		$name = array_shift( $arguments );
		$args = array_shift( $arguments );

		$this->data['meta_key'] = null;
		$this->data['meta_value'] = null;

		if ( empty( $type ) ) {
			return $this;
		}

		if ( empty( $name ) ) {
			return $this;
		}

		$this->data['meta_key'] = $name;

		unset( $args['name'], $args['type'] );

		// Pass which object we are dealing with
		$this->faker->set_meta_object( $this->object_name, $this->object_id );

		if ( is_callable( [ $this->faker, 'meta_type_' . $type ] ) ) {
			$this->data['meta_value'] = call_user_func_array( [ $this->faker, 'meta_type_' . $type ], array_values( $args ) );
		} else {
			$this->data['meta_value'] = reset( $args );
		}

		/**
		 * Allow filtering for the value for a Meta
		 *
		 * @since  0.4.8
		 *
		 * @param  mixed  $meta_value  The Meta value that will be filtered
		 * @param  string $meta_key    Which meta key we are currently filtering for
		 * @param  string $meta_type   Which type of Meta we are dealing with
		 * @param  self   $module      An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( "fakerpress.module.meta.value", $this->data['meta_value'], $this->data['meta_key'], $type, $this );

		/**
		 * Allow filtering for the Value of a specific meta value based on it's key
		 *
		 * @since  0.4.8
		 *
		 * @param  mixed  $meta_value  The Meta value that will be filtered
		 * @param  string $meta_type   Which type of Meta we are dealing with
		 * @param  self   $module      An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( "fakerpress.module.meta.{$this->data['meta_key']}.value", $this->data['meta_value'], $type, $this );

		return $this;
	}

	/**
	 * Actually save the meta value into the Database
	 *
	 * @since  0.3.0
	 *
	 * @param  string  $return_val  Unsed variable that comes from the hook
	 * @param  string  $data        Data generated, meta_key and meta_value
	 * @param  array   $module      Arguments used to setup the Meta
	 *
	 * @return self
	 */
	public function do_save( $return_val, $data, $module ) {
		$status = false;

		if ( ! isset( $data['meta_value'] ) ) {
			return false;
		}

		if ( empty( $data['meta_key'] ) ) {
			return false;
		}

		if ( ! is_null( $data['meta_value'] ) ) {
			$status = update_metadata( $this->object_name, $this->object_id, $data['meta_key'], $data['meta_value'] );
		}
		return $status;
	}
}
