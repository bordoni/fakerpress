<?php
namespace FakerPress\Fields;

use FakerPress\Template;

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
	protected $children;

	/**
	 * Hold the template instance that we use to render the fields.
	 *
	 * @since  0.5.1
	 *
	 * @var FakerPress\Template
	 */
	protected $template;

	/**
	 * Initialize the field class with the required props.
	 *
	 * @since  0.5.1
	 */
	public function __construct() {
		$this->template = new Template();
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
		var_dump( $this );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setup( array $arguments = [] ) {
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
	public function add_children( $children ) {
		// Remove non intances of Field_Interface.
		$children = array_filter( (array) $children, static function ( $children ) {
			return $children instanceof Field_Interface;
		} );

		$this->children = array_merge( $this->children, $children );

		return $this->sort_children();
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove_children( $search ) {

		/**
		 * @todo  @bordoni remove children method needs to be implemented.
		 */

		return $this->sort_children();
	}

	/**
	 * {@inheritDoc}
	 */
	public function sort_children() {
		usort( $this->children, static function( $a, $b ) {
			if ( $a->priority === $b->priority ) {
				return 0;
			}
			return $a->priority < $b->priority ? -1 : 1;
		} );
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