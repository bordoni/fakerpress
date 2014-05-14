<?php
namespace FakerPress;

Class Admin {
	/**
	 * Variable holding the menu reference
	 * @var string|null
	 */
	public static $menu = null;

	/**
	 * Variable holding the submenus objects
	 * @var array
	 */
	public static $submenus = array();

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
		// When trying to add a menu, make bigger than the default to avoid conflicting index further on
		add_action( 'admin_menu', array( $this, '_action_admin_menu' ), 11 );
		add_action( 'fakerpress.view', array( $this, '_action_current_menu_js' ) );

		// Creating information for the plugin pages footer
		add_filter( 'admin_footer_text', array( $this, '_filter_admin_footer_text' ) );
		add_filter( 'update_footer', array( $this, '_filter_update_footer' ), 15 );

		add_action( 'admin_enqueue_scripts', array( $this, '_action_enqueue_ui' ) );
	}

	public static function add_submenu( $view, $title, $label, $capability = 'manage_options', $priority = 10 ){
		self::$submenus[] = (object) array(
			'view' => $view,
			'title' => $title,
			'label' => $label,
			'capability' => $capability,
			'priority' => $priority,
		);
	}

	public function _action_current_menu_js( $view ) {
		?>
		<script>
			(function($){
				var fakerpress_menu_items = $('#toplevel_page_fakerpress').children('.wp-submenu').children('li').not('.wp-submenu-head'),
					fakerpress_menu_new_current = fakerpress_menu_items.children('a').filter('[href="admin.php?page=fakerpress&view=<?php echo esc_attr( $view->slug ); ?>"]');
				if ( fakerpress_menu_new_current.length !== 0 ){
					fakerpress_menu_items.filter('.current').removeClass('current');
					fakerpress_menu_new_current.parent().addClass('current');
				}
			}(jQuery))
		</script>
		<?php
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
		$menu_id    = add_menu_page( __( 'FakerPress Administration', 'fakerpress' ), __( 'FakerPress', 'fakerpress' ), 'manage_options', Plugin::$slug, array( &$this, '_include_settings_page' ), 'div' );
		self::$menu = Plugin::$slug . '-wpmenu';

		self::add_submenu( 'posts', __( 'Create Fake Posts', 'fakerpress' ), __( 'Posts', 'fakerpress' ), 'manage_options', 10 );

		foreach ( self::$submenus as $submenu ) {
			add_submenu_page( Plugin::$slug, esc_attr( $submenu->title ), esc_attr( $submenu->label ), $submenu->capability, Plugin::$slug . '&view=' . $submenu->view, array( &$this, '_include_settings_page' ) );
		}

		// Change the Default Submenu for FakerPress menus
		$GLOBALS['submenu'][ Plugin::$slug ][0][0] = esc_attr__( 'Settings', 'FakerPress' );
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
		// Default Page of the plugin
		$view = (object) array(
			'slug' => Filter::super( INPUT_GET, 'view', FILTER_SANITIZE_FILE, 'settings' ),
			'path' => null,
		);

		// First we check if the file exists in our plugin folder, otherwhise give the user an error
		if ( ! file_exists( Plugin::path( "view/{$view->slug}.php" ) ) ){
			$view->slug = 'error';
		}

		// Define the path for the view we
		$view->path = Plugin::path( "view/{$view->slug}.php" );

		// Execute some actions before including the view, to allow others to hook in here
		// Use these to do stuff related to the view you are working with
		do_action( 'fakerpress.view', $view );
		do_action( "fakerpress.view.{$view->slug}", $view );

		// PHP include the view
		include_once $view->path;
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