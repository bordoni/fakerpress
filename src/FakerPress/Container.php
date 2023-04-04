<?php

namespace FakerPress;

/**
 * Class Container.
 *
 * @since 0.6.0
 *
 * Stellar Dependency Injection Container.
 */
class Container extends \lucatume\DI52\Container {

	/**
	 * @since 0.6.0
	 *
	 * @var Container
	 */
	protected static $instance;

	/**
	 * @since 0.6.0
	 *
	 * @return static
	 */
	public static function init() {
		if ( empty( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}
}
