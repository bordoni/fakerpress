<?php
namespace FakerPress\Module;
use FakerPress\Admin;

/**
 * Abstract of a Module Generator.
 * When creating a new module generator you should extend this one using `extends \FakerPress\Module\Base` in order to
 * be make sure we have the needed methods.
 */
abstract class Base {

	public $faker = null;

	public $data = array();

	public $dependencies = array();

	public $provider = null;

	public static $_instances = array();

	public static $flag = 'fakerpress_flag';

	public $page = true;

	public $slug = null;

	public static function instance() {
		$class_name = get_called_class();
		$reflection = new \ReflectionClass( $class_name );
		$slug = strtolower( $reflection->getShortName() );

		if ( ! isset( self::$_instances[ $slug ] ) ) {
			self::$_instances[ $slug ] = new $class_name;
		}

		self::$_instances[ $slug ]->slug = $slug;
		self::$_instances[ $slug ]->data = array();

		return self::$_instances[ $slug ];
	}

	final public function __construct() {
		$class_name = get_called_class();
		$reflection = new \ReflectionClass( $class_name );
		$slug = strtolower( $reflection->getShortName() );

		if ( isset( self::$_instances[ $slug ] ) ){
			// Don't create a new one
			return;
		}
		$this->slug = $slug;

		$this->faker = \Faker\Factory::create();

		self::$flag = apply_filters( 'fakerpress.modules_flag', self::$flag );

		$this->provider = (string) apply_filters( "fakerpress.module.{$this->slug}.provider", $this->provider );

		$this->dependencies = (array) apply_filters( "fakerpress.module.{$this->slug}.dependencies", $this->dependencies );

		// We need to merge the Provider to the Dependecies, so everything is loaded
		if ( ! empty( $this->provider ) ){
			$providers = array_merge( $this->dependencies, (array) $this->provider );
		} else {
			$providers = $this->dependencies;
		}

		foreach ( $providers as $provider_class ) {
			$provider = new $provider_class( $this->faker );
			$this->faker->addProvider( $provider );
		}

		// Execute a method that can be overwritten by the called class
		$this->init();

		if ( $this->page ){
			add_action( 'admin_menu', array( $this, '_action_setup_admin_page' ) );
			add_action( 'fakerpress.view.request.' . $this->page->view, array( &$this, '_action_parse_request' ) );
		}
	}

	public function _action_setup_admin_page() {
		// Prevent any modules with page to be added to menu
		if ( ! $this->page ){
			return;
		}

		Admin::add_menu( $this->page->view, $this->page->title, $this->page->menu, 'manage_options', 10 );
	}

	public function _action_parse_request( $view ) {
		return;
	}

	/**
	 * Method that will be called from the construct which is a final function
	 * @return null
	 */
	abstract public function init();


	/**
	 * Amount of instaces of the module that are allowed to be generated in one single request
	 *
	 * @return int the Variabled amount
	 */
	public function get_amount_allowed() {
		return apply_filters( "fakerpress.module.{$this->slug}.amount_allowed", 15, $this );
	}

	final public function set( $key ) {
		if ( ! is_string( $key ) && ! is_array( $key ) ){
			return null;
		}

		// Allow a bunch of params
		$arguments = func_get_args();

		/**
		 * This allows the following behavior, both will have the same arguments
		 *
		 * $module->set( array( 'post_title', 'post_content' ), false, true );
		 */
		if ( is_array( $key ) ){
			// Remove any non string keys
			$keys = array_filter( $key, 'is_string' );

			foreach ( $keys as $key ) {
				// Set the key
				$arguments[0] = $key;

				// Re-call Set with the string key instead of array
				call_user_func_array( array( $this, 'set' ), $arguments );
			}

			return $this;
		}

		// Remove $key
		array_shift( $arguments );

		$this->data[ $key ] = (object) array(
			'key' => $key,
			'generator' => $key,
			'arguments' => (array) $arguments,
		);

		return $this;
	}

	/**
	 * Use this method to save the fake data to the database
	 *
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	final public function save( $reset = true ) {
		do_action( "fakerpress.module.{$this->slug}.pre_save", $this );

		$data = array();
		foreach ( $this->data as $key => $item ) {
			if ( is_object( $item ) ){
				$data[ $item->key ] = $item->value;
			} else {
				$data[ $key ] = $item;
			}
		}

		$response = apply_filters( "fakerpress.module.{$this->slug}.save", false, $data, $this );

		// @todo Set the flag

		if ( $reset ){
			$this->reset();
		}

		return $response;
	}

	public function reset() {
		$this->data = array();
	}

	/**
	 * Use this method to generate all the needed data
	 * @return array An array of the data generated
	 */
	public function generate() {
		foreach ( $this->data as $name => $item ) {
			$this->data[ $name ]->value = $this->apply( $item );
		}

		return $this;
	}

	protected function apply( $item ) {
		if ( ! isset( $item->generator ) ){
			return reset( $item->arguments );
		}
		return call_user_func_array( array( $this->faker, $item->generator ), ( isset( $item->arguments ) ? (array) $item->arguments : array() ) );
	}
}