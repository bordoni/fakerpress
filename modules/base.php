<?php
namespace FakerPress\Module;

/**
 * Abstract of a Module Generator.
 * When creating a new module generator you should extend this one using `extends \FakerPress\Module\Base` in order to
 * be make sure we have the needed methods.
 */
abstract class Base {

	public $faker = null;

	/**
	 * Method that will add the Faker Provider and save the $intance to the $faker var
	 * @return object|WP_Error Should return an error or a Faker provider
	 */
	abstract public function __construct( $arguments );

	final public function __get( $name ){
		return $this->faker->$name;
	}

	/**
	 * Use this method to save the fake data to the database
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	abstract public function save( $faker );
}