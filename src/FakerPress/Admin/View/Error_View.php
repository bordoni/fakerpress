<?php

namespace FakerPress\Admin\View;

/**
 * Class Post_View
 *
 * @since   TBD
 *
 * @package FakerPress\Admin\View
 */
class Error_View extends Abstract_View {
	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'error';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'Error', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Not Found (404)', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return false;
	}
}
