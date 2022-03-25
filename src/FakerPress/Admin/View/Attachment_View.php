<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Module\Attachment;
use FakerPress\Plugin;
use function FakerPress\get_request_var;
use function FakerPress\make;

/**
 * Class Attachment_View.
 *
 * @todo This class is not in use yet.
 *
 * @since   0.6.0
 *
 * @package FakerPress\Admin\View
 */
class Attachment_View extends Abstract_View {
	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'attachments';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'Attachments', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Generate Attachments', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function parse_request() {
		// The Abstract just checks for a nonce actually super handy.
		if ( ! parent::parse_request() ) {
			return false;
		}

		// After this point we are safe to say that we have a good POST request
		$results = make( Attachment::class )->parse_request( null, get_request_var( Plugin::$slug, [] ) );

		if ( ! empty( $results ) ) {
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'user', 'users', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( [ $this, 'format_link' ], $results ) )
				)
			);
		}
	}
}
