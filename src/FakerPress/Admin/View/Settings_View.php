<?php

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Module\Comment;
use FakerPress\Module\Post;
use FakerPress\Module\Term;
use FakerPress\Module\User;
use FakerPress\Plugin;
use function FakerPress\get_request_var;
use function FakerPress\make;

/**
 * Class Post_View
 *
 * @since   TBD
 *
 * @package FakerPress\Admin\View
 */
class Settings_View extends Abstract_View {
	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'settings';
	}

	/**
	 * @inheritDoc
	 */
	public static function get_menu_slug(): string {
		return Plugin::$slug;
	}

	/**
	 * @inheritDoc
	 */
	public function is_top_menu(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'FakerPress', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Settings', 'fakerpress' );
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
	public function get_capability_required(): string {
		return 'manage_options';
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu_priority(): int {
		return 0;
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
		$erase_intention = is_string( get_request_var( [ 'fakerpress', 'actions', 'delete' ] ) );
		$erase_check     = in_array( strtolower( get_request_var( [ 'fakerpress', 'erase_phrase' ] ) ), [
			'let it go',
			'let it go!'
		] );

		if ( ! $erase_intention ) {
			return false;
		}

		if ( ! $erase_check ) {
			return Admin::add_message( __( 'The verification to erase the data has failed, you have to let it go...', 'fakerpress' ), 'error' );
		}

		if ( ! current_user_can( $this->get_capability_required() ) ) {
			return Admin::add_message( __( 'You do not have the permissions to let it go!', 'fakerpress' ), 'error' );
		}

		$modules = [ Post::get_slug(), Term::get_slug(), Comment::get_slug(), User::get_slug() ];


		foreach ( $modules as $module_slug ) {
			$module = make( \FakerPress\Module\Factory::class )->get( $module_slug );

			if ( ! $module ) {
				continue;
			}

			$items   = $module::fetch();
			$deleted = $module::delete( $items );
		}

		return Admin::add_message( __( 'All data is gone for good.', 'fakerpress' ), 'success' );
	}
}
