<?php
/**
 * Contract every FakerPress generation module implements.
 *
 * @package FakerPress
 */

namespace FakerPress\Module;

use FakerPress\ThirdParty\Faker\Generator;

/**
 * Describes a module that generates, saves, lists and deletes one kind of fake WordPress data.
 */
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
	 * Any arguments after `$key` are stored and passed to the generator method when the data is generated.
	 *
	 * @since 0.6.4
	 *
	 * @param array|string $key Name of a particular method or a set of methods.
	 *
	 * @return Interface_Module
	 */
	public function set( $key ): Interface_Module;

	/**
	 * Use this method to save the fake data to the database.
	 *
	 * @since 0.6.0
	 *
	 * @param bool $reset Whether to reset the module data after saving.
	 *
	 * @return int|bool|\WP_Error Should return an error, or the $wpdb->insert_id or bool for the state.
	 */
	public function save( bool $reset = true );

	/**
	 * Use this method to delete a given item from this Module.
	 *
	 * @since 0.6.0
	 *
	 * @param int|\WP_Post|string $item What are we deleting.
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
	 * @return array IDs of the items this module created.
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
	 * @since 0.6.2 Added the $force parameter.
	 *
	 * @param bool $force Whether to force the regeneration of the data.
	 *
	 * @return self
	 */
	public function generate( bool $force = false ): Interface_Module;

	/**
	 * A method to make it easier to debug which variables will be actually saved.
	 *
	 * @since 0.6.0
	 *
	 * @return array
	 */
	public function get_values(): array;

	/**
	 * A method to get the data for a given key.
	 *
	 * @param string $key Name of the generator method the data was set for.
	 *
	 * @return object|null The stored data object (key, generator, arguments and value), or null when not set.
	 */
	public function get( string $key ): ?object;

	/**
	 * A method to get the value for a given key.
	 *
	 * @param string $key Name of the generator method the data was set for.
	 *
	 * @return mixed
	 */
	public function get_value( string $key );

	/**
	 * Determines if a value is set for a given key.
	 *
	 * @param string $key Name of the generator method the data was set for.
	 *
	 * @return bool
	 */
	public function has_value( string $key ): bool;
}
