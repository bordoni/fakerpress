<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Module\Comment;
use FakerPress\Module\Post;
use FakerPress\Plugin;
use function FakerPress\get_request_var;
use function FakerPress\make;

/**
 * Class Post_View
 *
 * @since   0.6.4
 *
 * @package FakerPress\Admin\View
 */
class Comment_View extends Abstract_View {
	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'comments';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'Comments', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Generate Comments', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return true;
	}

	/**
	 * Format the Administration edit link for each comment instance.
	 *
	 * @since 0.6.0
	 *
	 * @param int $id ID of the post
	 *
	 * @return string
	 */
	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_comment_link( $id ) ) . '">' . absint( $id ) . '</a>';
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
		$results = make( Comment::class )->parse_request( null, get_request_var( Plugin::$slug, [] ) );

		if ( ! empty( $results ) ) {
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'comment', 'comments', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( [ $this, 'format_link' ], $results ) )
				)
			);
		}
	}
}
