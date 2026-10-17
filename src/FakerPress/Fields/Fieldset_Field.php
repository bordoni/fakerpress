<?php
/**
 * Fieldset field: groups child fields.
 *
 * @package FakerPress
 */

namespace FakerPress\Fields;

/**
 * Field that renders its child fields as a nested table, using the `fieldset` template.
 */
class Fieldset_Field extends Field_Abstract {
	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	protected static $slug = 'fieldset';
}
