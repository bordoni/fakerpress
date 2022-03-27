<?php

namespace FakerPress\Module;

use Faker\Generator;
use Faker\Provider\Base;

interface Interface_Module {

	/**
	 * Gets the slug of the module.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_slug(): string;

	/**
	 * Gets the key that stores the flag for an item created in FakerPress.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_flag(): string;

	/**
	 * Any module requires by default the publish_posts permissions.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_permission_required(): string;

	/**
	 * Amount of instances of the module that are allowed to be generated in one single request.
	 *
	 * @since 0.6.0
	 *
	 * @return int
	 */
	public function get_amount_allowed(): int;

	/**
	 * When dealing with any module there will be a hook method that will bind any WordPress hook needed.
	 *
	 * @since 0.6.0
	 *
	 * @return void
	 */
	public function hook(): void;

	/**
	 * Pulls an array of the class names for all the Dependencies of this module, normally a list of classes
	 * that will extend `Faker\Provider\Base`.
	 *
	 * @since 0.6.0
	 *
	 * @return array
	 */
	public function get_dependencies(): array;

	/**
	 * Which is the base provider that will be used to generate data for this provider.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_provider_class(): string;

	/**
	 * Gets the Faker Generator used on this module.
	 *
	 * @since 0.6.0
	 *
	 * @return Generator
	 */
	public function get_faker(): Generator;

	/**
	 * Method that sets the data for an individual generator method.
	 *
	 * @since TBD
	 *
	 * @param array|string $key Name of a particular method or a set of methods.
	 * @param ...mixed $data Any number of arguments.
	 *
	 * @return Interface_Module
	 */
	public function set( $key ): Interface_Module;

	/**
	 * Use this method to save the fake data to the database
	 *
	 * @since 0.6.0
	 *
	 * @return int|bool|\WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	public function save( bool $reset = true );

	/**
	 * Use this method to delete a given item from this Module.
	 *
	 * @since 0.6.0
	 *
	 * @param int|\WP_Post|string $item What are we deleting
	 *
	 * @return bool|\WP_Error
	 */
	public static function delete( $item );

	/**
	 * Use this method to fetch all items created from a given module.
	 *
	 * @since 0.6.0
	 *
	 * @param array $args Overwrite the default arguments used to fetch the items.
	 *
	 * @return bool|\WP_Error
	 */
	public static function fetch( array $args = [] ): array;

	/**
	 * Resets all the data on this module.
	 *
	 * @since 0.6.0
	 *
	 * @return self
	 */
	public function reset(): Interface_Module;

	/**
	 * Use this method to generate all the needed data.
	 *
	 * @since 0.6.0
	 *
	 * @return self
	 */
	public function generate(): Interface_Module;

	/**
	 * A method to make it easier to debug which variables will be actually saved.
	 *
	 * @param 0.6.0
	 *
	 * @return array
	 */
	public function get_values(): array;
}
