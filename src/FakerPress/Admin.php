<?php
namespace FakerPress;

class Admin extends Template {
	/**
	 * Variable holding the submenus objects
	 *
	 * @since 0.1.0
	 *
	 * @var array
	 */
	protected static $menus = [];

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
	public function __construct() {
		$this->set_template_origin( Plugin::$instance )
			->set_template_folder( 'src/templates/pages' );

		self::$request_method = strtolower( fp_get_global_var( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) );

		self::$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$page = fp_get_global_var( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		self::$in_plugin = ( ! is_null( $page ) && strtolower( $page ) === Plugin::$slug );

		self::$menus[] = (object) [
			'view' => 'settings',
			'title' => esc_attr__( 'Settings', 'fakerpress' ),
			'label' => esc_attr__( 'FakerPress', 'fakerpress' ),
			'capability' => 'manage_options',
			'priority' => 0,
		];

		// From this point on we are doing hooks!
		add_action( 'init', [ $this, '_action_setup_modules' ] );
		add_action( 'admin_init', [ $this, '_action_set_admin_view' ] );
		add_action( 'admin_body_class', [ $this, '_filter_body_class' ] );
		add_action( 'admin_notices', [ $this, '_action_admin_notices' ] );
		add_action( 'fakerpress.view.request.settings', [ $this, '_action_setup_settings_page' ] );

		// Setup the Submenu using an PHP action based on View
		add_filter( 'parent_file', [ $this, '_filter_parent_file' ] );

		// When trying to add a menu, make bigger than the default to avoid conflicting index further on
		add_action( 'admin_menu', [ $this, '_action_admin_menu' ], 11 );

		// Creating information for the plugin pages footer
		add_filter( 'admin_footer_text', [ $this, '_filter_admin_footer_text' ] );
		add_filter( 'update_footer', [ $this, '_filter_update_footer' ], 15 );

		add_filter( 'fakerpress.view', [ $this, '_filter_set_view_title' ] );
		add_filter( 'fakerpress.view', [ $this, '_filter_set_view_action' ] );

		// Allow WordPress
		add_filter( 'fakerpress.messages.allowed_html', [ $this, '_filter_messages_allowed_html' ], 1, 1 );

		// Register assets
		$this->register_assets();
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
	public static function add_menu( $view, $title, $label, $capability = 'manage_options', $priority = 10 ) {
		$priority = absint( $priority );
		self::$menus[] = (object) [
			'view' => sanitize_title( $view ),
			'title' => esc_attr( $title ),
			'label' => esc_attr( $label ),
			'capability' => sanitize_title( $capability ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		];

		usort( self::$menus, 'fp_sort_by_priority' );
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
	public static function add_message( $html, $type = 'success', $priority = 10 ) {
		$priority = absint( $priority );

		/**
		 * @filter fakerpress.messages.allowed_html
		 * @since 0.1.2
		 */
		self::$messages[] = (object) [
			'html' => wp_kses( wpautop( $html ), apply_filters( 'fakerpress.messages.allowed_html', [] ), [ 'http', 'https' ] ),
			'type' => esc_attr( $type ),
			'priority' => $priority === 0 ? $priority + 1 : $priority,
		];

		usort( self::$messages, 'fp_sort_by_priority' );
	}

	/**
	 * Creates the whole view inside of the FakerPress Administration object
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function _action_set_admin_view() {
		if ( ! self::$in_plugin ){
			return;
		}

		// Default Page of the plugin
		$view = (object) [
			'slug' => fp_get_global_var( INPUT_GET, 'view', 'file', self::$menus[0]->view ),
			'path' => null,
		];

		// First we check if the file exists in our plugin folder, otherwhise give the user an error
		if ( ! file_exists( Plugin::path( "src/templates/pages/{$view->slug}.php" ) ) ){
			$view->slug = 'error';
		}

		// Define the path for the view we
		$view->menu = null;

		// Define Menu when possible
		foreach ( self::$menus as &$menu ) {
			if ( $menu->view !== $view->slug ){
				continue;
			}
			$view->menu = $menu;
		}

		// Set the Admin::$view
		self::$view = apply_filters( 'fakerpress.view', $view );

		do_action( 'fakerpress.view.request', self::$view );
		do_action( 'fakerpress.view.request.' . self::$view->slug, self::$view );
	}


	/**
	 * Filter the `$submenu_file` global right before WordPress builds the Administration Menu
	 *
	 * Note: This has nothing to do with `$parent_file`, this is the closest to `_wp_menu_output` execution we can get
	 *
	 * @since  0.4.0
	 *
	 * @param  string $parent_file This doesn't Matter
	 * @return string              We never touch this variable
	 */
	public function _filter_parent_file( $parent_file ) {
		if ( ! self::$in_plugin ){
			return $parent_file;
		}
		global $submenu_file;

		if (
			( is_null( self::$view->menu ) && 'error' !== self::$view->slug ) ||
			( 'error' === self::$view->slug || 0 !== self::$view->menu->priority )
		){
			$submenu_file = Plugin::$slug . '&view=' . self::$view->slug;
		}

		return $parent_file;
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
			if ( ! current_user_can( $menu->capability ) ){
				continue;
			}

			if ( 0 === $menu->priority ) {
				$menu->hook = add_menu_page( $menu->title, $menu->label, $menu->capability, Plugin::$slug, [ &$this, '_include_settings_page' ], 'none' );
			} else {
				$menu->hook = add_submenu_page( Plugin::$slug, $menu->title, $menu->label, $menu->capability, Plugin::$slug . '&view=' . $menu->view, [ &$this, '_include_settings_page' ] );
			}
		}

		// Change the Default Submenu for FakerPress menus
		if ( ! empty( $GLOBALS['submenu'][ Plugin::$slug ] ) ){
			$GLOBALS['submenu'][ Plugin::$slug ][0][0] = esc_attr__( 'Settings', 'fakerpress' );
		}
	}

	/**
	 * Method triggered to add messages recorded in this request to the admin front-end
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
				<div class="<?php echo wp_kses( implode( ' ', $classes ), [] ); ?>"><?php echo wp_kses( $message->html, apply_filters( 'fakerpress.messages.allowed_html', [] ), [ 'http', 'https' ] ); ?></div>
			<?php
		}
	}

	/**
	 * Register and enqueue the WordPress admin UI elements like JavaScript and CSS
	 *
	 * @since 0.5.1
	 *
	 * @return null Actions do not return
	 */
	public function register_assets() {
		$plugin = Plugin::$instance;
		$in_plugin_callback = static function () {
			return self::$in_plugin;
		};

		// Register a global CSS files
		fp_asset(
			$plugin,
			'fakerpress-icon',
			'font.css',
			[],
			'admin_enqueue_scripts',
			[
				'in_footer' => false,
			]
		);

		// Register QS.js
		fp_asset(
			$plugin,
			'fakerpress-qs',
			'vendor/qs.js',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);

		// Register Vendor Select2
		fp_asset(
			$plugin,
			'fakerpress-select2-styles',
			'vendor/select2/select2.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);
		fp_asset(
			$plugin,
			'fakerpress-select2-wordpress',
			'vendor/select2/select2-wordpress.css',
			[ 'fakerpress-select2-styles' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);

		fp_asset(
			$plugin,
			'fakerpress-select2',
			'vendor/select2/select2.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);

		// Register DatePicker Skins
		fp_asset(
			$plugin,
			'fakerpress-jquery-ui',
			'jquery-ui.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);
		fp_asset(
			$plugin,
			'fakerpress-datepicker',
			'datepicker.css',
			[ 'fakerpress-jquery-ui' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);

		// Register the plugin CSS files
		fp_asset(
			$plugin,
			'fakerpress-admin',
			'admin.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);
		fp_asset(
			$plugin,
			'fakerpress-messages',
			'messages.css',
			[],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);

		// Register the plugin JS files
		fp_asset(
			$plugin,
			'fakerpress-fields',
			'fields.js',
			[ 'jquery', 'underscore', 'fakerpress-select2', 'jquery-ui-datepicker' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);
		fp_asset(
			$plugin,
			'fakerpress-module',
			'module.js',
			[ 'jquery', 'underscore', 'fakerpress-qs' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => $in_plugin_callback,
			]
		);
	}

	/**
	 * Method to include the settings page, from views folders
	 *
	 * @uses \FakerPress\Plugin::path
	 * @uses do_action
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function _include_settings_page() {
		// PHP include the view
		$this->render( self::$view->slug, [ 'view' => self::$view ] );
	}

	public function _filter_set_view_action( $view ) {
		$view->action = fp_get_global_var( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		if ( empty( $view->action ) ){
			$view->action = null;
		}

		return $view;
	}

	public function _filter_set_view_title( $view ) {
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

		add_filter( 'admin_title', [ $this, '_filter_set_admin_page_title' ], 15, 2 );

		return $view;
	}

	public function _filter_set_admin_page_title( $admin_title, $title ) {
		if ( ! self::$in_plugin ){
			return $admin_title;
		}
		$pos = strpos( $admin_title, $title );
		if ( false !== $pos ) {
			$admin_title = substr_replace( $admin_title, sprintf( apply_filters( 'fakerpress.admin_title_base', __( '%s on FakerPress', 'fakerpress' ) ), self::$view->title ), $pos, strlen( $title ) );
		}
		return $admin_title;
	}

	public function _filter_messages_allowed_html() {
		return [
			'a' => [
				'class' => [],
				'href' => [],
				'title' => []
			],
			'br' => [
				'class' => [],
			],
			'p' => [
				'class' => [],
			],
			'em' => [
				'class' => [],
			],
			'strong' => [
				'class' => [],
			],
			'b' => [
				'class' => [],
			],
			'i' => [
				'class' => [],
			],
			'ul' => [
				'class' => [],
			],
			'ol' => [
				'class' => [],
			],
			'li' => [
				'class' => [],
			],
		];
	}

	/**
	 * Filter the WordPress Version on plugins pages to display plugin version
	 *
	 * @uses \FakerPress\Plugin::$slug
	 * @uses __
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function _filter_admin_footer_text( $text ) {
		if ( ! self::$in_plugin ){
			return $text;
		}

		return
			'<a target="_blank" href="http://wordpress.org/support/plugin/fakerpress#postform">' . esc_attr__( 'Contact Support', 'fakerpress' ) . '</a> | ' .
			str_replace(
				[ '[stars]', '[wp.org]' ],
				[ '<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/fakerpress#postform" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>', '<a target="_blank" href="http://wordpress.org/plugins/fakerpress/" >wordpress.org</a>' ],
				__( 'Add your [stars] on [wp.org] to spread the love.', 'fakerpress' )
			);
	}

	/**
	 * Filter the WordPress Version on plugins pages to display the plugin version
	 *
	 * @uses \FakerPress\Plugin::$slug
	 * @uses \FakerPress\Plugin::admin_url
	 * @uses \FakerPress\Plugin::VERSION
	 * @uses __
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function _filter_update_footer( $text ) {
		if ( ! self::$in_plugin ){
			return $text;
		}

		$translate = sprintf( '<a class="fp-translations-link" href="%s" title="%s"><span class="dashicons dashicons-translation"></span></a>', Plugin::ext_site_url( '/r/translate' ), esc_attr__( 'Help us with Translations for the FakerPress project', 'fakerpress' ) );
		$version = esc_attr__( 'Version', 'fakerpress' ) . ': <a title="' . __( 'View what changed in this version', 'fakerpress' ) . '" href="' . esc_url( Plugin::admin_url( 'view=changelog&version=' . esc_attr( Plugin::VERSION ) ) ) . '">' . esc_attr( Plugin::VERSION ) . '</a>';

		return $translate . $version;
	}

	public function _filter_body_class( $classes ) {
		$more = [
			$classes,
			'__fakerpress',
		];

		return implode( ' ', $more );
	}

	public function _action_setup_modules() {
		if ( ! is_admin() ){
			return;
		}

		Module\Meta::instance();
		Module\Post::instance();
		Module\Attachment::instance();
		Module\Comment::instance();
		Module\Term::instance();
		Module\User::instance();
	}

	public function _action_setup_settings_page( $view ) {
		if ( 'post' !== self::$request_method || empty( $_POST ) ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . self::$view->slug . ( isset( self::$view->action ) ? '.' . self::$view->action : '' );

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}

		// After this point we are safe to say that we have a good POST request
		$erase_intention = is_string( fp_get_global_var( INPUT_POST, [ 'fakerpress', 'actions', 'delete' ], FILTER_UNSAFE_RAW ) );
		$erase_check     = in_array( strtolower( fp_get_global_var( INPUT_POST, [ 'fakerpress', 'erase_phrase' ], FILTER_SANITIZE_STRING ) ), [ 'let it go', 'let it go!' ] );

		if ( ! $erase_intention ){
			return false;
		}

		if ( ! $erase_check ){
			return self::add_message( __( 'The verification to erase the data has failed, you have to let it go...', 'fakerpress' ), 'error' );
		}

		$modules = [ 'post', 'term', 'comment', 'user' ];

		foreach ( $modules as $module ){
			$class_name = '\FakerPress\Module\\' . ucfirst( $module );

			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			$items = call_user_func_array( $class_name . '::fetch', [] );
			$deleted = call_user_func_array( $class_name . '::delete', [ $items ] );
		}

		return self::add_message( __( 'All data is gone for good.', 'fakerpress' ), 'success' );
	}
}