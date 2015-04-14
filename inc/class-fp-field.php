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
		'range',
		'interval',
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
		$classes = array( 'fp-field', 'fp-field-' . $this->type . '-container' );

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

	public function name( $indexes = array() ){
		if ( empty( $indexes ) ){
			$indexes = (array) $this->name;
		}
		return 'fakerpress[' . implode( '][', $indexes ) . ']';
	}

	public function id( $container = false ){
		return 'fakerpress-field-' . $this->id . ( $container ? '-container' : '' );
	}

	public function is_multiple(){
		return (bool) ( isset( $this->args->multiple ) && $this->args->multiple ? true : false );
	}

	public function actions(){
		$html = '';
		foreach ( $this->args->actions as $action => $label ) {
			$html .= get_submit_button( $label, 'primary', 'fakerpress[actions][' . $action . ']', false );
		}

		return apply_filters( 'fakerpress/field-actions', $html, $this );
	}

	/**
	 * returns the field's div start
	 *
	 * @return string the field div start
	 */
	public function wrap_start() {
		if ( 'heading' === $this->type ){
			$html = '<th colspan="2" class="fp-field-wrap">';
		} else {
			$html = '<td>';
			$html .= '<fieldset class="fp-field-wrap">';
		}

		return apply_filters( 'fakerpress/field-wrap_start', $html, $this );
	}

	/**
	 * returns the field's div end
	 *
	 * @return string the field div end
	 */
	public function wrap_end() {
		$html = '';
		if ( 'heading' === $this->type ){
			$html .= $this->description();
			$html .= '</th>';
		} else {
			$html .= $this->actions();
			$html .= '</fieldset>';
			$html .= $this->description();
			$html .= '</td>';
		}
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
	public function attributes( $attributes = null ) {
		$html = '';
		$defaults = array(
			'id' => $this->id(),
			'name' => $this->name(),
		);

		if ( is_null( $attributes ) ){
			$attributes = $this->attributes;
		}
		$attributes = wp_parse_args( $attributes, $defaults );

		if ( ! empty( $attributes ) ) {
			foreach ( $attributes as $key => $value ) {
				if ( is_array( $value ) || is_object( $value ) ){
					if ( 'class' !== $key ){
						$value = htmlspecialchars( json_encode( $value ), ENT_QUOTES, 'UTF-8' );
					} else {
						$value = implode( ' ', $value );
					}
				} elseif ( false === $value ){
					$html .= ' ' . $key;
					continue;
				}

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

		$field = $this->start();
		$field .= $this->wrap_start();
		$field .= '<h3>' . $this->args->label . '</h3>';
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

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

		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<input' . $this->attributes( wp_parse_args( $attributes, $defaults ) ) . '/>';
		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}


	/**
	 * generate a simple text field
	 *
	 * @return string the field
	 */
	public function range( $name, $value = null, $attributes ) {
		$min_attr = array(
			'name' => $this->name( array( 'qty', 'min' ) ),
			'type' => 'number',
			'max' => 25,
			'min' => 1,
			'style' => 'width: 90px;',
			'class' => array( 'qty-range-min' ),
			'placeholder' => esc_attr__( 'e.g.: 3', 'fakerpress' ),
		);

		$max_attr = array(
			'name' => $this->name( array( 'qty', 'max' ) ),
			'type' => 'number',
			'max' => 25,
			'min' => 1,
			'style' => 'width: 90px;',
			'disabled' => false,
			'class' => array( 'qty-range-max' ),
			'placeholder' => esc_attr__( 'e.g.: 10', 'fakerpress' ),
		);

		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<input' . $this->attributes( $min_attr ) . '/>';
		$field .= '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$field .= '<input' . $this->attributes( $max_attr ) . '/>';
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
	public function textarea( $name, $value = null, $attributes ) {
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
	public function wysiwyg( $name, $value = null, $attributes ) {
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
	public function radio( $name, $value = null, $attributes ) {
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
	public function checkbox( $name, $value = null, $attributes ) {
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
	public function boolean( $name, $value = null, $attributes ) {
		$args = array(
			'type' => 'checkbox',
			'value' => 1,
		);

		if ( $value ){
			$args['checked'] = false;
		}

		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();
		$field .= '<input ' . $this->attributes( $args ) . '/>';
		if ( ! empty( $this->args->info ) ){
			$field .= '<label class="fp-field-label" for="' . $this->id() . '">' . $this->args->info . '</label>';
		}
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
	public function dropdown( $name, $value = null, $attributes ) {
		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();

		if ( $this->is_multiple() ){
			$defaults = array(
				'type' => 'hidden',
				'class' => 'fp-field-select2-mutiple',
				'value' => $value,
			);
			$field .= '<input ' . $this->attributes( wp_parse_args( $attributes, $defaults ) ) . ' />';
		} else {

		}

		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

	public function interval( $name, $value = null, $attributes ) {
		$min_attr = array(
			'id' => $this->id() . '-min',
			'name' => $this->name( array( 'date', 'min' ) ),
			'type' => 'text',
			'style' => 'width: 150px;',
			'class' => array( 'fp-field-datepicker' ),
			'data-type' => 'min',
			'placeholder' => esc_attr__( 'mm/dd/yyyy', 'fakerpress' ),
		);

		$max_attr = array(
			'id' => $this->id() . '-max',
			'name' => $this->name( array( 'date', 'max' ) ),
			'type' => 'text',
			'style' => 'width: 150px;',
			'class' => array( 'fp-field-datepicker' ),
			'data-type' => 'max',
			'placeholder' => esc_attr__( 'mm/dd/yyyy', 'fakerpress' ),
		);

		$field = $this->start();
		$field .= $this->label();
		$field .= $this->wrap_start();

		$field .= '<select id="fakerpress_interval_date" class="fp-field-interval fp-field-select2" data-placeholder="' . esc_attr__( 'Select an Interval', 'fakerpress' ) . '" style="margin-right: 5px; margin-top: -4px;">';
		$field .= '<option></option>';

		$_json_date_selection_output = Dates::get_intervals();
		foreach ( $_json_date_selection_output as $option ) {
			$field .= '<option data-min="' . esc_attr( date( 'm/d/Y', strtotime( $option['min'] ) ) ) . '" data-max="' . esc_attr( date( 'm/d/Y', strtotime( $option['max'] ) ) ) . '" value="' . esc_attr( $option['text'] ) . '">' . esc_attr( $option['text'] ) . '</option>';
		}
		$field .= '</select>';

		$field .= '<input' . $this->attributes( $min_attr ) . '/>';
		$field .= '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$field .= '<input' . $this->attributes( $max_attr ) . '/>';

		$field .= $this->screenreader();
		$field .= $this->wrap_end();
		$field .= $this->end();

		return $field;
	}

} // end class

