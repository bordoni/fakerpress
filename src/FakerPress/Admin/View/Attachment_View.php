<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Module\Attachment;
use FakerPress\Plugin;
use FakerPress\Provider\Image\Placeholder;
use FakerPress\Provider\Image\LoremPicsum;
use function FakerPress\get_request_var;
use function FakerPress\make;

/**
 * Class Attachment_View
 *
 * @since TBD
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
		return true;
	}

	/**
	 * @inheritDoc
	 */
	protected function get_page_data(): array {
		return [
			'image_providers' => [
				[
					'value' => Placeholder::ID,
					'label' => 'Placehold.co',
				],
				[
					'value' => LoremPicsum::ID,
					'label' => 'Lorem Picsum',
				],
			],
		];
	}

	/**
	 * Format the Administration edit link for each attachment instance.
	 *
	 * @since TBD
	 *
	 * @param int $id ID of the attachment
	 *
	 * @return string
	 */
	public function format_link( $id ) {
		$url = get_edit_post_link( $id );
		if ( ! $url ) {
			// Fallback to media library link.
			$url = admin_url( 'upload.php?item=' . $id );
		}
		return '<a href="' . esc_url( $url ) . '">' . absint( $id ) . '</a>';
	}

	/**
	 * @inheritDoc
	 */
	public function parse_request() {
		// The Abstract just checks for a nonce actually super handy.
		if ( ! parent::parse_request() ) {
			return;
		}

		// After this point we are safe to say that we have a good POST request
		$results = (array) make( Attachment::class )->parse_request( null, get_request_var( Plugin::$slug, [] ) );

		if ( ! empty( $results ) ) {
			return Admin::add_message(
				sprintf(
					__( 'Faked %1$d new %2$s: [ %3$s ]', 'fakerpress' ),
					count( $results ),
					_n( 'attachment', 'attachments', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( [ $this, 'format_link' ], $results ) )
				)
			);
		}
	}
}
