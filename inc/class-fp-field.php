<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

/**
 * helper class that creates fields for use in Settings, MetaBoxes, Users, anywhere.
 * Instantiate it whenever you need a field
 *
 */
class Field {

	/**
	 * the field's id
	 * @var string
	 */
	public $id;

	/**
	 * the field's name (also known as it's label)
	 * @var string
	 */
	public $name;

	/**
	 * the field's name (also known as it's label)
	 * @var string
	 */
	public $type;

	/**
	 * the field's attributes
	 * @var array
	 */
	public $attributes;

	/**
	 * the field's arguments
	 * @var array
	 */
	public $args;

	/**
	 * field defaults (static)
	 * @var array
	 */
	public static $defaults = array(
		'type' => 'html',
		'name' => null,
		'attributes' => array(),
		'actions' => array(),
		'label' => null,
		'tooltip' => null,
		'size' => 'medium',
		'html' => null,
		'raw' => false,
		'value' => null,
		'options' => null,
		'conditional' => true,
		'callback' => null,
		'if_empty' => null,
		'can_be_empty' => false,
		'structure' => 'table',
		'after' => null,
	);

	/**
	 * valid field types (static)
	 * @var array
	 */
	public static $valid_types = array(
		'heading',
		'html',
		'text',
		'textarea',
		'wysiwyg',
		'radio',
		'boolean',
		'checkbox',
		'dropdown',
	);

	public static $sanitize = array(
		'type' => 'esc_attr',
		'name' => 'esc_attr',
		'label' => 'wp_kses_post',
		'tooltip' => 'wp_kses_post',
	);

	/**
	 * Class constructor
	 *
	 * @param string     $id    the field id
	 * @param array      $field the field settings
	 * @param null|mixed $value the field's current value
	 *
	 * @return void
	 */
	public function __construct( $id, $args ) {
		// a list of valid field types, to prevent screwy behaviour
		self::$valid_types = apply_filters( 'fakerpress/fields-valid_types', self::$valid_types );

		// parse args with defaults and extract them
		$this->args = (object) wp_parse_args( $args, self::$defaults );

		// sanitize the values just to be safe
		foreach ( self::$sanitize as $key => $method ) {
			$this->args->{$key} = call_user_func_array( $method, array( $this->args->{$key} ) );
		}

		// set the ID
		$this->id = apply_filters( 'fakerpress/field-id', esc_attr( $id ), $this );
		$this->type = apply_filters( 'fakerpress/field-type', esc_attr( $this->args->type ), $this );
		$this->name = apply_filters( 'fakerpress/field-name', ( ! empty( $this->args->name ) ? esc_attr( $this->args->name ) : esc_attr( $id ) ), $this );
		$this->value = $this->args->value;
		$this->attributes = $this->args->attributes;

		unset( $this->args->name, $this->args->type, $this->args->value, $this->args->attributes );

		// Default Error Structure
		$this->error = false;

		if ( ! in_array( $this->type, self::$valid_types ) ){
			return;
		}
	}

	/**
	 * Determines how to handle this field's creation
	 * either calls a callback function or runs this class' course of action
	 * logs an error if it fails
	 *
	 * @return void
	 */
	public function output( $print = false ) {
		if ( ! $this->args->conditional ) {
			return false;
		}

		if ( $this->args->callback && is_callable( $this->args->callback ) ) {
			// if there's a callback, run it
			call_user_func( $this->args->callback );
		} elseif ( in_array( $this->type, self::$valid_types ) ) {
			// the specified type exists, run the appropriate method
			$field = call_user_func_array( array( $this, $this->type ), array( $this->name, $this->value, $this->attributes ) );

			// filter the output
			$field = apply_filters( 'fakerpress/field-output-' . $this->type, $field, $this );
			$field = apply_filters( 'fakerpress/field-output-' . $this->type . '_' . $this->id, $field, $this );

			if ( $print ){
				echo balanceTags( $field );
			} else {
				return $field;
			}
		} else {
			return false;
		}
	}

	/**
	 * returns the field's start
	 *
	 * @return string the field start
	 */
	public function start() {
		$classes = array( 'fp-field', 'fp-field-' . $this->type );

		if ( ! empty( $this->args->fieldset ) && is_array( $this->args->fieldset ) ){
			$classes += array_map( '', $this->args->fieldset );
		}

		if ( ! empty( $this->args->size ) ){
			$classes[] = 'fp-size-' . $this->args->size;
		}

		if ( is_wp_error( $this->error ) ){
			$classes[] = 'fp-error';
		}

		$return = '<tr id="' . $this->id( true ) . '" class="' . implode( ' ', $classes ) . '">';

		return apply_filters( 'fakerpress/field-start', $return, $this );
	}

	/**
	 * returns the field's end
	 *
	 * @return string the field end
	 */
	public function end() {
		$return = '</tr>';

		return apply_filters( 'fakerpress/field-end', $return, $this );
	}

	/**
	 * returns the field's label
	 *
	 * @return string the field label
	 */
	public function label() {
		$html = '<th scope="row">';
		$html .= $this->tooltip();

		if ( isset( $this->args->label ) && false !== $this->args->label ) {
			$html .= '<label class="fp-field-label" for="' . $this->id() . '">' . $this->args->label . '</label>';
		}

		$html .= '</th>';

		return apply_filters( 'fakerpress/field-label', $html, $this );
	}

	public function name(){
		return 'fakerpress[' . $this->name . ']';
	}

	public function id( $container = false ){
		return 'fakerpress-field-' . $this->id . ( $container ? '-container' : '' );
	}

	/**
	 * returns the field's div start
	 *
	 * @return string the field div start
	 */
	public function wrap_start() {
		$html = '<td>';
		$html .= '<fieldset class="fp-field-wrap">';

		return apply_filters( 'fakerpress/field-wrap_start', $html, $this );
	}

	public function actions(){
		$html = '';
		foreach ( $this->args->actions as $action => $label ) {
			$html .= get_submit_button( $label, 'primary', 'fakerpress[actions][' . $action . ']', false );
		}

		return apply_filters( 'fakerpress/field-actions', $html, $this );
	}

	/**
	 * returns the field's div end
	 *
	 * @return string the field div end
	 */
	public function wrap_end() {
		$html = $this->actions();
		$html .= '</fieldset>';
		$html .= $this->description();
		$html .= '</td>';

		return apply_filters( 'fakerpress/field-wrap_end', $html, $this );
	}

	public function description(){
		$html = '';
		if ( ! empty( $this->args->description ) ) {
			$html .= '<p class="fp-field-description">' . $this->args->description . '</p>';;
		}

		return apply_filters( 'fakerpress/field-description', $html, $this );
	}

	/**
	 * returns the field's tooltip/description
	 *
	 * @return string the field tooltip
	 */
	public function tooltip() {
		$html = '';
		if ( ! empty( $this->args->tooltip ) ) {
			$html = '<p class="tooltip description">' . $this->args->tooltip . '</p>';
		}

		return apply_filters( 'fakerpress/field-tooltip', $html, $this );
	}

	/**
	 * returns the screen reader label
	 *
	 * @return string the screen reader label
	 */
	public function screenreader() {
		$html = '';
		if ( ! empty( $this->args->tooltip ) ) {
			$html = '<label class="screen-reader-text">' . $this->args->tooltip . '</label>';
		}

		return apply_filters( 'fakerpress/field-screenreader', $html, $this );
	}

	/**
	 * Return a string of attributes for the field
	 *
	 * @return string
	 **/
	public function attributes() {
		$html = '';

		$this->attributes['id'] = $this->id();
		$this->attributes['name'] = $this->name();
		if ( ! empty( $this->attributes ) ) {
			foreach ( $this->attributes as $key => $value ) {
				$html .= ' ' . $key . '="' . $value . '"';
			}
		}

		return apply_filters( 'fakerpress/field-attributes', $html, $this->name, $this );
	}

	/**
	 * generate a heading field
	 *
	 * @return string the field
	 */
	public function heading() {
		$field = '<h3>' . $this->label . '</h3>';

		return $field;
	}

	/**
	 * generate an html field
	 *
	 * @return string the field
	 */
	public function html() {
		$field = $this->label();
		$field .= $this->html;

		return $field;
	}


	/**
	 * generate a simple text field
	 *
	 * @return string the field
	 */
	public function text( $name, $value = null, $attributes ) {
		$defaults = array(
			'type' => 'text',
		);
		$this->attributes = wp_parse_args( $this->attributes, $defaults );

		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<input' . $this->attributes() . '/>';
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a textarea field
	 *
	 * @return string the field
	 */
	public function textarea() {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<textarea' . $this->attributes() . '>';
		$field .= esc_html( stripslashes( $this->value ) );
		$field .= '</textarea>';
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a wp_editor field
	 *
	 * @return string the field
	 */
	public function wysiwyg() {
		$settings = array(
			'teeny'   => true,
			'wpautop' => true,
		);
		ob_start();
		wp_editor( html_entity_decode( ( $this->value ) ), $this->name, $settings );
		$editor = ob_get_clean();
		$field  = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= $editor;
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a radio button field
	 *
	 * @return string the field
	 */
	public function radio() {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		if ( is_array( $this->options ) ) {
			foreach ( $this->options as $option_id => $title ) {
				$field .= '<label title="' . esc_attr( $title ) . '">';
				$field .= '<input type="radio"';
				$field .= $this->get_name();
				$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( $this->value, $option_id, false ) . '/>';
				$field .= $title;
				$field .= '</label>';
			}
		} else {
			$field .= '<span class="tribe-error">' . __( 'No radio options specified', 'tribe-events-calendar' ) . '</span>';
		}
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a checkbox_list field
	 *
	 * @return string the field
	 */
	public function checkbox() {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		if ( ! is_array( $this->value ) ) {
			if ( ! empty( $this->value ) ) {
				$this->value = array( $this->value );
			} else {
				$this->value = array();
			}
		}

		if ( is_array( $this->options ) ) {
			foreach ( $this->options as $option_id => $title ) {
				$field .= '<label title="' . esc_attr( $title ) . '">';
				$field .= '<input type="checkbox"';
				$field .= $this->get_name( true );
				$field .= ' value="' . esc_attr( $option_id ) . '" ' . checked( in_array( $option_id, $this->value ), true, false ) . '/>';
				$field .= $title;
				$field .= '</label>';
			}
		} else {
			$field .= '<span class="tribe-error">' . __( 'No checkbox options specified', 'tribe-events-calendar' ) . '</span>';
		}
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a boolean checkbox field
	 *
	 * @return string the field
	 */
	public function boolean() {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<input type="checkbox"';
		$field .= $this->get_name();
		$field .= ' value="1" ' . checked( $this->value, true, false );
		$field .= $this->attributes();
		$field .= '/>';
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	/**
	 * generate a dropdown field
	 *
	 * @return string the field
	 */
	public function dropdown() {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		if ( is_array( $this->options ) && ! empty( $this->options ) ) {
			$field .= '<select';
			$field .= $this->get_name();
			$field .= '>';
			foreach ( $this->options as $option_id => $title ) {
				$field .= '<option value="' . esc_attr( $option_id ) . '"';
				if ( is_array( $this->value ) ) {
					$field .= isset( $this->value[0] ) ? selected( $this->value[0], $option_id, false ) : '';
				} else {
					$field .= selected( $this->value, $option_id, false );
				}
				$field .= '>' . esc_html( $title ) . '</option>';
			}
			$field .= '</select>';
			$field .= $this->screenreader();
		} elseif ( $this->if_empty ) {
			$field .= '<span class="empty-field">' . (string) $this->if_empty . '</span>';
		} else {
			$field .= '<span class="tribe-error">' . __( 'No select options specified', 'tribe-events-calendar' ) . '</span>';
		}
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

} // end class

