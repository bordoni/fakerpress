<?php

namespace FakerPress\Fields;

use FakerPress\Template;
use FakerPress\Plugin;
use function FakerPress\get;
use function FakerPress\make;

/**
 * Abstract for Fields.
 *
 * @since  0.5.1
 */
abstract class Field_Abstract implements Field_Interface {

	/**
	 * HTML ID for this field.
	 *
	 * @since  0.5.1
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Holds this field priority for ordering.
	 *
	 * @since  0.5.1
	 *
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Determines if this field was initialized.
	 *
	 * @since  0.6.0
	 *
	 * @var bool
	 */
	protected $is_init = false;

	/**
	 * Hold the configuration for this field instance.
	 *
	 * @since  0.5.1
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Hold a Field that is the parent of this one.
	 *
	 * @since  0.5.1
	 *
	 * @var Field_Interface
	 */
	protected $parent;

	/**
	 * Hold the children of this field, which should implement Field_Interface.
	 *
	 * @since  0.5.1
	 *
	 * @var array [...Field_Interface]
	 */
	protected $children = [];

	/**
	 * Hold the template instance that we use to render the fields.
	 *
	 * @since  0.5.1
	 *
	 * @var Template
	 */
	protected $template;


	/**
	 * {@inheritDoc}
	 */
	public function set_id( $value ) {
		$this->id = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html_id( $suffix = null ) {
		$ids    = [];
		$parent = $this;

		// Fetch the ID of all parents
		while ( $parent = $parent->get_parent() ) {
			$ids[] = $parent->get_id();
		}

		$ids[] = $this->get_id();

		$plugin = Plugin::$slug;
		$type   = static::get_slug();

		$id = "{$plugin}-field-{$type}-" . implode( '-', (array) $ids );

		if ( $suffix ) {
			$id .= '-' . $suffix;
		}

		return sanitize_html_class( $id );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug(): string {
		return static::$slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_setting( $index, $default = null ) {
		return get( $this->get_settings(), $index, $default );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = new Template();
			$this->template
				->set_template_origin( make( Plugin::class ) )
				->set_template_folder( 'src/templates/fields' )
				->set_template_context_extract( true );
		}

		return $this->template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_priority( int $value ): void {
		$this->priority = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html( $format = 'string', $echo = true ) {
		// Globally set in the template vars this instance of field.
		$this->get_template()->set( 'field', $this, false );

		return $this->get_template()->render( static::get_slug(), [ 'field' => $this ], $echo );
	}

	/**
	 * Generates a random ID for when an ID was not supplied.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function generate_random_id(): string {
		return sanitize_html_class( wp_generate_uuid4() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function init( array $args = [] ) {
		$this->set_id( get( $args, 'id', $this->generate_random_id() ) );
		$this->set_priority( get( $args, 'priority', $this->priority ) );
		$this->add_children( get( $args, 'children', [] ) );

		$parent = get( $args, 'parent' );
		if ( $parent ) {
			$this->set_parent( $parent );
		}

		// Mark that this field was initialied.
		$this->set_init_flag( true );

		return $this;
	}

	public function set_init_flag( bool $value ): void {
		$this->is_init = $value;
	}

	public function is_init(): bool {
		return $this->is_init;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_children() {
		return $this->children;
	}

	/**
	 * {@inheritDoc}
	 */
	public function find_children( $search, $operator = ' and ' ) {
		if ( $search instanceof Field_Interface ) {
			$args = [
				'id' => $search->id,
			];
		} elseif ( is_string( $search ) || is_numeric( $search ) ) {
			$args = [
				'id' => $search,
			];
		} elseif ( is_array( $search ) ) {
			$args = $search;
		} else {
			return [];
		}

		$found = wp_filter_object_list( $this->children, $args, $operator, false );

		if ( empty( $found ) ) {
			return [];
		}

		return reset( $found );
	}

	/**
	 * {@inheritDoc}
	 */
	public function add_children( $children ) {
		$children = array_map( static function ( $child ) {
			return make( Factory::class )->make( $child );
		}, $children );

		// Remove non instances of Field_Interface.
		$children = array_filter( (array) $children, static function ( $child ) {
			return $child instanceof Field_Interface;
		} );

		// Set the parent of all children to the current field.
		$children = array_map( function ( $children ) {
			return $children->set_parent( $this );
		}, $children );

		$this->children = array_merge( $this->children, $children );

		return $this->sort_children();
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove_children( $search = [], $operator = ' and ' ) {
		$found = $this->find_children( $search, $operator );

		if ( empty( $found ) ) {
			return $this;
		}

		$key = array_search( $found, $this->children );

		if ( isset( $this->children[ $key ] ) ) {
			unset( $this->children[ $key ] );
		}

		return $this->sort_children();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sort_children() {
		usort( $this->children, 'FakerPress\sort_by_priority' );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_parent( Field_Interface $parent ) {
		$this->parent = $parent;

		return $this;
	}
}
