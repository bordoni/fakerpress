<?php
/**
 * FakerPress admin pages: notices, page titles and footer links.
 *
 * @package FakerPress
 */

namespace FakerPress;

use FakerPress\Admin\View\Factory;

/**
 * Renders the FakerPress admin page templates and filters the admin chrome (notices, title, footer, body class) on FakerPress pages.
 */
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

	/**
	 * Checks whether the current admin request is for a FakerPress page.
	 *
	 * @return bool True when the `page` request variable matches the plugin slug.
	 */
	public function is_active(): bool {
		$page = get_request_var( 'page' );

		return ( ! is_null( $page ) && strtolower( $page ) === Plugin::$slug );
	}

	/**
	 * Points the template loader at the plugin's `src/templates/pages` folder.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->set_template_origin( make( Plugin::class ) )
			->set_template_folder( 'src/templates/pages' );
	}

	/**
	 * Queues an admin notice to show on the current FakerPress page.
	 *
	 * The HTML runs through wpautop() and wp_kses() with the `fakerpress.messages.allowed_html` tags,
	 * and the queue is re-sorted by priority after each addition.
	 *
	 * @todo Move to the Page Abstract.
	 *
	 * @since 0.1.2
	 *
	 * @param string $html     HTML or text of the message.
	 * @param string $type     Notice type: `success`, `error` or `warning`.
	 * @param int    $priority Sort priority, lower shows first; 0 is bumped to 1.
	 *
	 * @return object The queued message, with `html`, `type` and `priority` properties.
	 */
	public static function add_message( $html, $type = 'success', $priority = 10 ) {
		$priority = absint( $priority );

		/**
		 * @filter fakerpress.messages.allowed_html
		 * @since 0.1.2
		 */
		$message = (object) [
			'html'     => wp_kses( wpautop( $html ), apply_filters( 'fakerpress.messages.allowed_html', [] ), [ 'http', 'https' ] ),
			'type'     => esc_attr( $type ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		];

		self::$messages[] = $message;

		usort( self::$messages, 'FakerPress\sort_by_priority' );

		return $message;
	}

	/**
	 * Prints the notices queued during this request as admin notices.
	 *
	 * @todo Move to the Page Abstract.
	 *
	 * @since 0.1.2
	 *
	 * @return void
	 */
	public function _action_admin_notices() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
		foreach ( self::$messages as $k => $message ) {
			$classes = [
				// Plugin class to give the styling.
				'fakerpress-message',
				// This is to use WordPress JS to move them above the h2.
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
			<div class="<?php echo wp_kses( implode( ' ', $classes ), [] ); ?>">
			<?php
			echo wp_kses(
				$message->html,
				apply_filters( 'fakerpress.messages.allowed_html', [] ),
				[
					'http',
					'https',
				] 
			);
			?>
				</div>
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
		return apply_filters(
			'fakerpress.admin.title_base',
			/* translators: %s: Title of the current FakerPress admin page. */
			__( '%s on FakerPress', 'fakerpress' )
		);
	}

	/**
	 * Replaces the page title inside the admin `<title>` with "<view title> on FakerPress" on FakerPress pages.
	 *
	 * Hooked to `admin_title`.
	 *
	 * @todo refactor
	 *
	 * @param string $admin_title The full admin page title.
	 * @param string $title       The original page title, the part that gets replaced.
	 *
	 * @return string The filtered admin page title.
	 */
	public function _filter_set_admin_page_title( $admin_title, $title ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
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

	/**
	 * Lists the HTML tags and attributes allowed in FakerPress admin notices.
	 *
	 * Hooked to `fakerpress.messages.allowed_html`.
	 *
	 * @return array<string, array<string, array>> Allowed tags for wp_kses(): links, line breaks, paragraphs, emphasis and lists.
	 */
	public function _filter_messages_allowed_html() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
		return [
			'a'      => [
				'class' => [],
				'href'  => [],
				'title' => [],
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
	 * Replaces the left admin footer text on FakerPress pages with support and review links.
	 *
	 * Hooked to `admin_footer_text`.
	 *
	 * @since 0.1.0
	 *
	 * @param string $text The default footer text.
	 *
	 * @return string The footer HTML, unchanged outside FakerPress pages.
	 */
	public function _filter_admin_footer_text( $text ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
		if ( ! $this->is_active() ) {
			return $text;
		}

		return '<a target="_blank" href="http://wordpress.org/support/plugin/fakerpress#postform">' . esc_html__( 'Contact Support', 'fakerpress' ) . '</a> | ' .
			str_replace(
				[ '[stars]', '[wp.org]' ],
				[
					'<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/fakerpress#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
					'<a target="_blank" href="http://wordpress.org/plugins/fakerpress/" >wordpress.org</a>',
				],
				__( 'Add your [stars] on [wp.org] to spread the love.', 'fakerpress' )
			);
	}

	/**
	 * Replaces the WordPress version in the right admin footer on FakerPress pages with sponsor,
	 * translation and plugin version links.
	 *
	 * Hooked to `update_footer`.
	 *
	 * @since 0.1.0
	 *
	 * @param string $text The default footer text (the WordPress version).
	 *
	 * @return string The footer HTML, unchanged outside FakerPress pages.
	 */
	public function _filter_update_footer( $text ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
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

	/**
	 * Appends the `__fakerpress` class to the admin body classes.
	 *
	 * Hooked to `admin_body_class`; it runs on every admin page, not only FakerPress ones.
	 *
	 * @param string $classes Space-separated admin body classes.
	 *
	 * @return string The classes with `__fakerpress` appended.
	 */
	public function _filter_body_class( $classes ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore -- Public hook callback name, renaming breaks third-party remove_action()/remove_filter() calls.
		$more = [
			$classes,
			'__fakerpress',
		];

		return implode( ' ', $more );
	}

	/**
	 * Filters the array of row meta for each plugin in the Plugins list table.
	 *
	 * @since 0.6.3
	 *
	 * @param array<int, string> $plugin_meta An array of the plugin's metadata.
	 * @param string             $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @return array<int, string> Updated array of the plugin's metadata.
	 */
	public function filter_plugin_row_meta( array $plugin_meta, $plugin_file ) {
		if ( 'fakerpress/fakerpress.php' !== $plugin_file ) {
			return $plugin_meta;
		}

		$plugin_meta[] = sprintf(
			'<a href="%1$s"><span class="dashicons dashicons-star-filled" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>%2$s</a>',
			'https://fakerpress.com/r/sponsor',
			esc_html_x( 'Sponsor', 'verb', 'fakerpress' )
		);

		return $plugin_meta;
	}
}
