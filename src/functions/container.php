<?php

namespace FakerPress;

use lucatume\DI52\ServiceProvider;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * Registers a class as a singleton.
 *
 * Each call to obtain an instance of this class made using the `make( $slug )` function
 * will return the same instance; the instances are built just in time (if not passing an
 * object instance or callback function) and on the first request.
 * The container will call the class `__construct` method on the class (if not passing an object
 * or a callback function) and will try to automagically resolve dependencies.
 *
 * Example use:
 *
 *      singleton( 'fakerpress.foo.admin', FakerPress\Foo\Admin::class );
 *
 *      // some code later...
 *
 *      // class is built here
 *      make( 'fakerpress.foo.admin' )->do_something();
 *
 * Need the class built immediately? Build it and register it:
 *
 *      singleton( 'fakerpress.foo.admin', new FakerPress\Foo\Admin() );
 *
 *      // some code later...
 *
 *      make( 'fakerpress.foo.admin' )->do_something();
 *
 * Need a very custom way to build the class? Register a callback:
 *
 *      singleton( Admin_Class::class, [ FakerPress\Foo\Factory, 'make' ] );
 *
 *      // some code later...
 *
 *      make( Admin_Class::class )->do_something();
 *
 * Or register the methods that should be called on the object after its construction:
 *
 *      singleton( FakerPress\Foo\Admin::class, FakerPress\Foo\Admin::class, [ 'hook', 'register' ] );
 *
 *      // some code later...
 *
 *      // the `hook` and `register` methods will be called on the built instance.
 *      make( FakerPress\Foo\Admin::class )->do_something();
 *
 * The class will be built only once (if passing the class name or a callback function), stored
 * and the same instance will be returned from that moment on.
 *
 * @since 0.6.0
 *
 * @param string                 $slug                The human-readable and catchy name of the class.
 * @param string|object|callable $class               The full class name or an instance of the class
 *                                                    or a callback that will return the instance of the class.
 * @param array|null             $after_build_methods An array of methods that should be called on
 *                                                    the built object after the `__construct` method; the methods
 *                                                    will be called only once after the singleton instance
 *                                                    construction.
 */
function singleton( $slug, $class, array $after_build_methods = null ) {
	Container::init()->singleton( $slug, $class, $after_build_methods );
}


/**
 * Registers a class.
 *
 * Each call to obtain an instance of this class made using the `make( $slug )` function
 * will return a new instance; the instances are built just in time (if not passing an
 * object instance, in that case it will work as a singleton) and on the first request.
 * The container will call the class `__construct` method on the class (if not passing an object
 * or a callback function) and will try to automagically resolve dependencies.
 *
 * Example use:
 *
 *      register( 'fakerpress.some', 'FakerPress\Some' );
 *
 *      // some code later...
 *
 *      // class is built here
 *      $some_one = make( 'fakerpress.some' )->doSomething();
 *
 *      // $some_two !== $some_one
 *      $some_two = make( 'fakerpress.some' )->doSomething();
 *
 * Need the class built immediately? Build it and register it:
 *
 *      register( 'fakerpress.admin.class', new Admin_Class() );
 *
 *      // some code later...
 *
 *      // $some_two === $some_one
 *      // acts like a singleton
 *      $some_one = make( 'fakerpress.some' )->doSomething();
 *      $some_two = make( 'fakerpress.some' )->doSomething();
 *
 * Need a very custom way to build the class? Register a callback:
 *
 *      register( 'fakerpress.some', array( Some_Factory, 'make' ) );
 *
 *      // some code later...
 *
 *      // $some_two !== $some_one
 *      $some_one = make( 'fakerpress.some' )->doSomething();
 *      $some_two = make( 'fakerpress.some' )->doSomething();
 *
 * Or register the methods that should be called on the object after its construction:
 *
 *      singleton( 'fakerpress.admin.class', 'Admin_Class', array( 'hook', 'register' ) );
 *
 *      // some code later...
 *
 *      // the `hook` and `register` methods will be called on the built instance.
 *      make( 'fakerpress.admin.class' )->doSomething();
 *
 * @since  0.6.0
 *
 * @param string                 $slug                The human-readable and catchy name of the class.
 * @param string|object|callable $class               The full class name or an instance of the class
 *                                                    or a callback that will return the instance of the class.
 * @param array|null             $after_build_methods An array of methods that should be called on
 *                                                    the built object after the `__construct` method; the methods
 *                                                    will be called each time after the instance construction.
 */
function register( $slug, $class, array $after_build_methods = null ) {
	Container::init()->bind( $slug, $class, $after_build_methods );
}

/**
 * Returns a ready to use instance of the requested class.
 *
 * Example use:
 *
 *      singleton( 'fakerpress.plugin', 'FakerPress\Plugin' );
 *
 *      // some code later...
 *
 *      make( 'fakerpress.plugin' )->do_something();
 *
 * @since  0.6.0
 *
 * @param string|null $slug_or_class Either the slug of a binding previously registered using `singleton` or
 *                                   `register` or the full class name that should be automagically created or
 *                                   `null` to get the container instance itself.
 *
 * @return mixed|object|Container The instance of the requested class. Please note that the cardinality of
 *                                       the class is controlled registering it as a singleton using `singleton`
 *                                       or `register`; if the `$slug_or_class` parameter is null then the
 *                                       container itself will be returned.
 */
function make( $slug_or_class = null ) {
	return null === $slug_or_class ? Container::init() : Container::init()->make( $slug_or_class );
}

/**
 * Registers a value under a slug in the container.
 *
 * Example use:
 *
 *      set_var( 'fakerpress.url', 'http://example.com' );
 *
 * @since  0.6.0
 *
 * @param string $slug  The human-readable and catchy name of the var.
 * @param mixed  $value The variable value.
 */
function set_var( $slug, $value ) {
	Container::init()->setVar( $slug, $value );
}

/**
 * Returns the value of a registered variable.
 *
 * Example use:
 *
 *      set_var( 'fakerpress.url', 'http://example.com' );
 *
 *      $url = get_var( 'fakerpress.url' );
 *
 * @since  0.6.0
 *
 * @param string $slug    The slug of the variable registered using `set_var`.
 * @param null   $default The value that should be returned if the variable slug
 *                        is not a registered one.
 *
 * @return mixed Either the registered value or the default value if the variable
 *               is not registered.
 */
function get_var( $slug, $default = null ) {
	try {
		$var = Container::init()->getVar( $slug );
	} catch ( \InvalidArgumentException $e ) {
		return $default;
	}

	return $var;
}

/**
 * Returns the value of a registered variable.
 *
 * Example use:
 *
 *      set_var( 'fakerpress.url', 'http://example.com' );
 *
 *      unset_var( 'fakerpress.url' );
 *
 * @since  0.6.0
 *
 * @param string $slug The slug of the variable registered using `unset_var`.
 *
 * @return void
 */
function unset_var( $slug ) {
	try {
		Container::init()->offsetUnset( $slug );
	} catch ( \Exception $e ) {
	}
}

/**
 * Returns the value of a registered variable.
 *
 * Example use:
 *
 *      set_var( 'fakerpress.url', 'http://example.com' );
 *
 *      isset_var( 'fakerpress.url' );
 *
 * @since  0.6.0
 *
 * @param string $slug The slug of the variable checked using `isset_var`.
 *
 * @return boolean  Either the given slug exists.
 */
function isset_var( $slug ) {
	return Container::init()->offsetExists( $slug );
}

/**
 * Registers a service provider in the container.
 *
 * Service providers must implement the `ServiceProviderInterface` interface or extend
 * the `ServiceProvider` class.
 *
 * @see    ServiceProvider
 * @see    ServiceProviderInterface
 *
 * @since  0.6.0
 *
 * @param string $provider_class
 */
function register_provider( $provider_class ) {
	Container::init()->register( $provider_class );
}

/**
 * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
 * bound to `$classOrInterface` and return the value of a call to `$method` method with the call arguments.
 *
 * @since  0.6.0
 *
 * @param string $slug        A class or interface fully qualified name or a string slug.
 * @param string $method      The method that should be called on the resolved implementation with the
 *                            specified array arguments.
 * @param mixed  [$argsN]      (optional) Any number of arguments that will be passed down to the Callback
 *
 * @return callable A PHP Callable based on the Slug and Methods passed
 */
function callback( $slug, $method ) {
	$arguments = func_get_args();
	$is_empty  = 2 === count( $arguments );

	if ( $is_empty ) {
		$callable = Container::init()->callback( $slug, $method );
	} else {
		$callback = Container::init()->callback( 'callback', 'get' );
		$callable = call_user_func_array( $callback, $arguments );
	}

	return $callable;
}
