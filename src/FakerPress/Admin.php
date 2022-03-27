<?php

namespace FakerPress;

use FakerPress\Admin\Menu;
use FakerPress\Admin\View\Factory;

class Admin extends Template {
	/**
	 * Variable holding the messages objects
	 *
	 * @since 0.1.2
	 *
	 * @var array
	 */
	protected static $messages = [];

	/**
	 * Variable holding the submenus objects
	 *
	 * @since 0.1.0
	 *
	 * @var object
	 */
	public static $view = null;

	/**
	 * Makes it easier to check if is AJAX
	 *
	 * @since 0.1.2
	 *
	 * @var bool
	 */
	public static $is_ajax = false;

	public function is_active(): bool {
		$page = get_request_var( 'page' );

		return ( ! is_null( $page ) && strtolower( $page ) === Plugin::$slug );
	}

	/**
	 * Static method to include all the Hooks for WordPress
	 * There is a safe conditional here, it can only be triggered once!
	 *
	 * @since 0.1.0
	 *
	 * @uses  add_filter
	 *
	 * @uses  add_action
	 * @return null Construct never returns
	 */
	public function __construct() {
		$this->set_template_origin( make( Plugin::class ) )
			 ->set_template_folder( 'src/templates/pages' );
	}

	/**
	 * Creating messages in a standard way
	 *
	 * @todo Move to the Page Abstract.
	 *
	 * @since 0.1.2
	 *
	 * @param string  $type     The type of the Message
	 * @param integer $priority The priority to show this message
	 *
	 * @param string  $html     HTML or text of the message
	 */
	public static function add_message( $html, $type = 'success', $priority = 10 ) {
		$priority = absint( $priority );

		/**
		 * @filter fakerpress.messages.allowed_html
		 * @since  0.1.2
		 */
		self::$messages[] = $message = (object) [
			'html'     => wp_kses( wpautop( $html ), apply_filters( 'fakerpress.messages.allowed_html', [] ), [ 'http', 'https' ] ),
			'type'     => esc_attr( $type ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		];

		usort( self::$messages, 'FakerPress\sort_by_priority' );

		return $message;
	}

	/**
	 * Method triggered to add messages recorded in this request to the admin front-end
	 *
	 * @todo Move to the Page Abstract.
	 *
	 * @since 0.1.2
	 * @return null Actions do not return
	 */
	public function _action_admin_notices() {
		foreach ( self::$messages as $k => $message ) {
			$classes = [
				// Plugin class to give the styling
				'fakerpress-message',
				// This is to use WordPress JS to move them above the h2
				'notice',
			];

			if ( 0 === $k ) {
				$classes[] = 'first';
			}

			if ( $k + 1 === count( self::$messages ) ) {
				$classes[] = 'last';
			}

			switch ( $message->type ) {
				case 'error':
					$classes[] = 'fakerpress-message-error';
					break;
				case 'success':
					$classes[] = 'fakerpress-message-success';
					break;
				case 'warning':
					$classes[] = 'fakerpress-message-warning';
					break;
				default:
					break;
			}

			?>
			<div class="<?php echo wp_kses( implode( ' ', $classes ), [] ); ?>"><?php echo wp_kses( $message->html, apply_filters( 'fakerpress.messages.allowed_html', [] ), [
					'http',
					'https'
				] ); ?></div>
			<?php
		}
	}


	/**
	 * Gets the base title for the admin pages.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public function get_base_title(): string {
		/**
		 * Allows the modification of the base title for FakerPress pages.
		 *
		 * @since 0.6.0
		 *
		 * @param string $title Which base title will be used.
		 */
		return apply_filters( 'fakerpress.admin.title_base', __( '%s on FakerPress', 'fakerpress' ) );
	}

	/**
	 * @todo refactor
	 */
	public function _filter_set_admin_page_title( $admin_title, $title ) {
		if ( ! $this->is_active() ) {
			return $admin_title;
		}
		$view = make( Factory::class )->get_current_view();

		$pos = strpos( $admin_title, $title );
		if ( false !== $pos ) {
			$admin_title = substr_replace( $admin_title, sprintf( $this->get_base_title(), $view->get_title() ), $pos, strlen( $title ) );
		}

		return $admin_title;
	}

	public function _filter_messages_allowed_html() {
		return [
			'a'      => [
				'class' => [],
				'href'  => [],
				'title' => []
			],
			'br'     => [
				'class' => [],
			],
			'p'      => [
				'class' => [],
			],
			'em'     => [
				'class' => [],
			],
			'strong' => [
				'class' => [],
			],
			'b'      => [
				'class' => [],
			],
			'i'      => [
				'class' => [],
			],
			'ul'     => [
				'class' => [],
			],
			'ol'     => [
				'class' => [],
			],
			'li'     => [
				'class' => [],
			],
		];
	}

	/**
	 * Filter the WordPress Version on plugins pages to display plugin version
	 *
	 * @since 0.1.0
	 * @uses  __
	 *
	 * @uses  \FakerPress\Plugin::$slug
	 * @return string
	 */
	public function _filter_admin_footer_text( $text ) {
		if ( ! $this->is_active() ) {
			return $text;
		}

		return
			'<a target="_blank" href="http://wordpress.org/support/plugin/fakerpress#postform">' . esc_attr__( 'Contact Support', 'fakerpress' ) . '</a> | ' .
			str_replace(
				[ '[stars]', '[wp.org]' ],
				[
					'<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/fakerpress#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
					'<a target="_blank" href="http://wordpress.org/plugins/fakerpress/" >wordpress.org</a>'
				],
				__( 'Add your [stars] on [wp.org] to spread the love.', 'fakerpress' )
			);
	}

	/**
	 * Filter the WordPress Version on plugins pages to display the plugin version
	 *
	 * @since 0.1.0
	 * @uses  \FakerPress\Plugin::admin_url
	 * @uses  \FakerPress\Plugin::VERSION
	 * @uses  __
	 *
	 * @uses  \FakerPress\Plugin::$slug
	 * @return string
	 */
	public function _filter_update_footer( $text ) {
		if ( ! $this->is_active() ) {
			return $text;
		}

		$sponsor   = sprintf(
			'<a class="fp-link-footer fp-sponsor-link" href="%2$s" title="%3$s" target="_blank"><span class="dashicons dashicons-money-alt"></span> %1$s</a> | ',
			esc_html__( 'Sponsor the project on GitHub', 'fakerpress' ),
			Plugin::ext_site_url( '/r/sponsor' ),
			esc_attr__( 'Help by sponsoring the Project on GitHub', 'fakerpress' )
		);
		$translate = sprintf(
			'<a class="fp-link-footer fp-translations-link" href="%2$s" title="%3$s" target="_blank"><span class="dashicons dashicons-translation"></span> %1$s</a> | ',
			esc_html__( 'Translate', 'fakerpress' ),
			Plugin::ext_site_url( '/r/translate' ),
			esc_attr__( 'Help us with Translations for the FakerPress project', 'fakerpress' )
		);
		$version   = sprintf(
			'<a class="fp-link-footer fp-version-link" href="%2$s" title="%3$s" target="_blank">%1$s</a>',
			esc_html__( 'Version: ', 'fakerpress' ) . esc_attr( Plugin::VERSION ),
			esc_url( Plugin::admin_url( 'view=changelog&version=' . esc_attr( Plugin::VERSION ) ) ),
			esc_attr__( 'View what changed in this version', 'fakerpress' )
		);

		return $sponsor . $translate . $version;
	}

	public function _filter_body_class( $classes ) {
		$more = [
			$classes,
			'__fakerpress',
		];

		return implode( ' ', $more );
	}
}
