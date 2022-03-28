<?php

namespace FakerPress\Fields;

use function FakerPress\get;

class Raw_Field extends Field_Abstract {
	/**
	 * {@inheritDoc}
	 */
	protected static $slug = 'raw';

	/**
	 * {@inheritDoc}
	 */
	protected $raw_html;

	public function init( array $args = [] ) {
		parent::init( $args );

		$this->set_raw_html( get( $args, 'html' ) );

		return $this;
	}

	public function set_raw_html( $value ) {
		$this->raw_html = $value;
	}

	public function get_raw_html() {
		return $this->raw_html;
	}
}
