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
		'number',
		'hidden',
		// 'html',
		// 'textarea',
		// 'wysiwyg',
		'radio',
		'checkbox',
		'raw',
	);


	public function __construct( $type, $field, $container = array() ) {
		// Default Error Structure
		$this->error = false;

		// Non Valid types are just set to Raw
		if ( ! self::is_valid_type( $type ) ){
			$type = 'raw';
		}

		if ( is_string( $field ) ){
			$this->field = (object) array(
				'id' => $field,
			);
		} else {
			// Setup the Container if required
			$this->field = (object) $field;
		}

		$container = (object) wp_parse_args( $container, self::$default_container );

		// set the ID
		$this->type = $type;
		if ( ! isset( $this->field->id ) ){
			$this->id = self::abbr( uniqid() );
		} else {
			$this->id = $this->field->id;
		}

		$this->callback = null;
		$this->conditional = true;

		$this->label = $container->label;
		$this->description = $container->description;
		$this->actions = $container->actions;
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


	/******************
	 * Static methods *
	 ******************/

	public static function is_valid_type( $type = false ){
		// a list of valid field types, to prevent screwy behaviour
		return in_array( $type, apply_filters( self::plugin . '/fields/valid_types', self::$valid_types ) );
	}

	public static function name( $indexes = array() ){
		return self::plugin . '[' . implode( '][', (array) $indexes ) . ']';
	}

	public static function id( $id = array(), $container = false ){
		if ( ! is_array( $id ) ){
			$id = (array) $id;
		}
		if ( $container ){
			$id[] = 'container';
		}
		return self::plugin . '-field-' . implode( '-', (array) $id );
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

	public static function parse( $field, &$container = null ){
		if ( is_scalar( $field ) ){
			if ( ! is_string( $field ) ){
				return false;
			}

			$field = (object) array(
				'type' => $field,
			);
		} elseif ( is_array( $field ) ){
			$field = (object) $field;
		}

		if ( ! is_a( $container, __CLASS__ ) ){
			$default_container = array(
				'id' => self::abbr( uniqid() ),
				'type' => 'raw',
				'field' => array(),
			);
			$container = (object) wp_parse_args( $container, $default_container );
		}
		$field = (object) wp_parse_args( $field, ( ! empty( $container->field ) ? $container->field : array() ) );

		// Setup Private Attributes (_*)
		if ( isset( $field->_id ) ){

		} elseif ( empty( $field->id ) ){
			$field->_id = (array) $container->id;
		} else {
			$field->_id = (array) $field->id;
		}

		if ( isset( $field->_name ) ){

		} elseif ( ! isset( $field->name ) ){
			$field->_name = (array) ( isset( $container->field->name ) ? $container->field->name : $field->_id );
		} else {
			$field->_name = (array) $field->name;
		}

		// Setup Public Attributes
		if ( empty( $field->type ) ){
			$field->type = $container->type;
		}
		$field->_type = $field->type;

		$field->id = self::id( $field->_id );
		$field->name = self::name( $field->_name );

		switch ( $field->type ) {
			case 'heading':
				if ( ! isset( $field->title ) ){
					$field->title = '';
				}

				$container->has_label = false;
				break;
			case 'input':
				# code...
				break;
			case 'text':
				if ( empty( $field->size ) ){
					$field->size = 'medium';
				}
				break;
			case 'number':
				if ( empty( $field->size ) ){
					$field->size = 'tiny';
				}
				break;
			case 'radio':
				unset( $field->size );

				if ( ! isset( $field->options ) ){
					$field->options = array();
				}
				$field->options = (array) $field->options;

				break;
			case 'checkbox':
				unset( $field->size );

				if ( ! isset( $field->options ) ){
					$field->options = array();
				}

				if ( ! is_array( $field->options ) ){
					$field->options = array(
						1 => $field->options,
					);
				}

				break;
			case 'dropdown':
				if ( isset( $field->multiple ) && $field->multiple ){
					$field->type = 'hidden';
				} else {
					if ( ! isset( $field->options ) ){
						$field->options = array();
					}
					$field->options = (array) $field->options;
				}

				break;
			case 'interval':

				break;
			case 'date':
				$field->type = 'text';
				$field->size = 'small';
				break;
		}

		$field = apply_filters( self::plugin . '/fields/field', $field, $container );
		$container = apply_filters( self::plugin . '/fields/container', $container, $field );

		$field = apply_filters( self::plugin . '/fields/field-' . $field->_type, $field, $container );
		$container = apply_filters( self::plugin . '/fields/container-' . $field->_type, $container, $field );

		if ( ! empty( $field->class ) ){
			$field->class = (array) $field->class;
		}
		$field->class[] = 'field';
		$field->class[] = 'type-' . $field->_type;

		if ( ! empty( $field->size ) ){
			$field->class[] = 'size-' . $field->size;
		}

		return $field;
	}

	/*****************
	 * Field Methods *
	 *****************/

	public static function type_input( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

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

	public static function type_number( $field, $container = null, $output = 'string', $html = array() ) {
		return self::type_input( $field, $container, $output, $html );
	}

	public static function type_text( $field, $container = null, $output = 'string', $html = array() ) {
		return self::type_input( $field, $container, $output, $html );
	}

	public static function type_hidden( $field, $container = null, $output = 'string', $html = array() ) {
		return self::type_input( $field, $container, $output, $html );
	}

	public static function type_date( $field, $container = null, $output = 'string', $html = array() ) {
		return self::type_input( $field, $container, $output, $html );
	}

	public static function type_heading( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

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

	public static function type_radio( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

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
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		foreach ( $field->options as $value => $label ) {
			$checkbox = clone $field;
			$checkbox->_id[] = sanitize_html_class( $value );
			$checkbox->value = $value;

			if ( isset( $field->value ) && $field->value === $checkbox->value ){
				$checkbox->checked = true;
			}

			$html[] = self::type_input( $checkbox, null, 'string', array() );
			$html[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . self::id( $checkbox->_id ) . '">' . $label . '</label>';
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
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		if ( isset( $field->multiple ) && $field->multiple ){
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

	public static function type_range( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		$min = clone $field;
		$min->_id[] = 'min';
		$min->_name[] = 'min';
		$min->type = 'number';
		$min->{'data-type'} = 'min';
		$min->max = 25;
		$min->min = 1;
		$min->class = array();
		$min->placeholder = esc_attr__( 'e.g.: 3', self::plugin );

		$max = clone $field;
		$max->_id[] = 'max';
		$max->_name[] = 'max';
		$max->{'data-type'} = 'max';
		$max->type = 'number';
		$max->max = 25;
		$max->min = 1;
		$max->class = array();
		$max->disabled = true;
		$max->placeholder = esc_attr__( 'e.g.: 12', self::plugin );

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$html[] = self::type_input( $min, null, 'string', array() );
		$html[] = '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$html[] = self::type_input( $max, null, 'string', array() );

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
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		$min = clone $field;
		$min->_id[] = 'min';
		$min->_name[] = 'min';
		$min->type = 'date';
		$min->{'data-type'} = 'min';
		$min->class = array();
		$min->placeholder = esc_attr__( 'yyyy-mm-dd', self::plugin );

		$max = clone $field;
		$max->_id[] = 'max';
		$max->_name[] = 'max';
		$max->type = 'date';
		$max->{'data-type'} = 'max';
		$max->class = array();
		$max->placeholder = esc_attr__( 'yyyy-mm-dd', self::plugin );

		$interval = clone $field;
		$interval->_id[] = 'interval';
		$interval->_name[] = 'interval';
		$interval->type = 'dropdown';
		$interval->class = array();
		$interval->{'data-placeholder'} = esc_attr__( 'Select an Interval', self::plugin );
		$interval->options = Dates::get_intervals();

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		$html[] = self::type_dropdown( $interval, null, 'string' );
		$html[] = self::type_date( $min, null, 'string' );
		$html[] = '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$html[] = self::type_date( $max, null, 'string' );

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

	public static function type_raw( $field, $container = null, $output = 'string' ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ){
			return false;
		}

		if ( is_a( $container, __CLASS__ ) ){
			$html[] = $container->start();
		}

		if ( ! empty( $field->html ) ){
			$html[] = $field->html;
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

} // end class