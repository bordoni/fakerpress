<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ){
	die;
}

class Field {

	const plugin = 'fakerpress';
	const abbr = 'fp';

	public static function abbr( $str = '' ){
		return self::abbr . '-' . $str;
	}

	public $type = 'raw';

	public $id;

	public $field;

	public $container;

	public $has_container = true;

	public $has_wrap = true;

	public $has_label = true;


	public static $default_field = array(
		'id' => '__doe__',
		'name' => array(),
		'value' => '',
		'options' => array(),
		'size' => 'medium',
		'conditional' => true,
		'callback' => null,
	);

	public static $default_container = array(
		'label' => '',
		'description' => '',
		'attributes' => array(),
		'actions' => array(),
	);

	public static $valid_types = array(
		'heading',
		'input',
		'text',
		'dropdown',
		'range',
		'interval',
		// 'html',
		// 'textarea',
		// 'wysiwyg',
		'radio',
		'checkbox',
	);


	public function __construct( $type, $field, $container = array() ) {
		// Setup the Container if required
		$container = (object) wp_parse_args( $container, self::$default_container );

		// a list of valid field types, to prevent screwy behaviour
		self::$valid_types = apply_filters( self::plugin . '/fields/valid_types', self::$valid_types );

		// Default Error Structure
		$this->error = false;

		// parse args with defaults and extract them
		if ( ! is_scalar( $field ) ){
			$this->field = (object) wp_parse_args( $field, self::$default_field );
		} else {
			return;
		}

		// set the ID
		$this->type = apply_filters( self::plugin . '/fields/field-type', esc_attr( $type ), $this );

		$this->id = apply_filters( self::plugin . '/fields/field-id', esc_attr( $this->field->id ), $this );

		$this->callback = null;
		$this->conditional = true;

		$this->field->id = self::id( $this->id );
		$this->field->name = self::name( ( ! empty( $this->field->name ) ? $this->field->name : $this->id ) );
		$this->field->type = $this->type;
		if ( ! empty( $this->field->class ) ){
			$this->field->class = (array) $this->field->class;
		}
		$this->field->class[] = 'field';
		$this->field->class[] = 'type-' . $this->type;

		if ( ! empty( $this->field->size ) ){
			$this->field->class[] = 'size-' . $this->field->size;
		}

		$this->label = $container->label;
		$this->description = $container->description;
		$this->actions = $container->actions;

		unset( $this->field->callback, $this->field->conditional, $this->field->size );

		if ( ! in_array( $this->type, self::$valid_types ) ){
			return;
		}

	}

	public function output( $print = false ) {
		if ( ! $this->conditional ) {
			return false;
		}

		if ( $this->callback && is_callable( $this->callback ) ) {
			// if there's a callback, run it
			call_user_func( $this->callback );
		} elseif ( in_array( $this->type, self::$valid_types ) ) {
			// the specified type exists, run the appropriate method
			$field = call_user_func_array( array( __CLASS__, 'type_' . $this->type ), array( $this->field, $this, 'string', array() ) );

			// filter the output
			$field = apply_filters( self::plugin . '/fields/field-output-' . $this->type, $field, $this );
			$field = apply_filters( self::plugin . '/fields/field-output-' . $this->type . '_' . $this->id, $field, $this );

			if ( $print ){
				echo balanceTags( $field );
			} else {
				return $field;
			}
		} else {
			return false;
		}
	}

	public static function name( $indexes = array() ){
		return self::plugin . '[' . implode( '][', (array) $indexes ) . ']';
	}

	public static function id( $id, $container = false ){
		return self::plugin . '-field-' . $id . ( $container ? '-container' : '' );
	}

	public static function attr( $attributes = array(), $html = array() ) {
		if ( is_scalar( $attributes ) ){
			return null;
		}

		$defaults = array();
		$attributes = wp_parse_args( (array) $attributes, $defaults );

		foreach ( $attributes as $key => $value ) {
			if ( is_null( $value ) || false === $value ){
				continue;
			}

			if ( 'class' === $key && ! is_array( $value ) ){
				$value = (array) $value;
			}

			$attr = $key;

			if ( ! is_scalar( $value ) ) {
				if ( 'class' === $key ){
					$value = array_map( array( __CLASS__, 'abbr' ), (array) $value );
					$value = array_map( 'sanitize_html_class', $value );
					$value = implode( ' ', $value );
				} else {
					$value = htmlspecialchars( json_encode( $value ), ENT_QUOTES, 'UTF-8' );
				}
			}
			if ( ! is_bool( $value ) || true !== $value ){
				$attr .= '="' . $value . '"';
			}

			$html[ $key ] = $attr;
		}

		return ' ' . implode( ' ', $html );
	}

	public function start( $output = 'string', $html = array() ) {
		$html[] = $this->start_container();
		if ( $this->has_label ){
			$html[] = $this->label();
		}
		$html[] = $this->start_wrap();

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function end( $output = 'string', $html = array() ) {
		$html[] = $this->end_wrap();
		$html[] = $this->end_container();

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function start_container( $output = 'string', $html = array() ) {
		$classes = array( 'field-container', 'type-' . $this->type . '-container' );

		if ( is_wp_error( $this->error ) ){
			$classes[] = 'error';
		}

		$classes = array_map( array( __CLASS__, 'abbr' ) , $classes );

		$html[] = '<tr id="' . self::id( $this->id, true ) . '" class="' . implode( ' ', $classes ) . '">';

		$html = apply_filters( self::plugin . '/fields/field-start', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function end_container( $output = 'string', $html = array() ) {
		$html[] = '</tr>';

		$html = apply_filters( self::plugin . '/fields/field-end', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function start_wrap( $output = 'string', $html = array() ) {
		if ( 'heading' === $this->type ){
			$html[] = '<th colspan="2" class="' . self::abbr( 'field-wrap' ) . '">';
		} else {
			$html[] = '<td>';
			$html[] = '<fieldset class="' . self::abbr( 'field-wrap' ) . '">';
		}

		$html = apply_filters( self::plugin . '/fields/field-wrap_start', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function end_wrap( $output = 'string', $html = array() ) {
		if ( 'heading' === $this->type ){
			$html[] = $this->description();
			$html[] = '</th>';
		} else {
			$html[] = $this->actions();
			$html[] = '</fieldset>';
			$html[] = $this->description();
			$html[] = '</td>';
		}

		$html = apply_filters( self::plugin . '/fields/field-wrap_end', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function label( $output = 'string', $html = array() ) {
		$html[] = '<th scope="row">';

		if ( isset( $this->label ) && false !== $this->label ) {
			$html[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . self::id( $this->id ) . '">' . $this->label . '</label>';
		}

		$html[] = '</th>';

		$html = apply_filters( self::plugin . '/fields/field-label', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function actions( $output = 'string', $html = array() ) {
		$html[] = '<div class="' . self::abbr( 'actions' ) . '">';
		foreach ( $this->actions as $action => $label ) {
			$html[] = get_submit_button( $label, 'primary', self::plugin . '[actions][' . $action . ']', false );
		}
		$html[] = '</div>';

		$html = apply_filters( self::plugin . '/fields/field-actions', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public function description( $output = 'string', $html = array() ) {
		if ( ! empty( $this->description ) ) {
			$html[] = '<p class="' . self::abbr( 'field-description' ) . '">' . $this->description . '</p>';;
		}

		$html = apply_filters( self::plugin . '/fields/field-description', $html, $this );
		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	/*****************
	 * Field Methods *
	 *****************/

	public static function type_input( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return null;
		}

		$field = (object) $field;

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$html[] = '<input' . self::attr( $field ) . '/>';

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_heading( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( ! is_a( $container, __CLASS__ ) ){
			$container = (object) array(
				'field' => array(
					'title' => '',
				),
			);
			$container->has_label = false;

			$field = wp_parse_args( $field, $container->field );
		}
		$field = (object) $field;

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$html[] = '<h3>' . $field->title . '</h3>';

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}

	}

	public static function type_text( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( ! is_a( $container, __CLASS__ ) ){
			$container = (object) array(
				'field' => array(
					'type' => 'text',
				),
			);
			$field = wp_parse_args( $field, $container->field );
		}
		$field = (object) $field;

		$html = self::type_input( $field, $container, 'array', array() );

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_range( $field, $container = null, $output = 'string', $html = array() ) {
		$min_attr = array(
			'name' => self::name( array( 'qty', 'min' ) ),
			'type' => 'number',
			'max' => 25,
			'min' => 1,
			'style' => 'width: 90px;',
			'class' => array( 'qty-range-min' ),
			'placeholder' => esc_attr__( 'e.g.: 3', self::plugin ),
		);

		$max_attr = array(
			'name' => self::name( array( 'qty', 'min' ) ),
			'type' => 'number',
			'max' => 25,
			'min' => 1,
			'style' => 'width: 90px;',
			'disabled' => false,
			'class' => array( 'qty-range-max' ),
			'placeholder' => esc_attr__( 'e.g.: 10', self::plugin ),
		);

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$html[] = '<input' . self::attr( $min_attr ) . '/>';
		$html[] = '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$html[] = '<input' . self::attr( $max_attr ) . '/>';

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_radio( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$field = (object) $field;
		$field->type = 'radio';
		$field->options = (array) $field->options;

		foreach ( $field->options as $value => $label ) {
			$checkbox = clone $field;
			$radio->value = $value;

			$html[] = self::type_input( $radio, null, 'string', array() );
			$html[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . $field->id . '">' . $label . '</label>';
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_checkbox( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$field = (object) $field;
		$field->type = 'checkbox';
		if ( ! is_array( $field->options ) ){
			$field->options = array(
				1 => $field->options,
			);
		}

		foreach ( $field->options as $value => $label ) {
			$checkbox = clone $field;
			$checkbox->id .= '-' . sanitize_html_class( $value );
			$checkbox->value = $value;

			if ( isset( $field->value ) && $field->value === $checkbox->value ){
				$checkbox->checked = true;
			}

			$html[] = self::type_input( $checkbox, null, 'string', array() );
			$html[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . $checkbox->id . '">' . $label . '</label>';
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_dropdown( $field, $container = null, $output = 'string', $html = array() ) {
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$field = (object) $field;

		if ( isset( $field->multiple ) && $field->multiple ){
			$field->type = 'hidden';

			$html[] = self::type_input( $field, null, 'string', array() );
		} else {
			$html[] = '<select' . self::attr( $field ) . '>';
			$html[] = '<option></option>';

			foreach ( $field->options as $option ) {
				$html[] = '<option' . self::attr( $option ) . '>' . esc_attr( $option['text'] ) . '</option>';
			}
			$html[] = '</select>';
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_interval( $field, $container = null, $output = 'string', $html = array() ) {
		$min_attr = array(
			'id' => self::id( 'min' ),
			'name' => self::name( array( 'date', 'min' ) ),
			'type' => 'text',
			'style' => 'width: 150px;',
			'class' => array( 'type-datepicker' ),
			'data-type' => 'min',
			'placeholder' => esc_attr__( 'mm/dd/yyyy', self::plugin ),
		);

		$max_attr = array(
			'id' => self::id( 'max' ),
			'name' => self::name( array( 'date', 'max' ) ),
			'type' => 'text',
			'style' => 'width: 150px;',
			'class' => array( 'type-datepicker' ),
			'data-type' => 'max',
			'placeholder' => esc_attr__( 'mm/dd/yyyy', self::plugin ),
		);

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$interval = array(
			'class' => array( 'type-interval', 'type-dropdown' ),
			'value' => '',
			'options' => Dates::get_intervals(),
		);

		$html[] = self::type_dropdown( $interval, null, 'string' );
		$html[] = '<input' . self::attr( $min_attr ) . '/>';
		$html[] = '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$html[] = '<input' . self::attr( $max_attr ) . '/>';

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->end();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_textarea( $field, $container = null, $output = 'string' ) {
		if ( is_array( $container ) ){
			$field[] = $this->start_container();
			$field[] = $this->label();
			$field[] = $this->start_wrap();
		}

		$field[] = '<textarea' . $this->attr() . '>' . esc_html( stripslashes( $this->value ) ) . '</textarea>';

		if ( is_array( $container ) ){
			$field[] = $this->end_wrap();
			$field[] = $this->end_container();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $field );
		} else {
			return $field;
		}
	}

	public static function type_wysiwyg( $field, $container = null, $output = 'string' ) {
		$settings = array(
			'teeny'   => true,
			'wpautop' => true,
		);
		ob_start();
		wp_editor( html_entity_decode( ( $this->value ) ), $this->name, $settings );
		$editor = ob_get_clean();

		if ( is_array( $container ) ){
			$field[] = $this->start_container();
			$field[] = $this->label();
			$field[] = $this->start_wrap();
		}

		$field[] = $editor;

		if ( is_array( $container ) ){
			$field[] = $this->end_wrap();
			$field[] = $this->end_container();
		}

		if ( 'string' === $output ){
			return implode( "\r\n", $field );
		} else {
			return $field;
		}
	}

} // end class