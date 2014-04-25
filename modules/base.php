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

	public $faked_args = array();

	/**
	 * Method that will add the Faker Provider and save the $intance to the $faker var
	 * @return object|WP_Error Should return an error or a Faker provider
	 */
	abstract public function __construct();

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
	 * Use this method to save the fake data to the database
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	abstract public function save();
}