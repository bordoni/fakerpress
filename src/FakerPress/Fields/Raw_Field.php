<?php
/**
 * Raw field: prints a block of HTML built by plugin code.
 *
 * @package FakerPress
 */

namespace FakerPress\Fields;

use function FakerPress\get;

/**
 * Field that prints the HTML passed in its `html` argument, using the `raw` template.
 */
class Raw_Field extends Field_Abstract {
	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $slug = 'raw';

	/**
	 * HTML printed by the `raw` template.
	 *
	 * @var string|null
	 */
	protected $raw_html;

	/**
	 * Sets up the field from its arguments, reading the raw HTML from `html`.
	 *
	 * @param array $args Field arguments: the common ones handled by Field_Abstract::init(), plus `html`.
	 *
	 * @return $this
	 */
	public function init( array $args = [] ) {
		parent::init( $args );

		$this->set_raw_html( get( $args, 'html' ) );

		return $this;
	}

	/**
	 * Sets the HTML this field prints.
	 *
	 * @param string|null $value The HTML.
	 *
	 * @return void
	 */
	public function set_raw_html( $value ) {
		$this->raw_html = $value;
	}

	/**
	 * Gets the HTML this field prints.
	 *
	 * @return string|null The HTML, or null when none was set.
	 */
	public function get_raw_html() {
		return $this->raw_html;
	}
}
