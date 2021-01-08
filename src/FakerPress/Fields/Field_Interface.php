<?php
namespace FakerPress\Fields;

/**
 * Interface for Fields.
 *
 * @since  0.5.1
 */
interface Field_Interface {
	/**
	 * The slug of field we dealing with.
	 *
	 * @since  0.5.1
	 *
	 * @return string Slug of field.
	 */
	public function get_slug();

	/**
	 * The slug of field we dealing with.
	 *
	 * @since  0.5.1
	 *
	 * @return string Slug of field.
	 */
	public function get_id();

	/**
	 * The slug of field we dealing with.
	 *
	 * @since  0.5.1
	 *
	 * @param string|null $value ID of this field.
	 *
	 * @return string Slug of field.
	 */
	public function set_id( $value );

	/**
	 * Composes and return the html id of a field based on it's parents and it's own ID.
	 *
	 * @since  0.5.1
	 *
	 * @param string|null $suffix Something to be appended to the end of the html id.
	 *
	 * @return string HTML ID of the field.
	 */
	public function get_html_id( $suffix = null );

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
	 * Get the instance of template that will be used to render this field.
	 *
	 * @since  0.5.1
	 *
	 * @return Template Instance of the template class used to rende templates.
	 */
	public function get_template();

	/**
	 * Settings for this field object.
	 *
	 * @since  0.5.1
	 *
	 * @return array Settings for the field.
	 */
	public function get_settings();

	/**
	 * A given setting from the settings on this field.
	 *
	 * @since  0.5.2
	 *
	 * @param array|string $index   Index passed to fp_array_get.
	 * @param mixed        $default Default value for the setting.
	 *
	 * @return mixed Setting you are looking for.
	 */
	public function get_setting( $index, $default = null );

	/**
	 * Configure this field and it's childs.
	 *
	 * @since  0.5.1
	 *
	 * @param array  $args        Which settings will be use configure this field.
	 *
	 * @return Field_Interface    Returns an instance of itself be able to chain calls.
	 */
	public function setup( array $args = [] );

	/**
	 * Get field instance priority.
	 *
	 * @since  0.5.1
	 *
	 * @return int Which priority this field currently have.
	 */
	public function get_priority();

	/**
	 * Array of children fields.
	 *
	 * @since  0.5.1
	 *
	 * @return array Returns the children fields which must implement Field_Interface.
	 */
	public function get_children();

	/**
	 * Finds a children from this field instance.
	 *
	 * @since  0.5.2
	 *
	 * @uses wp_filter_object_list
	 *
	 * @param Field_Interface|array|string|int $search   Search for a given children to remove.
	 * @param string                           $operator Optional. The logical operation to perform. 'or' means
	 *                                                   only one element from the array needs to match; 'and'
	 *                                                   means all elements must match; 'not' means no elements may
	 *                                                   match. Default 'and'.
	 *
	 * @return Field_Interface|array                     Returns an instance of itself be able to chain calls.
	 */
	public function find_children( $search, $operator = 'and' );

	/**
	 * Add a children to this field instance.
	 *
	 * @since  0.5.1
	 *
	 * @param Field_Interface|array $children Which settings will be use
	 *
	 * @return Field_Interface      Returns an instance of itself be able to chain calls.
	 */
	public function add_children( $children );

	/**
	 * Remove a children from this field instance.
	 *
	 * @since  0.5.1
	 *
	 * @uses Field_Interface::find_children
	 *
	 * @param Field_Interface|array|string|int $search   Search for a given children to remove.
	 * @param string                           $operator Optional. The logical operation to perform. 'or' means
	 *                                                   only one element from the array needs to match; 'and'
	 *                                                   means all elements must match; 'not' means no elements may
	 *                                                   match. Default 'and'.
	 *
	 * @return Field_Interface|array                     Returns an instance of itself be able to chain calls.
	 */
	public function remove_children( $search = [], $operator = 'and' );

	/**
	 * Sort this Field's children array by priority
	 *
	 * @since  0.5.1
	 *
	 * @return Field_Interface  Returns an instance of itself be able to chain calls.
	 */
	public function sort_children();

	/**
	 * Parent field object.
	 *
	 * @since  0.5.1
	 *
	 * @return Field_Interface|null Returns a parent field or null if not present.
	 */
	public function get_parent();

	/**
	 * Set the parent field object.
	 *
	 * @since  0.5.1
	 *
	 * @param Field_Interface   $parent Which field is the parent of this one.
	 *
	 * @return Field_Interface  Returns an instance of itself be able to chain calls.
	 */
	public function set_parent( Field_Interface $parent );
}