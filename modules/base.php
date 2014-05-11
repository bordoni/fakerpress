<?php
namespace FakerPress\Module;

// Inluding needed providers
include_once \Fakerpress\Plugin::path( 'providers/html.php' );
include_once \Fakerpress\Plugin::path( 'providers/post.php' );

/**
 * Abstract of a Module Generator.
 * When creating a new module generator you should extend this one using `extends \FakerPress\Module\Base` in order to
 * be make sure we have the needed methods.
 */
abstract class Base {

	public $faker = null;

	public static $instance = null;

	public $args = array();

	public $settings = array();

	public $faked = array();

	public $dependencies = array();

	final public function __construct( $settings = array() ) {
		$reflection = new \ReflectionClass( get_called_class() );

		$this->slug = strtolower( $reflection->getShortName() );
		$this->faker = \Faker\Factory::create();

		$this->settings = apply_filters( "fakerpress.module.{$this->slug}.settings", wp_parse_args( $this->settings, $settings ) );

		$this->load_dependencies();
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
	abstract public function init();

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
			$this->args[ $name ] = call_user_func_array( array( $this->faker, $name ), ( isset( $this->args[ $name ] ) ? array( $this->args[ $name ] ) : array() ) );
		}

		return $this->args;
	}

	/**
	 * This method will load all the needed Faker dependencies for this Module
	 * @return null
	 */
	public function load_dependencies() {
		$this->dependencies = apply_filters( "fakerpress.module.{$this->slug}.dependencies", $this->dependencies );

		foreach ( $this->dependencies as $provider_name ) {
			$provider = new $provider_name( $this->faker );
			$this->faker->addProvider( $provider );
		}
	}
}