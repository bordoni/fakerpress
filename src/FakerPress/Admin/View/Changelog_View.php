<?php
/**
 * Admin view for the changelog page.
 *
 * @package FakerPress
 */

namespace FakerPress\Admin\View;

/**
 * Admin view for the changelog page.
 *
 * @since 0.6.4
 *
 * @package FakerPress\Admin\View
 */
class Changelog_View extends Abstract_View {
	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'changelog';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'Changelog', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Changelogs', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return false;
	}
}
