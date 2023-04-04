<?php

namespace FakerPress\Fields;

use lucatume\DI52\ServiceProvider;
use function FakerPress\get;
use function FakerPress\make;

/**
 * Class Factory
 *
 * @since   TBD
 *
 * @package FakerPress\Fields
 */
class Factory extends ServiceProvider {
	protected $types = [];

	/**
	 * Register all the Admin Views as Singletons and initializes them.
	 *
	 * @since 0.6.0
	 */
	public function register() {
		// Register the provider as a singleton.
		$this->container->singleton( static::class, $this );

		// When fetching all items it will initialize.
		$this->get_all_types();
	}

	public function make( $fields ) {
		if ( $fields instanceof Field_Abstract ) {
			return ! $fields->is_init() ? $fields->init() : $fields;
		}

		if ( ! is_array( $fields ) ) {
			return new \WP_Error( 'fakerpress-fields-factory-invalid-config', null, [ 'fields' => $fields ] );
		}

		if ( $this->is_valid_field_config( $fields ) ) {
			$field_class = $this->get_field_class_for_type( (string) get( $fields, 'type' ) );
			/** @var Field_Abstract $field */
			$field = make( $field_class );

			// Now using the class create a new instance and init it with the params.
			return $field->init( $fields );
		}

		return array_map( [ $this, 'make' ], $fields );
	}

	public function get_all_types(): array {
		$default_types = [
			Raw_Field::class,
			Fieldset_Field::class,
		];

		/**
		 * Allows the filtering of the FakerPress available modules.
		 *
		 * @since 0.6.0
		 *
		 * @param string[] $views Which modules are available.
		 */
		$types = apply_filters( 'fakerpress.fields', $default_types );

		foreach ( $types as $field_type ) {
			// Skips all non Field Abstract items.
			if ( ! is_subclass_of( $field_type, Field_Abstract::class ) ) {
				continue;
			}

			$this->container->bind( $field_type, $field_type );

			$this->types[ $field_type::get_slug() ] = $field_type;
		}

		return $this->types;
	}

	public function get_field_class_for_type( string $type ): string {
		return get( $this->get_all_types(), $type );
	}

	public function is_valid_field_config( array $field ): bool {
		return (bool) $this->get_field_class_for_type( (string) get( $field, 'type' ) );
	}
}
