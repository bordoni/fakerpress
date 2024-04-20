<?php

namespace FakerPress\Contracts;

use FakerPress\Exceptions\Not_Bound_Exception;

use FakerPress\ThirdParty\lucatume\DI52\Container as DI52_Container;

class Container extends DI52_Container {
	/**
	 * @since 0.6.2
	 *
	 * @var Container
	 */
	protected static Container $instance;

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @since 0.6.2
	 *
	 * @throws Not_Bound_Exception Error while retrieving the entry.
	 *
	 * @param string $id A fully qualified class or interface name or an already built object.
	 *
	 * @return mixed The entry for an id.
	 */
	public function get( $id ) {
		try {
			return parent::get( $id );
		} catch ( \Exception $e ) {
			// Do not chain the previous exception into ours, as it makes the error log confusing.
			throw new Not_Bound_Exception( $e->getMessage(), $e->getCode() );
		}
	}

	/**
	 * Creates the first instance of the container, it should be used for all the subsequent calls.
	 *
	 * @since 0.6.2
	 *
	 * @return Container
	 */
	public static function init(): self {
		if ( empty( self::$instance ) ) {
			self::$instance = new self;
		}

		return static::$instance;
	}
}
