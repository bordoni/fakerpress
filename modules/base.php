<?php
namespace FakerPress\Module;

/**
 * Abstract of a Module Generator.
 * When creating a new module generator you should extend this one using `extends \FakerPress\Module\Base` in order to
 * be make sure we have the needed methods.
 */
abstract class Base {

	public $faker = null;

	public static $instance = null;

	public $params = array();

	public $meta = array();

	public $faked = array();

	public $dependencies = array();

	public $provider = null;

	final public function __construct() {
		$reflection = new \ReflectionClass( get_called_class() );

		$this->slug  = strtolower( $reflection->getShortName() );
		$this->faker = \Faker\Factory::create();

		$this->flag = apply_filters( 'fakerpress.modules_flag', 'fakerpress_flag' );

		$this->provider = (string) apply_filters( "fakerpress.module.{$this->slug}.provider", $this->provider );

		$this->dependencies = (array) apply_filters( "fakerpress.module.{$this->slug}.dependencies", $this->dependencies );

		// We need to merge the Provider to the Dependecies, so everything is loaded
		$providers = array_merge( $this->dependencies, (array) $this->provider );
		foreach ( $providers as $provider_class ) {
			$provider = new $provider_class( $this->faker );
			$this->faker->addProvider( $provider );
		}

		// Create a Reflection of the Provider class to discover all the methods that will fake an Argument
		$provider_reflection = new \ReflectionClass( $this->provider );
		$provider_methods    = $provider_reflection->getMethods();

		// Loop and verify which methods are will be faked on `generate`
		foreach ( $provider_methods as $method ) {
			if ( $provider_reflection->getName() !== $method->class ){
				continue;
			}
			$this->faked[] = $method->name;
		}

		// Execute a method that can be overwritten by the called class
		$this->init();

		$this->meta( $this->flag, 'randomElement', array( array( 1 ) ) );
	}

	/**
	 * Method that will be called from the construct which is a final function
	 * @return null
	 */
	abstract public function init();

	/**
	 * Use this method to save the fake data to the database
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	final public function save(){
		do_action( "fakerpress.module.{$this->slug}.pre_save", $this );

		$params = array();
		foreach ( $this->params as $param ) {
			$params[ $param->key ] = $param->value;
		}

		$metas = false;
		if ( is_array( $this->meta ) ){
			$metas = array();
			foreach ( $this->meta as $meta ) {
				$metas[ $meta->key ] = $meta->value;
			}
		}

		return apply_filters( "fakerpress.module.{$this->slug}.save", false, $params, $metas, $this );
	}

	protected function apply( $item ){
		return call_user_func_array( array( $this->faker, $item->generator ), ( isset( $item->arguments ) ? (array) $item->arguments : array() ) );
	}

	final public function meta( $key, $generator, $arguments = array() ){
		// If there is no meta just leave
		if ( ! is_array( $this->meta ) ){
			return $this;
		}

		$this->meta[ $key ] = (object) array(
			'key' => $key,
			'generator' => $generator,
			'arguments' => (array) $arguments,
		);

		return $this;
	}

	final public function param( $key, $arguments = array() ){
		$this->params[ $key ] = (object) array(
			'key' => $key,
			'generator' => $key,
			'arguments' => (array) $arguments,
		);

		return $this;
	}

	/**
	 * Use this method to generate all the needed data
	 * @return array An array of the data generated
	 */
	public function generate( $args = array() ) {
		foreach ( $this->faked as $name ) {
			if ( ! isset( $this->params[ $name ] ) ){
				$this->params[ $name ] = (object) array(
					'key' => $name,
					'generator' => $name,
					'arguments' => array(),
				);
			}
			$this->params[ $name ]->value = $this->apply( $this->params[ $name ] );
		}

		if ( is_array( $this->meta ) ){
			foreach ( $this->meta as $meta ) {
				$this->meta[ $meta->key ]->value = $this->apply( $meta );
			}
		}

		return $this;
	}
}