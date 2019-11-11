<?php
namespace FakerPress\Fields;

/**
 * Interface for Fields.
 *
 * @since  0.5.1
 */
interface Field_Interface {
	/**
	 * The type of field we dealing with.
	 *
	 * @since  0.5.1
	 *
	 * @return string Type of field.
	 */
	public function get_type();

	/**
	 * Settings for this field object.
	 *
	 * @since  0.5.1
	 *
	 * @param string $format Which format we should return the HTML. Options: `string` or `array`.
	 *
	 * @return string|array The string holding the HTML of this field.
	 */
	public function get_html( $format = 'string' );

	/**
	 * Settings for this field object.
	 *
	 * @since  0.5.1
	 *
	 * @return array Settings for the field.
	 */
	public function get_settings();

	/**
	 * Parent field object.
	 *
	 * @since  0.5.1
	 *
	 * @return Field_Interface|null Returns a parent field or null if not present.
	 */
	public function get_parent();
}