<?php
namespace FakerPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Field {

	const plugin = 'fakerpress';
	const abbr = 'fp';

	public static function abbr( $str = '' ) {
		return self::abbr . '-' . $str;
	}

	public $type = 'raw';

	public $id;

	public $field;

	public $container;

	public $has_container = true;

	public $has_wrap = true;

	public static $default_container = array(
		'label' => '',
		'description' => '',
		'attributes' => array(),
		'actions' => array(),
		'heads' => array(),
		'class' => array(),
		'wrap' => array(
			'class' => array()
		),
		'blocks' => array( 'label', 'fields', 'description', 'actions' ),
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
		'meta',
		'taxonomy',
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
		if ( ! self::is_valid_type( $type ) ) {
			$type = 'raw';
		}

		if ( is_string( $field ) ) {
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
		if ( ! isset( $this->field->id ) ) {
			$this->id = (array) self::abbr( uniqid() );
		} else {
			$this->id = (array) $this->field->id;
		}

		$this->callback = null;
		$this->conditional = true;

		$this->label = $container->label;
		$this->description = $container->description;
		$this->attributes = $container->attributes;
		$this->actions = $container->actions;
		$this->blocks = $container->blocks;
		$this->class = $container->class;
		$this->wrap = $container->wrap;
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

			if ( $print ) {
				echo balanceTags( $field );
			} else {
				return $field;
			}
		} else {
			return false;
		}
	}

	public function build( $content, $output = 'string', $html = array() ) {
		$content = (array) $content;
		$key = array_search( 'fields', $this->blocks );
		$is_multiple = is_array( reset( $content ) );
		if ( ! $is_multiple ) {
			$content = array( $content );
		}

		$before = array_filter( array_slice( $this->blocks, 0, $key ), 'is_array' );
		$before_content = array();
		foreach ( $before as $i => $block ) {
			$_html = '';
			if ( ! empty( $block['html'] ) ) {
				$_html = $block['html'];
				unset( $block['html'] );
			}
			$before_content[] = '<td' . self::attr( $block ) . '>' . $_html . '</td>';
		}

		$after = array_filter( array_slice( $this->blocks, $key + 1, count( $this->blocks ) - ( $key + 1 ) ), 'is_array' );
		$after_content = array();
		foreach ( $after as $i => $block ) {
			$_html = '';
			if ( ! empty( $block['html'] ) ) {
				$_html = $block['html'];
				unset( $block['html'] );
			}
			$after_content[] = '<td' . self::attr( $block ) . '>' . $_html . '</td>';
		}

		if ( in_array( 'heading', $this->blocks ) ) {
			$html[] = self::type_heading( array(
				'type' => 'heading',
				'title' => $this->label,
				'description' => $this->description,
			), null, 'string' );
		}

		foreach ( $content as $blocks ) {
			if ( in_array( 'table', $this->blocks ) ) {
				$html[] = self::start_table( $this );
			}

			$html[] = self::start_container( $this );
			$html[] = implode( "\r\n", $before_content );

			if ( in_array( 'label', $this->blocks ) ) {
				$html[] = self::label( $this );
			}

			$html[] = self::start_wrap( $this );
			$html[] = implode( "\r\n", $blocks );
			$html[] = self::end_wrap( $this );

			$html[] = implode( "\r\n", $after_content );

			$html[] = self::end_container( $this );

			if ( in_array( 'table', $this->blocks ) ) {
				$html[] = self::end_table( $this );
			}
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function start_table( $container, $output = 'string', $html = array() ) {
		$html[] = '<table class="' . self::abbr( 'table-' . implode( '-', $container->id ) ) . '">';
		if ( ! empty( $container->heads ) ) {
			$html[] = '<thead>';
			foreach ( $container->heads as $head ) {
				$_html = '';
				if ( ! empty( $head['html'] ) ) {
					$_html = $head['html'];
					unset( $head['html'] );
				}
				$html[] = '<th' . self::attr( $head ) . '>' . $_html . '</th>';
			}
			$html[] = '</thead>';
		}
		$html[] = '<tbody>';

		$html = apply_filters( self::plugin . '/fields/field-start_table', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function end_table( $container, $output = 'string', $html = array() ) {
		$html[] = '</tbody>';
		$html[] = '</table>';

		$html = apply_filters( self::plugin . '/fields/field-end_table', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function start_container( $container, $output = 'string', $html = array() ) {
		if ( ! is_array( $container->class ) ) {
			$container->class = (array) $container->class;
		}
		$container->class[] = 'field-container';
		$container->class[] = 'type-' . $container->type . '-container';

		if ( is_wp_error( $container->error ) ) {
			$container->class[] = 'error';
		}

		$container->class = array_map( array( __CLASS__, 'abbr' ), $container->class );

		if ( ! in_array( 'table', $container->blocks ) ) {
			$html[] = '<tr id="' . self::id( $container->id, true ) . '" class="' . implode( ' ', $container->class ) . '" ' . self::attr( $container->attributes ) . '>';
		}

		$html = apply_filters( self::plugin . '/fields/field-start_container', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function end_container( $container, $output = 'string', $html = array() ) {
		if ( ! in_array( 'table', $container->blocks ) ) {
			$html[] = '</tr>';
		}

		$html = apply_filters( self::plugin . '/fields/field-end_container', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function wrapper( $content = array(), $field = array(), $output = 'string' ) {
		$attributes = (object) array();
		$attributes->class[] = 'field-wrap';
		$attributes->class[] = 'type-' . $field->type . '-wrap';

		$html = array();
		if ( ! empty( $content ) ) {
			$html[] = '<fieldset' . self::attr( $attributes ) . '>';
			$html[] = implode( "\r\n", (array) $content );
			if ( ! empty( $field->label ) ) {
				$html[] = '<label class="' . self::abbr( 'internal-label' ) . '">' . $field->label . '</label>';
			}
			$html[] = '</fieldset>';
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function start_wrap( $container, $output = 'string', $html = array() ) {
		$container->wrap['class'][] = 'field-wrap';
		$container->wrap['class'][] = 'type-' . $container->type . '-wrap';
		if ( in_array( 'fields', $container->blocks ) ) {
			$html[] = '<td colspan="1">';
			$html[] = '<fieldset' . self::attr( $container->wrap ) . '>';
		} elseif ( ! in_array( 'table', $container->blocks ) ) {
			$container->wrap['colspan'] = 2;
			$html[] = '<td' . self::attr( $container->wrap ) . '>';
		}

		$html = apply_filters( self::plugin . '/fields/field-start_wrap', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function end_wrap( $container, $output = 'string', $html = array() ) {
		if ( in_array( 'actions', $container->blocks ) ) {
			$html[] = self::actions( $container );
		}

		if ( in_array( 'fields', $container->blocks ) && ! in_array( 'table', $container->blocks ) ) {
			$html[] = '</fieldset>';
		}

		if ( in_array( 'description', $container->blocks ) ) {
			$html[] = self::description( $container );
		}
		if ( ! in_array( 'table', $container->blocks ) ) {
			$html[] = '</td>';
		}

		$html = apply_filters( self::plugin . '/fields/field-end_wrap', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function label( $container, $output = 'string', $html = array() ) {
		$is_td = ( false !== strpos( $container->type, 'meta' ) ) || ( false !== strpos( $container->type, 'taxonomy' ) );

		$html[] = '<' . ( $is_td ? 'td' : 'th' ) . ' scope="row" colspan="1">';

		if ( isset( $container->label ) && false !== $container->label ) {
			$html[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . self::id( $container->id ) . '">' . $container->label . '</label>';
		}

		$html[] = '</' . ( $is_td ? 'td' : 'th' ) . '>';

		$html = apply_filters( self::plugin . '/fields/field-label', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function actions( $container, $output = 'string', $html = array() ) {
		if ( empty( $container->actions ) ) {
			return ( 'string' === $output ? '' : array() );
		}

		$html[] = '<div class="' . self::abbr( 'actions' ) . '">';
		foreach ( $container->actions as $action => $label ) {
			$html[] = get_submit_button( $label, 'primary', self::plugin . '[actions][' . $action . ']', false );
		}
		$html[] = '</div>';

		$html = apply_filters( self::plugin . '/fields/field-actions', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function description( $container, $output = 'string', $html = array() ) {
		if ( ! empty( $container->description ) ) {
			$html[] = '<p class="' . self::abbr( 'field-description' ) . '">' . $container->description . '</p>';;
		}

		$html = apply_filters( self::plugin . '/fields/field-description', $html, $container );
		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}


	/******************
	 * Static methods *
	 ******************/

	public static function is_valid_type( $type = false ) {
		// a list of valid field types, to prevent screwy behaviour
		return in_array( $type, apply_filters( self::plugin . '/fields/valid_types', self::$valid_types ) );
	}

	public static function name( $indexes = array() ) {
		return self::plugin . '[' . implode( '][', (array) $indexes ) . ']';
	}

	public static function id( $id = array(), $container = false ) {
		if ( ! is_array( $id ) ) {
			$id = (array) $id;
		}
		if ( $container ) {
			$id[] = 'container';
		}
		return self::plugin . '-field-' . implode( '-', (array) $id );
	}

	public static function attr( $attributes = array(), $html = array() ) {
		if ( is_scalar( $attributes ) ) {
			return false;
		}

		$attributes = (array) $attributes;

		foreach ( $attributes as $key => $value ) {
			if ( is_null( $value ) || false === $value ) {
				continue;
			}

			if ( 'label' === $key ) {
				continue;
			}

			if ( '_' === substr( $key, 0, 1 ) ) {
				$key = substr_replace( $key, 'data-', 0, 1 );
			}

			if ( 'class' === $key && ! is_array( $value ) ) {
				$value = (array) $value;
			}

			$attr = $key;

			if ( ! is_scalar( $value ) ) {
				if ( 'class' === $key ) {
					$value = array_map( array( __CLASS__, 'abbr' ), (array) $value );
					if ( in_array( 'fp-type-button', $value ) ) {
						$value[] = 'button';
					}
					$value = array_map( 'sanitize_html_class', $value );
					$value = implode( ' ', $value );
				} else {
					$value = htmlspecialchars( json_encode( $value ), ENT_QUOTES, 'UTF-8' );
				}
			}
			if ( ! is_bool( $value ) || true !== $value ) {
				$attr .= '="' . $value . '"';
			}

			$html[ $key ] = $attr;
		}

		return ' ' . implode( ' ', $html );
	}

	public static function parse( $field, &$container = null ) {
		if ( is_scalar( $field ) ) {
			if ( ! is_string( $field ) ) {
				return false;
			}

			$field = (object) array(
				'type' => $field,
			);
		} elseif ( is_array( $field ) ) {
			$field = (object) $field;
		}

		if ( ! is_a( $container, __CLASS__ ) ) {
			$container = (object) wp_parse_args( $container, self::$default_container );
		}
		if ( ! isset( $container->id ) ) {
			$container->id = (array) self::abbr( uniqid() );
		}

		$field = (object) wp_parse_args( $field, ( ! empty( $container->field ) ? $container->field : array() ) );

		// Setup Private Attributes (_*)
		if ( isset( $field->_id ) ) {

		} elseif ( empty( $field->id ) ) {
			$field->_id = (array) $container->id;
		} else {
			$field->_id = (array) $field->id;
		}

		if ( isset( $field->_name ) ) {

		} elseif ( ! isset( $field->name ) ) {
			$field->_name = (array) ( isset( $container->field->name ) ? $container->field->name : $field->_id );
		} else {
			$field->_name = (array) $field->name;
		}

		// Setup Public Attributes
		if ( empty( $field->type ) ) {
			$field->type = $container->type;
		}
		$field->_type = $field->type;

		$field->id = self::id( $field->_id );
		$field->name = self::name( $field->_name );

		switch ( $field->type ) {
			case 'heading':
				if ( ! isset( $field->title ) ) {
					$field->title = '';
				}

				if ( ! isset( $field->description ) ) {
					$field->description = '';
				}

				$container->has_label = false;
				$container->blocks = array( 'actions' );
				break;
			case 'meta':
			case 'taxonomy':
				if ( ! isset( $container->label ) ) {
					$container->label = '';
				}

				if ( ! isset( $field->config ) ) {
					$field->config = array();
				}

				if ( ! isset( $field->index ) ) {
					$field->index = 0;
				}

				$container->has_label = false;
				$container->blocks = array( 'actions' );
				break;
			case 'input':
				# code...
				break;
			case 'text':
				if ( empty( $field->size ) ) {
					$field->size = 'medium';
				}
				break;
			case 'number':
				if ( empty( $field->size ) ) {
					$field->size = 'tiny';
				}
				break;
			case 'radio':
			case 'checkbox':
				unset( $field->size );

				if ( ! isset( $field->options ) ) {
					$field->options = array();
				}
				$field->options = (array) $field->options;

				break;
			case 'dropdown':
				if ( isset( $field->multiple ) && $field->multiple ) {
					$field->type = 'hidden';

					if ( isset( $field->{'data-tags'} ) && true === $field->{'data-tags'} && empty( $field->{'data-separator'} ) ) {
						$field->{'data-separator'} = ',';
					}
				} else {
					if ( ! isset( $field->options ) ) {
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

		if ( ! empty( $field->class ) ) {
			$field->class = (array) $field->class;
		}
		$field->class[] = 'field';
		$field->class[] = 'type-' . $field->_type;

		if ( ! empty( $field->size ) ) {
			$field->class[] = 'size-' . $field->size;
		}

		return $field;
	}

	/*****************
	 * Field Methods *
	 *****************/

	public static function type_input( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		$content[] = '<input' . self::attr( $field ) . '/>';

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_button( $field, $container = null, $output = 'string', $html = array() ) {
		return self::type_input( $field, $container, $output, $html );
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
		if ( is_scalar( $field ) ) {
			return false;
		}

		$content[] = '<h3>' . $field->title . '</h3>';

		if ( ! empty( $field->description ) ) {
			$content[] = '<div class="' . self::abbr( 'field-description' ) . '">' . $field->description . '</div>';
		}

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_radio( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		foreach ( $field->options as $opt ) {
			$radio = clone $field;
			if ( isset( $opt['value'] ) ) {
				$radio->_id[] = sanitize_html_class( $opt['value'] );
				$radio->value = $opt['value'];
			}

			if ( ! empty( $opt['class'] ) ) {
				$radio->class = array_merge( $radio->class, (array) $opt['class'] );
			}

			if ( isset( $field->value ) && $field->value === $radio->value ) {
				$radio->checked = true;
			}

			$content[] = self::type_input( $radio, null, 'string', array() );
			$content[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . self::id( $radio->_id ) . '">' . $opt['text'] . '</label><br />';
		}

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_checkbox( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		foreach ( $field->options as $opt ) {
			$checkbox = clone $field;
			if ( isset( $opt['value'] ) ) {
				$checkbox->_id[] = sanitize_html_class( $opt['value'] );
				$checkbox->value = $opt['value'];
			}

			if ( ! empty( $opt['class'] ) ) {
				$checkbox->class = array_merge( $checkbox->class, (array) $opt['class'] );
			}

			if ( isset( $field->value ) && $field->value === $checkbox->value ) {
				$checkbox->checked = true;
			}

			$content[] = self::type_input( $checkbox, null, 'string', array() );
			$content[] = '<label class="' . self::abbr( 'field-label' ) . '" for="' . self::id( $checkbox->_id ) . '">' . $opt['text'] . '</label><br />';
		}

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_dropdown( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		if ( isset( $field->multiple ) && $field->multiple ) {
			$content[] = self::type_input( $field, null, 'string', array() );
		} else {
			$content[] = '<select' . self::attr( $field ) . '>';
			$content[] = '<option></option>';
			foreach ( $field->options as $option ) {
				$option = (array) $option;

				if ( ! isset( $option['value'] ) && isset( $option['id'] ) ) {
					$option['value'] = $option['id'];
				} elseif ( ! isset( $option['value'] ) && isset( $option['ID'] ) ) {
					$option['value'] = $option['ID'];
				} elseif ( ! isset( $option['value'] ) ) {
					$option['value'] = false;
				}

				if ( isset( $field->value ) && $field->value === $option['value'] ) {
					$option['selected'] = true;
				}
				$content[] = '<option' . self::attr( $option ) . '>' . esc_attr( $option['text'] ) . '</option>';
			}
			$content[] = '</select>';
		}

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_range( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		$min = clone $field;
		$min->_id[] = 'min';
		$min->_name[] = 'min';
		$min->type = 'number';
		$min->{'data-type'} = 'min';
		$min->min = 0;

		if ( isset( $field->min ) && is_numeric( $field->min ) ) {
			$min->value = $field->min;
		}

		if ( isset( $field->_max ) && is_numeric( $field->_max ) ) {
			$min->max = $field->_max;
		}

		if ( isset( $field->_min ) && is_numeric( $field->_min ) ) {
			$min->min = $field->_min;
		}

		$min->class = array();

		$min->placeholder = esc_attr__( 'e.g.: 3', 'fakerpress' );
		if ( ! empty( $min->_placeholder_min ) ) {
			$min->placeholder = $min->_placeholder_min;
		}

		$max = clone $field;
		$max->_id[] = 'max';
		$max->_name[] = 'max';
		$max->{'data-type'} = 'max';
		$max->type = 'number';
		$max->min = 0;

		if ( isset( $field->max ) && is_numeric( $field->max ) ) {
			$max->value = $field->max;
		} elseif ( ! isset( $max->_prevent_disable ) || ! $max->_prevent_disable ) {
			$max->disabled = true;
		}

		if ( isset( $field->_max ) && is_numeric( $field->_max ) ) {
			$max->max = $field->_max;
		}

		if ( isset( $field->_min ) && is_numeric( $field->_min ) ) {
			$max->min = $field->_min;
		}

		$max->class = array();

		$max->placeholder = esc_attr__( 'e.g.: 12', 'fakerpress' );
		if ( ! empty( $max->_placeholder_max ) ) {
			$max->placeholder = $max->_placeholder_max;
		}

		$content[] = self::type_input( $min, null, 'string', array() );
		$content[] = '<div title="' . esc_attr__( 'To', 'fakerpress' ) . '" class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$content[] = self::type_input( $max, null, 'string', array() );

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_taxonomy( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		$index = clone $field;
		$index->_id[] = 'index';
		$index->_name[] = 'index';
		$index->type = 'button';
		$index->value = '1';
		$index->disabled = true;
		$index->class = array( 'action-order' );

		$remove = clone $field;
		$remove->_id[] = 'remove';
		$remove->_name[] = 'remove';
		$remove->type = 'button';
		$remove->value = '&minus;';
		$remove->class = array( 'action-remove' );

		$duplicate = clone $field;
		$duplicate->_id[] = 'duplicate';
		$duplicate->_name[] = 'duplicate';
		$duplicate->type = 'button';
		$duplicate->deactive = true;
		$duplicate->value = '&plus;';
		$duplicate->class = array( 'action-duplicate' );

		$table = clone $container;
		$table->blocks = array( 'heading', 'table' );
		$table->heads = array(
			array(
				'class' => 'order-table',
				'html' => self::type_button( $index, null, 'string' ),
			),
			array(
				'class' => 'label-table',
				'html' => '',
			),
			array(
				'class' => 'fields-table',
				'html' => '',
			),
			array(
				'html' => self::type_button( $remove, null, 'string' ) . self::type_button( $duplicate, null, 'string' ),
				'class' => 'actions-table',
			),
		);
		$blocks = array(
			array(
				'html' => '',
				'class' => 'order-table',
			),
			'label',
			'fields',
			'description',
			array(
				'html' => '',
				'class' => 'actions-table',
			),
		);

		$tax_container = clone $container;
		$tax_container->id[] = 'taxonomies';
		$tax_container->type .= '_taxonomies';
		$tax_container->label = __( 'Taxonomies', 'fakerpress' );
		$tax_container->description = '';
		$tax_container->blocks = $blocks;

		$taxonomies = get_taxonomies( array( 'public' => true ), 'object' );
		$_json_taxonomies_output = array();
		foreach ( $taxonomies as $key => $taxonomy ) {
			$_json_taxonomies_output[] = array(
				'id' => $taxonomy->name,
				'text' => $taxonomy->labels->name,
			);
		}

		$tax_field = clone $field;
		$tax_field->_id[] = 'taxonomies';
		$tax_field->_name[] = 'taxonomies';
		$tax_field->type = 'dropdown';
		$tax_field->multiple = true;
		$tax_field->{'data-options'} = $_json_taxonomies_output;
		$tax_field->value = 'post_tag, category';
		$tax_field->class = array( 'taxonomies' );
		$tax_field->placeholder = esc_attr__( 'Select Which taxonomies', 'fakerpress' );

		$content[] = $tax_container->build( self::type_dropdown( $tax_field, null, 'string' ) );

		$terms_container = clone $container;
		$terms_container->id[] = 'terms';
		$terms_container->type .= '_terms';
		$terms_container->label = __( 'Terms', 'fakerpress' );
		$terms_container->description = '';
		$terms_container->blocks = $blocks;

		$terms = clone $field;
		$terms->_id[] = 'terms';
		$terms->_name[] = 'terms';
		$terms->type = 'dropdown';
		$terms->multiple = true;
		$terms->description = esc_html__( 'If you do not select any, the plugin will choose from all the existing terms.', 'fakerpress' );
		$terms->{'data-source'} = 'search_terms';

		$terms->placeholder = esc_attr__( 'Which terms can be used', 'fakerpress' );

		$content[] = $terms_container->build( self::type_dropdown( $terms, null, 'string' ) );

		$rate_container = clone $container;
		$rate_container->id[] = 'rate';
		$rate_container->type .= 'rate';
		$rate_container->label = esc_html__( 'Rate', 'fakerpress' );
		$rate_container->description = esc_html__( 'Percentage rate of posts that will have terms generated for the amount below', 'fakerpress' );
		$rate_container->blocks = $blocks;

		$rate = clone $field;
		$rate->_id[] = 'rate';
		$rate->_name[] = 'rate';
		$rate->type = 'number';
		$rate->placeholder = esc_attr__( 'Rate', 'fakerpress' );
		$rate->min = 0;
		$rate->max = 100;
		$rate->value = 85;

		$content[] = $rate_container->build( self::type_text( $rate, null, 'string' ) );

		$qty_container = clone $container;
		$qty_container->id[] = 'qty';
		$qty_container->type .= '_qty';
		$qty_container->label = esc_html__( 'Quantity', 'fakerpress' );
		$qty_container->description = __( 'How many terms will be selected. <br> E.g.: From 1 to 4 or just fill the first field for an exact number', 'fakerpress' );
		$qty_container->blocks = $blocks;

		$qty = clone $field;
		$qty->_id[] = 'qty';
		$qty->_name[] = 'qty';
		$qty->type = 'range';
		$qty->min = 1;
		$qty->max = 4;
		$qty->_max = 200;
		$qty->class = array( 'qty' );

		$content[] = $qty_container->build( self::type_range( $qty, null, 'string' ) );

		$content = $table->build( $content );

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_meta( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		$index = clone $field;
		$index->_id[] = 'index';
		$index->_name[] = 'index';
		$index->type = 'button';
		$index->value = $field->index + 1;
		$index->disabled = true;
		$index->class = array( 'action-order' );

		$remove = clone $field;
		$remove->_id[] = 'remove';
		$remove->_name[] = 'remove';
		$remove->type = 'button';
		$remove->value = '&minus;';
		$remove->class = array( 'action-remove' );

		$duplicate = clone $field;
		$duplicate->_id[] = 'duplicate';
		$duplicate->_name[] = 'duplicate';
		$duplicate->type = 'button';
		$duplicate->deactive = true;
		$duplicate->value = '&plus;';
		$duplicate->class = array( 'action-duplicate' );

		$table = clone $container;
		$table->blocks = array( 'heading', 'table' );
		$table->heads = array(
			array(
				'class' => 'order-table',
				'html' => self::type_button( $index, null, 'string' ),
			),
			array(
				'class' => 'label-table',
				'html' => '',
			),
			array(
				'class' => 'fields-table',
				'html' => '',
			),
			array(
				'html' => self::type_button( $remove, null, 'string' ) . self::type_button( $duplicate, null, 'string' ),
				'class' => 'actions-table',
			),
		);

		$container_blocks = array(
			array(
				'html' => '',
				'class' => 'order-table',
			),
			'label',
			'fields',
			array(
				'html' => '',
				'class' => 'actions-table',
			),
		);

		// Store the Configuration
		$configuration = $field->config;

		// Prevents Unwanted Attributes
		unset( $field->config );

		// Makes Sure we at least have some configuration to avoid bugs
		if ( empty( $configuration ) ) {
			$configuration = array( true );
		}

		foreach ( $configuration as $index => $config ) {
			$type = clone $field;
			$type->_id[] = 'type';
			$type->_name[] = 'type';
			$type->type = 'dropdown';
			$type->options = self::get_meta_types();
			$type->class = array( 'meta_type' );
			$type->placeholder = esc_attr__( 'Select a Field type', 'fakerpress' );
			if ( isset( $config['type'] ) ) {
				$type->value = $config['type'];
			}

			$name = clone $field;
			$name->_id[] = 'name';
			$name->_name[] = 'name';
			$name->type = 'text';
			$name->class = array( 'meta_name' );
			$name->placeholder = esc_attr__( 'Newborn Meta needs a Name, E.g.: _new_image', 'fakerpress' );
			if ( isset( $config['name'] ) ) {
				$name->value = $config['name'];
			}

			$containers = (object) array();

			$containers->type = clone $container;
			$containers->type->id[] = 'type';
			$containers->type->type .= '_type';
			$containers->type->label = __( 'Type', 'fakerpress' );
			$containers->type->description = __( 'Select a type of the Meta Field', 'fakerpress' );
			$containers->type->class = array( 'meta_type-container' );
			$containers->type->blocks = $container_blocks;

			$containers->name = clone $container;
			$containers->name->id[] = 'name';
			$containers->name->type .= '_name';
			$containers->name->label = __( 'Name', 'fakerpress' );
			$containers->name->description = __( 'Select the name for Meta Field', 'fakerpress' );
			$containers->name->class = array( 'meta_name-container' );
			$containers->name->blocks = $container_blocks;

			$containers->conf = clone $container;
			$containers->conf->id[] = 'conf';
			$containers->conf->type .= '_conf';
			$containers->conf->label = __( 'Configuration', 'fakerpress' );
			$containers->conf->description = __( '', 'fakerpress' );
			$containers->conf->class = array( 'meta_conf-container' );
			$containers->conf->blocks = $container_blocks;
			$containers->conf->attributes = array(
				'data-config' => $config,
			);

			$content[] = array(
				$containers->type->build( self::type_dropdown( $type, null, 'string' ) ),
				$containers->name->build( self::type_text( $name, null, 'string' ) ),
				$containers->conf->build( '' ),
			);

		}

		$content = $table->build( $content );

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		// Creates Templates for generating Meta via JavaScript
		foreach ( $type->options as $key => $ftype ) {
			$is_callable = ( isset( $ftype->template ) && is_callable( $ftype->template ) );
			$html[] = '<script type="text/html" data-rel="' . self::id( $container->id, true ) . '" class="' . self::abbr( 'template-' . $ftype->value ) . '"' . ( $is_callable ? ' data-callable' : '' ) . '>';
			if ( $is_callable ) {
				$html[] = call_user_func_array( $ftype->template, array( $field, $ftype ) );
			}
			$html[] = '</script>';
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function get_meta_types() {
		$types = (object) array();

		$default = (object) array(
			'separator' => array(
				'_id' => array( 'meta', 'separator' ),
				'_name' => array( 'meta', 'separator' ),
				'type' => 'text',
				'size' => 'tiny',
				'class' => array(),
				'value' => ',',
				'placeholder' => __( 'E.g.: , ', 'fakerpress' ),
				'label' => __( 'Separator', 'fakerpress' ),
			),
			'weight' => array(
				'_id' => array( 'meta', 'weight' ),
				'_name' => array( 'meta', 'weight' ),
				'type' => 'number',
				'class' => array(),
				'value' => 90,
				'min' => 0,
				'max' => 100,
				'placeholder' => __( 'E.g.: 55', 'fakerpress' ),
				'label' => __( 'Weight', 'fakerpress' ),
			),
			'qty' => array(
				'_id' => array( 'meta', 'qty' ),
				'_name' => array( 'meta', 'qty' ),
				'type' => 'range',
				'class' => array(),
				'label' => __( 'Quantity', 'fakerpress' ),
			),
		);
		foreach ( $default as $key => $field ) {
			$default->{$key} = (object) $field;
		}

		$types->numbers = array(
			'value' => 'numbers',
			'text' => __( 'Number', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$range = clone $field;
				$range->_id = array( 'meta', 'number' );
				$range->_name = array( 'meta', 'number' );
				$range->type = 'range';
				$range->class = array();
				$range->label = __( 'Range of possible numbers', 'fakerpress' );
				$range->_min = 0;

				$html[] = Field::wrapper( Field::type_range( $range, null, 'string' ), $range );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->wp_query = array(
			'value' => 'wp_query',
			'text' => __( 'WP_Query', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$query = clone $field;
				$query->_id = array( 'meta', 'query' );
				$query->_name = array( 'meta', 'query' );
				$query->type = 'text';
				$query->class = array();
				$query->size = 'large';
				$query->placeholder = __( 'category=2&posts_per_page=10', 'fakerpress' );
				$query->label = __( 'Uses <a href="http://codex.wordpress.org/Class_Reference/WP_Query" target="_blank">WP_Query</a>', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_text( $query, null, 'string' ), $query );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->attachment = array(
			'value' => 'attachment',
			'text' => __( 'Attachment', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$store = clone $field;
				$store->_id = array( 'meta', 'store' );
				$store->_name = array( 'meta', 'store' );
				$store->type = 'dropdown';
				$store->value = 'id';
				$store->options = array(
					array(
						'id' => 'id',
						'text' => esc_attr__( 'Attachment ID' ),
					),
					array(
						'id' => 'url',
						'text' => esc_attr__( 'Attachment URL' ),
					),
				);
				$store->class = array();
				$store->placeholder = __( 'Which value should be saved on the Meta', 'fakerpress' );
				$store->label = __( 'Stored Data', 'fakerpress' );

				$providers = clone $field;
				$providers->_id = array( 'meta', 'provider' );
				$providers->_name = array( 'meta', 'provider' );
				$providers->type = 'dropdown';
				$providers->class = array();
				$providers->multiple = true;
				$providers->placeholder = __( 'Select at least one Provider', 'fakerpress' );
				$providers->label = __( 'Which image services will the generator use?', 'fakerpress' );
				$providers->value = implode( ',', wp_list_pluck( Module\Attachment::get_providers(), 'id' ) );
				$providers->{'data-options'} = Module\Attachment::get_providers();

				$size_width = clone $field;
				$size_width->_id = array( 'meta', 'width' );
				$size_width->_name = array( 'meta', 'width' );
				$size_width->type = 'range';
				$size_width->class = array();
				$size_width->label = __( 'Range of possible width sizes for the image', 'fakerpress' );
				$size_width->_min = 0;
				$size_width->_placeholder_min = esc_attr__( 'e.g.: 350', 'fakerpress' );
				$size_width->_placeholder_max = esc_attr__( 'e.g.: 900', 'fakerpress' );
				$size_width->_prevent_disable = true;

				$size_height = clone $field;
				$size_height->_id = array( 'meta', 'height' );
				$size_height->_name = array( 'meta', 'height' );
				$size_height->type = 'range';
				$size_height->class = array();
				$size_height->label = __( 'Range of possible height sizes for the image', 'fakerpress' );
				$size_height->_min = 0;
				$size_height->_placeholder_min = esc_attr__( 'e.g.: 125', 'fakerpress' );
				$size_height->_placeholder_max = esc_attr__( 'e.g.: 650', 'fakerpress' );
				$size_height->_prevent_disable = true;

				$html[] = Field::wrapper( Field::type_dropdown( $store, null, 'string' ), $store );
				$html[] = Field::wrapper( Field::type_dropdown( $providers, null, 'string' ), $providers );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				// Image Dimensions
				$html[] = Field::wrapper( Field::type_range( $size_width, null, 'string' ), $size_width );
				$html[] = Field::wrapper( Field::type_range( $size_height, null, 'string' ), $size_height );

				return implode( "\r\n", $html );
			},
		);

		$types->elements = array(
			'value' => 'elements',
			'text' => __( 'Elements', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$tags = clone $field;
				$tags->_id = array( 'meta', 'elements' );
				$tags->_name = array( 'meta', 'elements' );
				$tags->type = 'dropdown';
				$tags->multiple = true;
				$tags->{'data-options'} = array();
				$tags->{'data-tags'} = true;
				$tags->class = array();
				$tags->placeholder = __( 'Type all possible elements (Tab or Return)', 'fakerpress' );
				$tags->label = __( '', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_dropdown( $tags, null, 'string' ), $tags );
				$html[] = Field::wrapper( Field::type_range( $default->qty, null, 'string' ), $default->qty );
				$html[] = Field::wrapper( Field::type_text( $default->separator, null, 'string' ), $default->separator );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->letter = array(
			'value' => 'letter',
			'text' => __( 'Letter', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->words = array(
			'value' => 'words',
			'text' => __( 'Words', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {

				$html[] = Field::wrapper( Field::type_range( $default->qty, null, 'string' ), $default->qty );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->text = array(
			'value' => 'text',
			'text' => __( 'Text', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$text = clone $field;
				$text->_id = array( 'meta', 'text_type' );
				$text->_name = array( 'meta', 'text_type' );
				$text->type = 'dropdown';
				$text->options = array(
					array(
						'text' => __( 'Sentences', 'fakerpress' ),
						'value' => 'sentences',
					),
					array(
						'text' => __( 'Paragraphs', 'fakerpress' ),
						'value' => 'paragraphs',
					),
				);
				$text->value = 'paragraphs';
				$text->class = array();

				$separator = clone $default->separator;
				$separator->value = '\n';
				$separator->label = __( 'Separator <b space>&mdash;</b> New Line: "\n"', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_dropdown( $text, null, 'string' ), $text );
				$html[] = Field::wrapper( Field::type_range( $default->qty, null, 'string' ), $default->qty );
				$html[] = Field::wrapper( Field::type_text( $separator, null, 'string' ), $separator );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->html = array(
			'value' => 'html',
			'text' => __( 'HTML', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$elements = clone $field;
				$elements->_id = array( 'meta', 'elements' );
				$elements->_name = array( 'meta', 'elements' );
				$elements->type = 'dropdown';
				$elements->multiple = true;
				$elements->{'data-tags'} = true;
				$elements->{'data-options'} = array_merge( \Faker\Provider\HTML::$sets['header'], \Faker\Provider\HTML::$sets['list'], \Faker\Provider\HTML::$sets['block'], \Faker\Provider\HTML::$sets['self_close'] );
				$elements->value = implode( ',', $elements->{'data-options'} );
				$elements->class = array();
				$elements->label = __( 'HTML Tags, without &lt; or &gt;', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_dropdown( $elements, null, 'string' ), $elements );
				$html[] = Field::wrapper( Field::type_range( $default->qty, null, 'string' ), $default->qty );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->lexify = array(
			'value' => 'lexify',
			'text' => __( 'Lexify', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'text';
				$template->class = array();
				$template->placeholder = __( 'E.g.: John ##??', 'fakerpress' );
				$template->label = __( 'John ##?? <b spacer>&raquo;</b> John 29ze', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_text( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->asciify = array(
			'value' => 'asciify',
			'text' => __( 'Asciify', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'text';
				$template->class = array();
				$template->placeholder = __( 'E.g.: John ****', 'fakerpress' );
				$template->label = __( 'John **** <b spacer>&raquo;</b> John r9"+', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_text( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->regexify = array(
			'value' => 'regexify',
			'text' => __( 'Regexify', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'text';
				$template->class = array();
				$template->placeholder = __( 'E.g.: [A-Z0-9._%+-]+@[A-Z0-9.-]', 'fakerpress' );
				$template->label = __( '[A-Z0-9._%+-]+@[A-Z0-9.-] <b spacer>&raquo;</b> sm0@y8k96a', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_text( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->person = array(
			'value' => 'person',
			'text' => __( 'Person', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'dropdown';
				$template->multiple = true;
				$template->{'data-tags'} = true;
				$template->{'data-options'} = array(
					array(
						'text' => __( 'Title', 'fakerpress' ),
						'value' => '{% title %}',
					),
					array(
						'text' => __( 'First Name', 'fakerpress' ),
						'value' => '{% first_name %}',
					),
					array(
						'text' => __( 'Last Name', 'fakerpress' ),
						'value' => '{% last_name %}',
					),
					array(
						'text' => __( 'Suffix', 'fakerpress' ),
						'value' => '{% suffix %}',
					),
				);
				$template->value = 'title,first_name,last_name,suffix';
				$template->value = '{% title %}|{% first_name %}|{% last_name %}|{% suffix %}';
				$template->{'data-separator'} = '|';
				$template->class = array();
				$template->label = __( 'Name Template', 'fakerpress' );

				$gender = clone $field;
				$gender->_id = array( 'meta', 'gender' );
				$gender->_name = array( 'meta', 'gender' );
				$gender->type = 'radio';
				$gender->options = array(
					array(
						'text' => __( 'Male', 'fakerpress' ),
						'value' => 'male',
					),
					array(
						'text' => __( 'Female', 'fakerpress' ),
						'value' => 'female',
					),
				);
				$gender->value = 'female';
				$gender->class = array();

				$html[] = Field::wrapper( Field::type_dropdown( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_radio( $gender, null, 'string' ), $gender );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->geo = array(
			'value' => 'geo',
			'text' => __( 'Geo Information', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'dropdown';
				$template->multiple = true;
				$template->{'data-tags'} = true;
				$template->{'data-options'} = array(
					array(
						'text' => __( 'Country', 'fakerpress' ),
						'value' => '{% country %}',
					),
					array(
						'text' => __( 'Country Code (e.g.: US)', 'fakerpress' ),
						'value' => '{% country_code %}',
					),
					array(
						'text' => __( 'Country ABBR (e.g.: USA', 'fakerpress' ),
						'value' => '{% country_abbr %}',
					),
					array(
						'text' => __( 'City Prefix', 'fakerpress' ),
						'value' => '{% city_prefix %}',
					),
					array(
						'text' => __( 'City Suffix', 'fakerpress' ),
						'value' => '{% city_suffix %}',
					),
					array(
						'text' => __( 'City', 'fakerpress' ),
						'value' => '{% city %}',
					),
					array(
						'text' => __( 'State', 'fakerpress' ),
						'value' => '{% state %}',
					),
					array(
						'text' => __( 'State Abbr', 'fakerpress' ),
						'value' => '{% state_abbr %}',
					),
					array(
						'text' => __( 'Address', 'fakerpress' ),
						'value' => '{% address %}',
					),
					array(
						'text' => __( 'Secondary Address', 'fakerpress' ),
						'value' => '{% secondary_address %}',
					),
					array(
						'text' => __( 'Building Number', 'fakerpress' ),
						'value' => '{% building_number %}',
					),
					array(
						'text' => __( 'Street Name', 'fakerpress' ),
						'value' => '{% street_name %}',
					),
					array(
						'text' => __( 'Street Address', 'fakerpress' ),
						'value' => '{% street_address %}',
					),
					array(
						'text' => __( 'Postal Code', 'fakerpress' ),
						'value' => '{% postalcode %}',
					),
					array(
						'text' => __( 'Latitude', 'fakerpress' ),
						'value' => '{% latitude %}',
					),
					array(
						'text' => __( 'Longitude', 'fakerpress' ),
						'value' => '{% longitude %}',
					),
				);
				$template->value = '{% latitude %}|,|{% longitude %}';
				$template->{'data-separator'} = '|';
				$template->class = array();
				$template->label = __( 'Address Format', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_dropdown( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->phone = array(
			'value' => 'company',
			'text' => __( 'Company', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'template' );
				$template->_name = array( 'meta', 'template' );
				$template->type = 'dropdown';
				$template->multiple = true;
				$template->{'data-tags'} = true;
				$template->{'data-options'} = array(
					array(
						'text' => __( 'Catch Phrase', 'fakerpress' ),
						'value' => '{% catch_phrase %}',
					),
					array(
						'text' => __( 'BS', 'fakerpress' ),
						'value' => '{% bs %}',
					),
					array(
						'text' => __( 'Company', 'fakerpress' ),
						'value' => '{% company %}',
					),
					array(
						'text' => __( 'Suffix', 'fakerpress' ),
						'value' => '{% suffix %}',
					),
				);
				$template->value = '{% company %}|&nbsp;|{% suffix %}';
				$template->{'data-separator'} = '|';
				$template->class = array();
				$template->label = __( 'Company Format', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_dropdown( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->date = array(
			'value' => 'date',
			'text' => __( 'Date', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$template = clone $field;
				$template->_id = array( 'meta', 'interval' );
				$template->_name = array( 'meta', 'interval' );
				$template->type = 'interval';
				$template->class = array();

				$format = clone $field;
				$format->_id = array( 'meta', 'format' );
				$format->_name = array( 'meta', 'format' );
				$format->type = 'text';
				$format->class = array();
				$format->value = 'Y-m-d H:i:s';
				$format->label = __( 'Date Format <b space>&mdash;</b> See <a href="http://php.net/manual/function.date.php" target="_blank">PHP Date</a>', 'fakerpress' );

				$html[] = Field::wrapper( Field::type_interval( $template, null, 'string' ), $template );
				$html[] = Field::wrapper( Field::type_text( $format, null, 'string' ), $format );
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->timezone = array(
			'value' => 'timezone',
			'text' => __( 'TimeZone', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->email = array(
			'value' => 'email',
			'text' => __( 'Email', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->domain = array(
			'value' => 'domain',
			'text' => __( 'Domain', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->ip = array(
			'value' => 'ip',
			'text' => __( 'IP', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);

		$types->user_agent = array(
			'value' => 'user_agent',
			'text' => __( 'Browser User Agent', 'fakerpress' ),
			'template' => function( $field, $type ) use ( $default ) {
				$html[] = Field::wrapper( Field::type_number( $default->weight, null, 'string' ), $default->weight );

				return implode( "\r\n", $html );
			},
		);
		foreach ( $types as $key => $type ) {
			$types->{$key} = (object) $type;
		}

		return apply_filters( self::plugin . '/fields/meta_types', $types );
	}

	public static function type_interval( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		$min = clone $field;
		$min->_id[] = 'min';
		$min->_name[] = 'min';
		$min->type = 'date';
		$min->{'data-type'} = 'min';
		$min->value = '';
		$min->class = array();
		$min->placeholder = esc_attr__( 'yyyy-mm-dd', 'fakerpress' );

		$max = clone $field;
		$max->_id[] = 'max';
		$max->_name[] = 'max';
		$max->type = 'date';
		$max->{'data-type'} = 'max';
		$max->class = array();
		$max->value = '';
		$max->placeholder = esc_attr__( 'yyyy-mm-dd', 'fakerpress' );

		$interval = clone $field;
		$interval->_id[] = 'name';
		$interval->_name[] = 'name';
		$interval->type = 'dropdown';
		$interval->class = array();
		$interval->{'data-placeholder'} = esc_attr__( 'Select an Interval', 'fakerpress' );
		$interval->options = Dates::get_intervals();

		$content[] = self::type_dropdown( $interval, null, 'string' );
		$content[] = self::type_date( $min, null, 'string' );
		$content[] = '<div class="dashicons dashicons-arrow-right-alt2 dashicon-date" style="display: inline-block;"></div>';
		$content[] = self::type_date( $max, null, 'string' );

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	public static function type_raw( $field, $container = null, $output = 'string', $html = array() ) {
		$field = self::parse( $field, $container );
		if ( is_scalar( $field ) ) {
			return false;
		}

		if ( ! empty( $field->html ) ) {
			$content[] = $field->html;
		} else {
			$content = '';
		}

		if ( is_a( $container, __CLASS__ ) ) {
			$html[] = $container->build( $content );
		} else {
			$html = $content;
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $html );
		} else {
			return $html;
		}
	}

	/*

	public static function type_textarea( $field, $container = null, $output = 'string' ) {
		if ( is_array( $container ) ) {
			$field[] = $this->start_container();
			$field[] = $this->label();
			$field[] = $this->start_wrap();
		}

		$field[] = '<textarea' . $this->attr() . '>' . esc_html( stripslashes( $this->value ) ) . '</textarea>';

		if ( is_array( $container ) ) {
			$field[] = $this->end_wrap();
			$field[] = $this->end_container();
		}

		if ( 'string' === $output ) {
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

		if ( is_array( $container ) ) {
			$field[] = $this->start_container();
			$field[] = $this->label();
			$field[] = $this->start_wrap();
		}

		$field[] = $editor;

		if ( is_array( $container ) ) {
			$field[] = $this->end_wrap();
			$field[] = $this->end_container();
		}

		if ( 'string' === $output ) {
			return implode( "\r\n", $field );
		} else {
			return $field;
		}
	}

	 */

} // end class
