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

	public $args = array();

	public $params = array();

	public $settings = array();

	public $faked = array();

	public $dependencies = array();

	public $provider = null;

	final public function __construct( $settings = array() ) {
		$reflection = new \ReflectionClass( get_called_class() );

		$this->slug  = strtolower( $reflection->getShortName() );
		$this->faker = \Faker\Factory::create();

		$this->flag = apply_filters( 'fakerpress.modules_flag', 'fakerpress_flag' );

		$this->settings = (array) apply_filters( "fakerpress.module.{$this->slug}.settings", wp_parse_args( $this->settings, $settings ) );

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
	}

	final public function __get( $name ){
		return $this->faker->$name;
	}

	final public function __call( $name, $arguments ){
		return call_user_func_array( array( $this->faker, $name ), $arguments );
	}

	final public static function __callStatic( $name, $arguments ){
		$_class = get_called_class();
		null === self::$instance and self::$instance = new $_class();
		return call_user_func_array( array( self::$instance->faker, $name ), $arguments );
	}

	/**
	 * Method that will be called from the construct which is a final function
	 * @return null
	 */
	public function init() {
		return;
	}

	/**
	 * Use this method to save the fake data to the database
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	abstract public function save();

	/**
	 * Use this method to generate all the needed data
	 * @return array An array of the data generated
	 */
	public function generate( $args = array() ) {
		$this->args = apply_filters( "fakerpress.module.{$this->slug}.args", wp_parse_args( $this->args, $args ) );

		foreach ( $this->faked as $name ) {
			$this->params[ $name ] = call_user_func_array( array( $this->faker, $name ), ( isset( $this->args[ $name ] ) ? (array) $this->args[ $name ] : array() ) );
		}

		return $this;
	}

}