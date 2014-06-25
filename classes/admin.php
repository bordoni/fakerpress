<?php
namespace FakerPress;

Class Admin {
	/**
	 * Variable holding the submenus objects
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	protected static $menus = array();

	/**
	 * Variable holding the messages objects
	 *
	 * @since 0.1.2
	 *
	 * @var array
	 */
	protected static $messages = array();

	/**
	 * Variable holding the submenus objects
	 *
	 * @since 0.1.0
	 *
	 * @var object
	 */
	public static $view = null;

	/**
	 * Easier way to determine which method originated the request
	 *
	 * @since 0.1.2
	 *
	 * @var string
	 */
	public static $request_method = 'get';

	/**
	 * Makes it easier to check if is AJAX
	 *
	 * @since 0.1.2
	 *
	 * @var bool
	 */
	public static $is_ajax = false;

	/**
	 * Bool if we are inside of a Plugin request
	 *
	 * @todo Make this work with an AJAX request
	 *
	 * @since 0.1.2
	 *
	 * @var bool
	 */
	public static $in_plugin = false;

	/**
	 * Static method to include all the Hooks for WordPress
	 * There is a safe conditional here, it can only be triggered once!
	 *
	 * @uses add_action
	 * @uses add_filter
	 *
	 * @since 0.1.0
	 *
	 * @return null Construct never returns
	 */
	public function __construct(){
		self::$request_method = strtolower( Filter::super( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) );

		self::$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$page = Filter::super( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		self::$in_plugin = ( ! is_null( $page ) && strtolower( $page ) === Plugin::$slug );

		self::$menus[] = (object) array(
			'view' => 'settings',
			'title' => esc_attr__( 'Settings', 'fakerpress' ),
			'label' => esc_attr__( 'FakerPress', 'fakerpress' ),
			'capability' => 'manage_options',
			'priority' => 0,
		);

		// From this point on we are doing hooks!

		add_action( 'admin_init', array( $this, '_action_set_admin_view' ) );
		add_action( 'admin_notices', array( $this, '_action_admin_notices' ) );

		// When trying to add a menu, make bigger than the default to avoid conflicting index further on
		add_action( 'admin_menu', array( $this, '_action_admin_menu' ), 11 );
		add_action( 'fakerpress.view.start', array( $this, '_action_current_menu_js' ) );

		// Creating information for the plugin pages footer
		add_filter( 'admin_footer_text', array( $this, '_filter_admin_footer_text' ) );
		add_filter( 'update_footer', array( $this, '_filter_update_footer' ), 15 );

		add_filter( 'fakerpress.view', array( $this, '_filter_set_view_title' ) );
		add_filter( 'fakerpress.view', array( $this, '_filter_set_view_action' ) );

		// Allow WordPress
		add_filter( 'fakerpress.messages.allowed_html', array( $this, '_filter_messages_allowed_html' ), 1, 1 );

		// This has to turn to something bigger
		add_action( 'admin_enqueue_scripts', array( $this, '_action_enqueue_ui' ) );
	}

	/**
	 * Creating submenus easier to be extent from outside
	 *
	 * @param string  $view The slug of the View, _GET param
	 * @param string  $title Translatable string for the title of the page
	 * @param string  $label Translatable string that will go on the menu
	 * @param string  $capability WordPress capability that will be required to access this view
	 * @param integer $priority The priority to show this submenu
	 *
	 * @since 0.1.0
	 *
	 */
	public static function add_menu( $view, $title, $label, $capability = 'manage_options', $priority = 10 ){
		$priority = absint( $priority );
		self::$menus[] = (object) array(
			'view' => sanitize_title( $view ),
			'title' => esc_attr( $title ),
			'label' => esc_attr( $label ),
			'capability' => sanitize_title( $capability ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		);

		usort(
			self::$menus,
			function( $a, $b ){
				return $a->priority - $b->priority;
			}
		);
	}

	/**
	 * Creating messages in a standard way
	 *
	 * @param string  $html HTML or text of the message
	 * @param string  $type The type of the Message
	 * @param integer $priority The priority to show this message
	 *
	 * @since 0.1.2
	 *
	 */
	public static function add_message( $html, $type = 'success', $priority = 10 ){
		$priority = absint( $priority );

		/**
		 * @filter fakerpress.messages.allowed_html
		 * @since 0.1.2
		 */
		self::$messages[] = (object) array(
			'html' => wp_kses( wpautop( $html ), apply_filters( 'fakerpress.messages.allowed_html', array() ), array( 'http', 'https' ) ),
			'type' => esc_attr( $type ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		);

		usort(
			self::$messages,
			function( $a, $b ){
				return $a->priority - $b->priority;
			}
		);
	}

	/**
	 * [_action_current_menu_js description]
	 * @param  [type] $view [description]
	 *
	 * @since 0.1.0
	 *
	 * @return [type]       [description]
	 */
	public function _action_current_menu_js( $view ) {
		?>
		<script>
			(function($){
				'use strict';
				var fp = typeof FakerPress === 'object' ? window.FakerPress : {};

				fp.view = {
					name: '<?php echo esc_attr( $view->slug ); ?>',
					default: '<?php echo esc_attr( self::$menus[0]->view ); ?>'
				};

				fp.menu = {
					$container: $('#toplevel_page_fakerpress')
				};

				fp.menu.$main = fp.menu.$container.children('a');
				fp.menu.$items = fp.menu.$container.children('.wp-submenu').children('li').not('.wp-submenu-head');
				fp.menu.$current = fp.menu.$items.filter('.current');

				if ( fp.view.default !== fp.view.name ){
					fp.menu.$current = fp.menu.$items.children('a').filter('[href="admin.php?page=fakerpress&view=' + fp.view.name + '"]');
					fp.menu.$items.filter('.current').removeClass('current');
					fp.menu.$current.parent().addClass('current');
				}
			}(jQuery));
		</script>
		<?php
	}

	/**
	 * [_action_set_admin_view description]
	 *
	 * @since 0.1.0
	 *
	 * @return [type] [description]
	 */
	public function _action_set_admin_view(){
		if ( ! self::$in_plugin ){
			return;
		}

		// Default Page of the plugin
		$view = (object) array(
			'slug' => Filter::super( INPUT_GET, 'view', 'file', self::$menus[0]->view ),
			'path' => null,
		);

		// First we check if the file exists in our plugin folder, otherwhise give the user an error
		if ( ! file_exists( Plugin::path( "view/{$view->slug}.php" ) ) ){
			$view->slug = 'error';
		}

		// Define the path for the view we
		$view->path = Plugin::path( "view/{$view->slug}.php" );

		// Set the Admin::$view
		self::$view = apply_filters( 'fakerpress.view', $view );

		do_action( 'fakerpress.view.request', self::$view );
		do_action( 'fakerpress.view.request.' . self::$view->slug , self::$view );
	}

	/**
	 * Method triggered to add the menu to WordPress administration
	 *
	 * @uses add_menu_page
	 * @uses __
	 * @uses \FakerPress\Plugin::$slug
	 *
	 * @since 0.1.0
	 * @return null Actions do not return
	 */
	public function _action_admin_menu() {
		foreach ( self::$menus as &$menu ) {
			if ( $menu->priority === 0 ) {
				$menu->hook = add_menu_page( $menu->title, $menu->label, $menu->capability, Plugin::$slug, array( &$this, '_include_settings_page' ), 'none' );
			} else {
				$menu->hook = add_submenu_page( Plugin::$slug, $menu->title, $menu->label, $menu->capability, Plugin::$slug . '&view=' . $menu->view, array( &$this, '_include_settings_page' ) );
			}
		}

		// Change the Default Submenu for FakerPress menus
		$GLOBALS['submenu'][ Plugin::$slug ][0][0] = esc_attr__( 'Settings', 'FakerPress' );
	}

	/**
	 * Method triggered to add messages recorded in this request to the admin front-end
	 *
	 * @since 0.1.2
	 * @return null Actions do not return
	 */
	public function _action_admin_notices() {
		foreach ( self::$messages as $k => $message ) {
			$classes = array(
				// Plugin class to give the styling
				'fakerpress-message',
				// This is to use WordPress JS to move them above the h2
				'updated-nag',
			);

			if ( $k === 0 ) {
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
				<div class="<?php echo wp_kses( implode( ' ', $classes ), array() ); ?>"><?php echo wp_kses( $message->html, apply_filters( 'fakerpress.messages.allowed_html', array() ), array( 'http', 'https' ) ); ?></div>
			<?php
		}
	}

	/**
	 * Register and enqueue the WordPress admin UI elements like JavaScript and CSS
	 *
	 * @uses wp_register_style
	 * @uses wp_enqueue_style
	 *
	 * @since 0.1.0
	 *
	 * @return null Actions do not return
	 */
	public function _action_enqueue_ui() {
		// Register a global CSS files
		wp_register_style( 'fakerpress.icon', Plugin::url( 'ui/css/font.css' ), array(), Plugin::version, 'screen' );

		// Enqueue a global CSS files
		wp_enqueue_style( 'fakerpress.icon' );

		if ( ! self::$in_plugin ){
			return;
		}

		// Register the plugin CSS files
		wp_register_style( 'fakerpress.messages', Plugin::url( 'ui/css/messages.css' ), array(), Plugin::version, 'screen' );

		// Register the plugin JS files
		wp_register_script( 'fakerpress.fields', Plugin::url( 'ui/js/fields.js' ), array( 'jquery', 'underscore', 'fakerpress.select2', 'jquery-ui-datepicker' ), Plugin::version, true );

		// Register Vendor Select2
		wp_register_style( 'fakerpress.select2', Plugin::url( 'ui/vendor/select2/select2.css' ), array(), '3.5.0', 'screen' );
		wp_register_style( 'fakerpress.select2-wordpress', Plugin::url( 'ui/vendor/select2/select2-wordpress.css' ), array( 'fakerpress.select2' ), '3.5.0', 'screen' );
		wp_register_script( 'fakerpress.select2', Plugin::url( 'ui/vendor/select2/select2.min.js' ), array( 'jquery' ), '3.5.0', true );

		// Register DatePicker Skins
		wp_register_style( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css', array(), '1.10.1', 'screen' );
		wp_register_style( 'fakerpress.datepicker', Plugin::url( 'ui/css/datepicker.css' ), array( 'jquery-ui' ), Plugin::version, 'screen' );

		// Enqueue DatePicker Skins
		wp_enqueue_style( 'fakerpress.datepicker' );

		// Enqueue plugin CSS
		wp_enqueue_style( 'fakerpress.messages' );

		// Enqueue Vendor Select2
		wp_enqueue_style( 'fakerpress.select2-wordpress' );
		wp_enqueue_script( 'fakerpress.fields' );
	}

	/**
	 * Method to include the settings page, from views folders
	 *
	 * @uses \FakerPress\Filter::super
	 * @uses \FakerPress\Plugin::path
	 * @uses do_action
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function _include_settings_page() {
		$view = self::$view;

		// Execute some actions before including the view, to allow others to hook in here
		// Use these to do stuff related to the view you are working with
		do_action( 'fakerpress.view.start', self::$view );
		do_action( "fakerpress.view.start.{$view->slug}", self::$view );

		// PHP include the view
		include_once self::$view->path;

		// Execute some actions before including the view, to allow others to hook in here
		// Use these to do stuff related to the view you are working with
		do_action( 'fakerpress.view.end', self::$view );
		do_action( "fakerpress.view.end.{$view->slug}", self::$view );
	}


	public function _filter_set_view_action( $view ){
		$view->action = Filter::super( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

		return $view;
	}

	public function _filter_set_view_title( $view ){
		foreach ( self::$menus as $menu ){
			if ( $view->slug !== $menu->view ){
				continue;
			}
			$view->title = $menu->title;
		}

		switch ( $view->slug ) {
			case 'changelog':
				$view->title = esc_attr__( 'Changelog', 'fakerpress' );
				break;

			case 'error':
				$view->title = esc_attr__( 'Not Found (404)', 'fakerpress' );
				break;
		}

		add_filter( 'admin_title', array( $this, '_filter_set_admin_page_title' ), 15, 2 );

		return $view;
	}

	public function _filter_set_admin_page_title( $admin_title, $title ){
		if ( ! self::$in_plugin ){
			return $admin_title;
		}
		$pos = strpos( $admin_title, $title );
		if ( $pos !== false ) {
			$admin_title = substr_replace( $admin_title, sprintf( apply_filters( 'fakerpress.admin_title_base', __( '%s on FakerPress', 'fakerpress' ) ), self::$view->title ), $pos, strlen( $title ) );
		}
		return $admin_title;
	}

	public function _filter_messages_allowed_html() {
		return array(
			'a' => array(
				'class' => array(),
				'href' => array(),
				'title' => array()
			),
			'br' => array(
				'class' => array(),
			),
			'p' => array(
				'class' => array(),
			),
			'em' => array(
				'class' => array(),
			),
			'strong' => array(
				'class' => array(),
			),
			'b' => array(
				'class' => array(),
			),
			'i' => array(
				'class' => array(),
			),
			'ul' => array(
				'class' => array(),
			),
			'ol' => array(
				'class' => array(),
			),
			'li' => array(
				'class' => array(),
			),
		);
	}

	/**
	 * Filter the WordPress Version on plugins pages to display plugin version
	 *
	 * @uses \FakerPress\Filter::super
	 * @uses \FakerPress\Plugin::$slug
	 * @uses __
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function _filter_admin_footer_text( $text ){
		if ( ! self::$in_plugin ){
			return $text;
		}

		/**
		 * @todo Review the links to the Official repository before release
		 */
		return
			'<a target="_blank" href="http://wordpress.org/support/plugin/fakerpress#postform">' . esc_attr__( 'Contact Support', 'fakerpress' ) . '</a>' .
			' | ' .
			str_replace(
				array( '[stars]', '[wp.org]' ),
				array( '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/fakerpress#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>', '<a target="_blank" href="http://wordpress.org/plugins/fakerpress/" >wordpress.org</a>' ),
				__( 'Add your [stars] on [wp.org] to spread the love.', 'fakerpress' )
			);
	}

	/**
	 * Filter the WordPress Version on plugins pages to display the plugin version
	 *
	 * @uses \FakerPress\Filter::super
	 * @uses \FakerPress\Plugin::$slug
	 * @uses \FakerPress\Plugin::admin_url
	 * @uses \FakerPress\Plugin::version
	 * @uses __
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function _filter_update_footer( $text ){
		if ( ! self::$in_plugin ){
			return $text;
		}

		return esc_attr__( 'Version' ) . ': ' . '<a title="' . __( 'View what changed in this version', 'fakerpress' ) . '" href="' . esc_url( Plugin::admin_url( 'view=changelog&version=' . esc_attr( Plugin::version ) ) ) . '">' . esc_attr( Plugin::version ) . '</a>';
	}
}