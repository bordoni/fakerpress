<?php

namespace FakerPress\Module;

use Faker\Generator;
use Faker\Provider\Base;
use function FakerPress\is_truthy;

/**
 * Class Abstract_Module.
 *
 * @since   0.6.0
 *
 * @package FakerPress\Module
 */
abstract class Abstract_Module implements Interface_Module {

	/**
	 * Stores the Faker generator.
	 *
	 * @since 0.6.0
	 *
	 * @var Generator
	 */
	protected $faker;

	/**
	 * Stores the default set of dependencies.
	 *
	 * @since 0.6.0
	 *
	 * @var string[]
	 */
	protected $dependencies = [];

	/**
	 * Stores the default provider.
	 *
	 * @since 0.6.0
	 *
	 * @var string
	 */
	protected $provider_class;

	/**
	 * Stores the generated data.
	 *
	 * @since 0.6.0
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * @inheritDoc
	 */
	abstract public static function get_slug(): string;

	/**
	 * @inheritDoc
	 */
	public static function get_permission_required(): string {
		return 'publish_posts';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_flag(): string {
		$slug = static::get_slug();

		// Default flag value.
		$flag = 'fakerpress_flag';

		/**
		 * String holding the flag used to mark items from this module as FakerPress created.
		 *
		 * @since 0.6.0
		 *
		 * @param string $flag Value for the flag.
		 * @param string $module_class Which module class we are using.
		 */
		$flag = apply_filters( "fakerpress.module.flag", $flag, self::class );

		/**
		 * String holding the flag used to mark items from this module as FakerPress created.
		 *
		 * @since 0.6.0
		 *
		 * @param string $flag Value for the flag.
		 * @param string $module_class Which module class we are using.
		 */
		$flag = apply_filters( "fakerpress.module.{$slug}.flag", $flag, self::class );

		return $flag;
	}

	/**
	 * @inheritDoc
	 */
	public function get_amount_allowed(): int {
		$slug = static::get_slug();

		return apply_filters( "fakerpress.module.{$slug}.amount_allowed", 15, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_faker(): Generator {
		if ( ! $this->faker ) {
			$this->faker = \Faker\Factory::create();

			// We need to merge the Provider to the Dependencies, so everything is loaded.
			$providers = array_merge( $this->get_dependencies(), (array) $this->get_provider_class() );
			$providers = array_unique( $providers );
			$providers = array_filter( $providers );

			foreach ( $providers as $provider_class ) {
				$this->faker->addProvider( new $provider_class( $this->faker ) );
			}
		}

		return $this->faker;
	}

	/**
	 * Filters the dependencies classes for this module.
	 *
	 * @since 0.6.0
	 *
	 * @param array $dependencies Which default dependencies classes will be used.
	 *
	 * @return array
	 */
	protected function filter_dependencies( array $dependencies ): array {
		$slug = static::get_slug();

		/**
		 * Allows the modification of the default provider class used.
		 *
		 * @since 0.6.0
		 *
		 * @param array $dependencies Which default dependencies classes will be used.
		 * @param self  $this         Module we are currently getting the provider for.
		 */
		$dependencies = apply_filters( 'fakerpress.module.dependencies', $dependencies, $this );

		/**
		 * Allows the modification of the default reset data set for a specific module.
		 *
		 * @since 0.6.0
		 *
		 * @param array $dependencies Which default dependencies classes will be used.
		 * @param self  $this         Module we are currently getting the provider for.
		 */
		return apply_filters( "fakerpress.module.{$slug}.dependencies", $dependencies, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_dependencies(): array {
		return $this->filter_dependencies( $this->dependencies );
	}

	/**
	 * Filters the provider class for this module.
	 *
	 * @since 0.6.0
	 *
	 * @param string $provider Which default provider class used.
	 *
	 * @return string
	 */
	protected function filter_provider_class( string $provider ): string {
		$slug = static::get_slug();

		/**
		 * Allows the modification of the default provider class used.
		 *
		 * @since 0.6.0
		 *
		 * @param string $provider Which default provider class used.
		 * @param self   $this     Module we are currently getting the provider for.
		 */
		$provider = apply_filters( 'fakerpress.module.provider_class', $provider, $this );

		/**
		 * Allows the modification of the default reset data set for a specific module.
		 *
		 * @since 0.6.0
		 *
		 * @param string $provider Which default provider class used.
		 * @param self   $this     Module we are currently getting the provider for.
		 */
		return apply_filters( "fakerpress.module.{$slug}.provider_class", $provider, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_provider_class(): string {
		return $this->filter_provider_class( $this->provider_class );
	}

	/**
	 * @inheritDoc
	 */
	abstract public static function delete( $item );

	/**
	 * @inheritDoc
	 */
	abstract public static function fetch( array $args = [] ): array;

	/**
	 * @inheritDoc
	 */
	final public function set( $key ): Interface_Module {
		if ( ! is_string( $key ) && ! is_array( $key ) ) {
			return $this;
		}

		// Allow a bunch of params
		$arguments = func_get_args();

		/**
		 * This allows the following behavior, both will have the same arguments.
		 *
		 * $module->set( [ 'post_title', 'post_content' ], false, true );
		 */
		if ( is_array( $key ) ) {
			// Remove any non string keys.
			$keys = array_filter( $key, 'is_string' );

			foreach ( $keys as $key ) {
				// Set the key.
				$arguments[0] = $key;

				// Re-call Set with the string key instead of array.
				$this->set( ...$arguments );
			}

			return $this;
		}

		// Remove $key.
		array_shift( $arguments );

		$this->data[ $key ] = (object) [
			'key'       => $key,
			'generator' => $key,
			'arguments' => (array) $arguments,
		];

		return $this;
	}

	/**
	 * Modules extending this Abstract should use this module to make sure their data is properly saved as the actual
	 * save method is final.
	 *
	 * @since TBD
	 *
	 * @param mixed           $response
	 * @param array           $data
	 * @param Abstract_Module $module
	 *
	 * @return mixed
	 */
	abstract protected function filter_save_response( $response, array $data, Abstract_Module $module );

	/**
	 * @inheritDoc
	 */
	final public function save( bool $reset = true ) {
		$slug = static::get_slug();

		/**
		 * Allows the hooking into before the saving of a module.
		 *
		 * @since 0.6.0
		 *
		 * @param bool $reset Will reset the data in this module after save.
		 * @param self $this  Module we are currently saving.
		 */
		do_action( 'fakerpress.module.before_save', $reset, $this );

		/**
		 * Allows the hooking into before the saving of a module specifically.
		 *
		 * @since 0.6.0
		 *
		 * @param bool $reset Will reset the data in this module after save.
		 * @param self $this  Module we are currently saving.
		 */
		do_action( "fakerpress.module.{$slug}.before_save", $reset, $this );

		$data = [];
		foreach ( $this->data as $key => $item ) {
			if ( is_object( $item ) ) {
				$data[ $item->key ] = $item->value;
			} else {
				$data[ $key ] = $item;
			}
		}

		/**
		 * Allows us to prevent `_encloseme` and `_pingme` meta when generating Posts
		 *
		 * @since  0.4.9
		 *
		 * @param bool $prevent_enclose_ping_meta
		 */
		$prevent_enclose_ping_meta = is_truthy( apply_filters( 'fakerpress.module.generate.prevent_enclose_ping_meta', true ) );

		// This will prevent us having `_encloseme` and `_pingme`.
		if ( $prevent_enclose_ping_meta && ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		$response = $this->filter_save_response( false, $data, $this );

		/**
		 * Allows the modification of the response for modules.
		 *
		 * @since 0.6.0
		 *
		 * @param mixed $response The actual response when saving.
		 * @param array $data     Data generated by this module.
		 * @param bool  $reset    Will reset the data in this module after save.
		 * @param self  $this     Module we are currently saving.
		 */
		$response = apply_filters( 'fakerpress.module.save', $response, $data, $reset, $this );

		/**
		 * Allows the modification of the response for a specific module.
		 *
		 * @since 0.6.0
		 *
		 * @param mixed $response The actual response when saving.
		 * @param array $data     Data generated by this module.
		 * @param bool  $reset    Will reset the data in this module after save.
		 * @param self  $this     Module we are currently saving.
		 */
		$response = apply_filters( "fakerpress.module.{$slug}.save", $response, $data, $reset, $this );

		if ( $reset ) {
			$this->reset();
		}

		/**
		 * Allows the hooking into before the saving of a module.
		 *
		 * @since 0.6.0
		 *
		 * @param mixed $response The actual response when saving.
		 * @param bool  $reset    Will reset the data in this module after save.
		 * @param self  $this     Module we are currently saving.
		 */
		do_action( 'fakerpress.module.after_save', $response, $reset, $this );

		/**
		 * Allows the hooking into before the saving of a module specifically.
		 *
		 * @since 0.6.0
		 *
		 * @param mixed $response The actual response when saving.
		 * @param bool  $reset    Will reset the data in this module after save.
		 * @param self  $this     Module we are currently saving.
		 */
		do_action( "fakerpress.module.{$slug}.after_save", $response, $reset, $this );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function reset(): Interface_Module {
		$slug = static::get_slug();

		/**
		 * Allows the modification of the default reset data set.
		 *
		 * @since 0.6.0
		 *
		 * @param array $data Which data will be applied when reset.
		 * @param self  $this Module we are currently resetting for.
		 */
		$data = apply_filters( 'fakerpress.module.reset_data', [], $this );

		/**
		 * Allows the modification of the default reset data set for a specific module.
		 *
		 * @since 0.6.0
		 *
		 * @param array $data Which data will be applied when reset.
		 * @param self  $this Module we are currently resetting for.
		 */
		$data = apply_filters( "fakerpress.module.{$slug}.reset_data", $data, $this );

		$this->data = $data;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function generate(): Interface_Module {
		foreach ( $this->data as $name => $item ) {
			$this->data[ $name ]->value = $this->apply( $item );
		}

		return $this;
	}

	/**
	 * Applies a set of arguments to the generator method of the provider.
	 *
	 * @since 0.6.0
	 *
	 * @param $item
	 *
	 * @return false|mixed
	 */
	protected function apply( $item ) {
		// Where there is no generator just return the first argument.
		if ( ! isset( $item->generator ) ) {
			return reset( $item->arguments );
		}

		return call_user_func_array( [ $this->get_faker(), $item->generator ], ( isset( $item->arguments ) ? (array) $item->arguments : [] ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_values(): array {
		$values = [];

		foreach ( $this->data as $name => $item ) {
			$values[ $name ] = $this->data[ $name ]->value;
		}

		return $values;
	}
}
