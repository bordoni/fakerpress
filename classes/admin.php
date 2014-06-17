<?php
namespace FakerPress;

Class Admin {
	/**
	 * Variable holding the submenus objects
	 * @var array
	 */
	protected static $menus = array();

	/**
	 * Variable holding the submenus objects
	 * @var object
	 */
	public static $view = null;

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
		add_action( 'admin_init', array( $this, '_action_set_admin_view' ) );

		// Add needs to come before `admin_menu`
		add_action( 'init', array( $this, '_add_core_submenus' ) );

		// When trying to add a menu, make bigger than the default to avoid conflicting index further on
		add_action( 'admin_menu', array( $this, '_action_admin_menu' ), 11 );
		add_action( 'fakerpress.before_view', array( $this, '_action_current_menu_js' ) );

		self::$menus[] = (object) array(
			'view' => 'settings',
			'title' => esc_attr__( 'Settings', 'fakerpress' ),
			'label' => esc_attr__( 'FakerPress', 'fakerpress' ),
			'capability' => 'manage_options',
			'priority' => 0,
		);

		// Creating information for the plugin pages footer
		add_filter( 'admin_footer_text', array( $this, '_filter_admin_footer_text' ) );
		add_filter( 'update_footer', array( $this, '_filter_update_footer' ), 15 );

		add_filter( 'fakerpress.view', array( $this, '_filter_set_view_title' ) );

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

		usort( self::$menus, '\FakerPress\Admin::_sort_priority' );
	}

	public static function _sort_priority( $a, $b ){
		return $a->priority - $b->priority;
	}

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

	public function _action_set_admin_view(){
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
	 *
	 */
	public function _add_core_submenus(){
		self::add_menu( 'users', __( 'Users', 'fakerpress' ), __( 'Users', 'fakerpress' ), 'manage_options', 10 );
		self::add_menu( 'terms', __( 'Terms', 'fakerpress' ), __( 'Terms', 'fakerpress' ), 'manage_options', 10 );
		self::add_menu( 'posts', __( 'Posts', 'fakerpress' ), __( 'Posts', 'fakerpress' ), 'manage_options', 10 );
		self::add_menu( 'comments', __( 'Comments', 'fakerpress' ), __( 'Comments', 'fakerpress' ), 'manage_options', 10 );
	}

	/**
	 * Register and enqueue the WordPress admin UI elements like JavaScript and CSS
	 *
	 * @uses wp_register_style
	 * @uses wp_enqueue_style
	 * @uses \FakerPress\Plugin::url
	 * @uses \FakerPress\Plugin::version
	 *
	 * @since 0.1.0
	 *
	 * @return null Actions do not return
	 */
	public function _action_enqueue_ui() {
		wp_register_style( 'fakerpress.icon', Plugin::url( 'ui/font.css' ), array(), Plugin::version, 'screen' );

		wp_enqueue_style( 'fakerpress.icon' );
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
		do_action( 'fakerpress.before_view', self::$view );
		do_action( "fakerpress.before_view.{$view->slug}", self::$view );

		// PHP include the view
		include_once self::$view->path;

		// Execute some actions before including the view, to allow others to hook in here
		// Use these to do stuff related to the view you are working with
		do_action( 'fakerpress.after_view', self::$view );
		do_action( "fakerpress.after_view.{$view->slug}", self::$view );
	}


	public function _filter_set_view_title( $view ){
		foreach ( self::$menus as $menu ){
			if ( $view->slug !== $menu->view ){
				continue;
			}
			$view->title = $menu->title;
		}

		add_filter( 'admin_title', array( $this, '_filter_set_admin_page_title' ), 15, 2 );

		return $view;
	}

	public function _filter_set_admin_page_title( $admin_title, $title ){
		$pos = strpos( $admin_title, $title );
		if ( $pos !== false ) {
			$admin_title = substr_replace( $admin_title, sprintf( apply_filters( 'fakerpress.admin_title_base', __( '%s on FakerPress', 'fakerpress' ) ), self::$view->title ), $pos, strlen( $title ) );
		}
		return $admin_title;
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
		$page = Filter::super( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( is_null( $page ) || strtolower( $page ) !== Plugin::$slug ){
			return $text;
		}

		/**
		 * @todo Review the links to the Official repository before release
		 */
		return
			'<a target="_blank" href="http://wordpress.org/support/plugin/fakerpress#postform">' . __( 'Contact Support', 'fakerpress' ) . '</a>' .
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
		$page = Filter::super( INPUT_GET, 'page', FILTER_SANITIZE_STRING );
		if ( is_null( $page ) || strtolower( $page ) !== Plugin::$slug ){
			return $text;
		}

		return __( 'Version' ) . ': ' . '<a title="' . __( 'View what changed in this version', 'fakerpress' ) . '" href="' . Plugin::admin_url( 'view=changelog&version=' . Plugin::version ) . '">' . Plugin::version . '</a>';
	}
}