<?php
namespace FakerPress\Fields;

use FakerPress\Template;
use FakerPress\Plugin;

/**
 * Abstract for Fields.
 *
 * @since  0.5.1
 */
abstract class Field_Abstract implements Field_Interface {

	/**
	 * Slug for this type of field.
	 *
	 * @since  0.5.1
	 *
	 * @var string
	 */
	protected $slug;

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
	public function setup_template() {
		$this->template = new Template();
		$this->template
			->set_template_origin( Plugin::$instance )
			->set_template_folder( 'src/templates/fields' );
	}

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
		$ids = [];
		$parent = $this;

		// Fetch the ID of all parents
		while ( $parent = $parent->get_parent() ) {
			$ids[] = $parent->get_id();
		}

		$ids[] = $this->get_id();


		$id = 'fakerpress-field-' . implode( '-', (array) $ids );

		if ( $suffix ) {
			$id .= '-' . $suffix;
		}

		return sanitize_html_class( $id );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_setting( $index, $default = null ) {
		return fp_array_get( $this->get_settings(), $index, null, $default );
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
		return $this->template;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_html( $format = 'string' ) {
		// Globally set in the template vars this instance of field.
		$this->template->set( 'field', $this, false );

		$this->template->render( $this->get_slug() );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup( array $args = [] ) {
		$this->setup_template();

		$this->set_id( fp_array_get( $args, 'id' ) );

		return $this;
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
	public function find_children( $search, $operator = 'and' ) {
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
		// Remove non instances of Field_Interface.
		$children = array_filter( (array) $children, static function ( $children ) {
			return $children instanceof Field_Interface;
		} );

		$this->children = array_merge( $this->children, $children );

		// Set the parent of all children to the current field.
		$this->children = array_map( function ( $children ) {
			return $children->set_parent( $this );
		}, $this->children );

		return $this->sort_children();
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove_children( $search = [], $operator = 'and' ) {
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
		usort( $this->children, 'fp_sort_by_priority' );
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