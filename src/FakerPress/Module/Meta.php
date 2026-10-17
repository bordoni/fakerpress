<?php
/**
 * Generation module for a single meta value on a post, term, comment or user.
 *
 * @package FakerPress
 */

namespace FakerPress\Module;

use FakerPress;

/**
 * Meta Module which will generate one Meta Value at a time
 *
 * @since 0.3.0
 */
class Meta extends Abstract_Module {

	/**
	 * Which Faker Dependencies this Module will need
	 *
	 * @since 0.3.0
	 *
	 * @var string[]
	 */
	protected $dependencies = [
		FakerPress\ThirdParty\Faker\Provider\Base::class,
		FakerPress\ThirdParty\Faker\Provider\Lorem::class,
		FakerPress\ThirdParty\Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
		FakerPress\ThirdParty\Faker\Provider\Internet::class,
		FakerPress\ThirdParty\Faker\Provider\UserAgent::class,
		FakerPress\ThirdParty\Faker\Provider\en_US\Company::class,
		FakerPress\ThirdParty\Faker\Provider\en_US\Address::class,
		FakerPress\ThirdParty\Faker\Provider\en_US\Person::class,
	];


	/**
	 * Which Faker Provider class we are using here
	 *
	 * @since 0.3.0
	 *
	 * @var string
	 */
	protected $provider_class = FakerPress\Provider\WP_Meta::class;

	/**
	 * Which type of object we are saving to
	 *
	 * @since 0.3.0
	 *
	 * @var string
	 */
	public $object_name = 'post';

	/**
	 * Which object we are saving to
	 *
	 * @since 0.3.0
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
	 * Configure which Object we will save Meta to.
	 *
	 * @since 0.3.0
	 *
	 * @param int    $id   ID of the object the meta belongs to.
	 * @param string $name Meta object type passed to `update_metadata()`: post, term, comment or user.
	 *
	 * @return self
	 */
	public function object( $id = 0, $name = 'post' ): Interface_Module {
		$this->object_id   = $id;
		$this->object_name = $name;

		return $this;
	}

	/**
	 * Sets which meta type, key and arguments the next `generate()` call will use.
	 *
	 * Clears the previous key and value first; an empty type or name leaves the module without a type,
	 * so nothing is generated. Also tells the WP_Meta provider which object the meta is for.
	 *
	 * @param string $type Meta type, matching a `meta_type_{$type}()` method on the WP_Meta provider.
	 * @param string $name Meta key to save the value under.
	 * @param array  $args Arguments for the meta type method, passed in order.
	 *
	 * @return Interface_Module
	 */
	public function with( string $type, string $name, $args ): Interface_Module {
		$this->data['meta_key']   = null;
		$this->data['meta_value'] = null; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Key in the module's own data array, not a query argument.

		if ( empty( $type ) ) {
			return $this;
		}

		if ( empty( $name ) ) {
			return $this;
		}

		$this->data['meta_type'] = $type;
		$this->data['meta_key']  = $name;
		$this->data['meta_args'] = array_values( $args );

		$faker = $this->get_faker();

		// Pass which object we are dealing with.
		$faker->set_meta_object( $this->object_name, $this->object_id );

		return $this;
	}

	/**
	 * Generates the meta value with the WP_Meta provider method for the configured type.
	 *
	 * Without a matching method, a single argument is used as the value as is. The result goes through
	 * the `fakerpress.module.meta.value` and `fakerpress.module.meta.{$meta_key}.value` filters.
	 *
	 * @param bool $force Whether to regenerate a value that already exists.
	 *
	 * @return Interface_Module
	 */
	public function generate( bool $force = false ): Interface_Module {
		// Only regenerate if there is no data, or we are forcing it.
		if ( isset( $this->data['meta_value'] ) && ! $force ) {
			return $this;
		}

		// Failed generation because we have no type.
		if ( ! isset( $this->data['meta_type'] ) ) {
			return $this;
		}

		$type = $this->data['meta_type'];
		$args = $this->data['meta_args'] ?? [];

		$faker = $this->get_faker();

		if ( is_callable( [ $faker, 'meta_type_' . $type ] ) ) {
			try {
				$this->data['meta_value'] = call_user_func_array( [ $faker, 'meta_type_' . $type ], $args ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Key in the module's own data array, not a query argument.
			} catch ( \Throwable $e ) {
				// Not enough configuration to generate this meta value; skip it without halting generation.
				return $this;
			}
		} elseif ( count( $args ) === 1 ) {
			$this->data['meta_value'] = reset( $args ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Key in the module's own data array, not a query argument.
		}

		/**
		 * Allow filtering for the value for a Meta
		 *
		 * @since 0.4.8
		 *
		 * @param mixed  $meta_value The Meta value that will be filtered
		 * @param string $meta_key   Which meta key we are currently filtering for
		 * @param string $meta_type  Which type of Meta we are dealing with
		 * @param self   $module     An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( 'fakerpress.module.meta.value', $this->data['meta_value'], $this->data['meta_key'], $type, $this ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Key in the module's own data array, not a query argument.

		/**
		 * Allow filtering for the Value of a specific meta value based on it's key
		 *
		 * @since 0.4.8
		 *
		 * @param mixed  $meta_value The Meta value that will be filtered
		 * @param string $meta_type  Which type of Meta we are dealing with
		 * @param self   $module     An instance of the Meta Module
		 */
		$this->data['meta_value'] = apply_filters( "fakerpress.module.meta.{$this->data['meta_key']}.value", $this->data['meta_value'], $type, $this ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Key in the module's own data array, not a query argument.

		return $this;
	}

	/**
	 * Saves the generated value with `update_metadata()` on the configured object.
	 *
	 * @param mixed           $response Response so far, ignored.
	 * @param array           $data     Module data with `meta_key` and `meta_value`.
	 * @param Abstract_Module $module   Module being saved.
	 *
	 * @return int|bool Meta ID for a new key, true on update, false when there is no key or value or the update failed.
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
	 * Not implemented for meta values.
	 *
	 * The body is empty, so calling it raises a TypeError for the missing array return.
	 *
	 * @param array $args Unused.
	 */
	public static function fetch( array $args = [] ): array {
		// TODO: Implement fetch() method.
	}

	/**
	 * Not implemented for meta values.
	 *
	 * @param mixed $item Unused.
	 *
	 * @return void
	 */
	public static function delete( $item ) {
		// TODO: Implement delete() method.
	}
}
