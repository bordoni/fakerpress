<?php
namespace FakerPress\Module;

/**
 * Abstract of a Module Generator.
 * When creating a new module generator you should extend this one using `extends \FakerPress\Module\Base` in order to
 * be make sure we have the needed methods.
 */
abstract class Base {

	public $faker = null;

	public $params = array();

	public $meta = array();

	public $faked = array();

	public $dependencies = array();

	public $provider = null;

	public static $_instances = array();

	public static $flag = 'fakerpress_flag';

	public $page = true;

	public $slug = null;

	final public static function instance(){
		$class_name = get_called_class();
		$reflection = new \ReflectionClass( $class_name );
		$slug = strtolower( $reflection->getShortName() );

		if ( ! isset( self::$_instances[ $slug ] ) ) {
			self::$_instances[ $slug ] = new $class_name;
		}

		self::$_instances[ $slug ]->slug = $slug;
		self::$_instances[ $slug ]->params = array();

		if ( is_array( self::$_instances[ $slug ]->meta ) ){
			self::$_instances[ $slug ]->meta( self::$flag, null, 1 );
		}

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

		if ( ! empty( $this->provider ) ){
			// Create a Reflection of the Provider class to discover all the methods that will fake an Argument
			$provider_reflection = new \ReflectionClass( $this->provider );
			$provider_methods    = $provider_reflection->getMethods();

			// Loop and verify which methods are will be faked on `generate`
			foreach ( $provider_methods as $method ) {
				if ( $provider_reflection->getName() !== $method->class ){
					continue;
				}
				$this->faked[] = $method->name;
			}
		}

		// Execute a method that can be overwritten by the called class
		$this->init();

		// Create a meta with the FakerPress flag, always
		$this->meta( self::$flag, null, 1 );

		if ( $this->page ){
			add_action( 'admin_menu', array( $this, '_action_setup_admin_page' ) );
			add_action( 'fakerpress.view.request.' . $this->page->view, array( &$this, '_action_parse_request' ) );
		}
	}

	/**
	 * Method that will be called from the construct which is a final function
	 * @return null
	 */
	abstract public function init();

	/**
	 * Use this method to save the fake data to the database
	 * @return int|bool|WP_Error Should return an error, or the $wpdb->insert_id or bool for the state
	 */
	final public function save( $reset = true ){
		do_action( "fakerpress.module.{$this->slug}.pre_save", $this );

		$params = array();
		foreach ( $this->params as $key => $param ) {
			if ( is_object( $param ) ){
				$params[ $param->key ] = $param->value;
			} else {
				$params[ $key ] = $param;
			}
		}

		$metas = false;
		if ( is_array( $this->meta ) ){
			$metas = array();
			foreach ( $this->meta as $meta ) {
				$metas[ $meta->key ] = $meta->value;
			}
		}

		$response = apply_filters( "fakerpress.module.{$this->slug}.save", false, $params, $metas, $this );

		if ( $reset ){
			$this->reset();
		}

		return $response;
	}

	public function reset(){
		$this->params = array();
		$this->metas = array();

		// This needs to move away from here
		$this->object_id = 0;
		$this->object_name = 'post';
	}

	public function _action_setup_admin_page(){
		if ( ! $this->page ){
			return;
		}

		\FakerPress\Admin::add_menu( $this->page->view, $this->page->title, $this->page->menu, 'manage_options', 10 );
	}

	public function _action_parse_request( $view ){
		return;
	}

	protected function apply( $item ){
		if ( ! isset( $item->generator ) ){
			return reset( $item->arguments );
		}
		return call_user_func_array( array( $this->faker, $item->generator ), ( isset( $item->arguments ) ? (array) $item->arguments : array() ) );
	}

	final public function meta( $key, $generator = null ){
		// If there is no meta just leave
		if ( ! is_array( $this->meta ) ){
			return false;
		}
		$arguments = func_get_args();
		// Remove $key and $generator
		array_shift( $arguments );
		array_shift( $arguments );

		$this->meta[ $key ] = (object) array(
			'key' => $key,
			'generator' => $generator,
			'arguments' => (array) $arguments,
		);

		return $this->meta[ $key ];
	}

	final public function param( $key, $arguments = array() ){
		$arguments = func_get_args();
		// Remove $key
		array_shift( $arguments );

		$this->params[ $key ] = (object) array(
			'key' => $key,
			'generator' => $key,
			'arguments' => (array) $arguments,
		);

		return $this->params[ $key ];
	}

	/**
	 * Use this method to generate all the needed data
	 * @return array An array of the data generated
	 */
	final public function generate() {
		foreach ( $this->faked as $name ) {
			if ( ! isset( $this->params[ $name ] ) ){
				$this->params[ $name ] = (object) array(
					'key' => $name,
					'generator' => $name,
					'arguments' => array(),
				);
			}
			$this->params[ $name ]->value = $this->apply( $this->params[ $name ] );
		}

		if ( is_array( $this->meta ) ){
			foreach ( $this->meta as $meta ) {
				$this->meta[ $meta->key ]->value = $this->apply( $meta );
			}
		}

		return $this;
	}
}