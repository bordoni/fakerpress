<?php

namespace FakerPress\Module;

use function FakerPress\get_request_var;
use function FakerPress\get;
use function FakerPress\make;
use FakerPress\Plugin;
use Faker;
use FakerPress;

class User extends Abstract_Module {

	/**
	 * @inheritDoc
	 */
	protected $dependencies = [
		Faker\Provider\Lorem::class,
		Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
	];

	/**
	 * @inheritDoc
	 */
	protected $provider_class = FakerPress\Provider\WP_User::class;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'users';
	}

	/**
	 * @inheritDoc
	 */
	public function hook(): void {

	}

	/**
	 * @inheritDoc
	 */
	public static function fetch( array $args = [] ): array {
		$defaults = [
			'fields'     => 'ID',
			'meta_query' => [
				[
					'key'   => static::get_flag(),
					'value' => true,
					'type'  => 'BINARY',
				],
			],
		];
		$args     = wp_parse_args( $args, $defaults );

		$query_users = new \WP_User_Query( $args );

		return array_map( 'absint', $query_users->results );
	}

	/**
	 * @inheritDoc
	 */
	public static function delete( $user ) {
		if ( is_array( $user ) ) {
			$deleted = [];

			foreach ( $user as $id ) {
				$id = $id instanceof \WP_User ? $id->ID : $id;

				if ( ! is_numeric( $id ) ) {
					continue;
				}

				$deleted[ $id ] = self::delete( $id );
			}

			return $deleted;
		}

		if ( is_numeric( $user ) ) {
			$user = new \WP_User( $user );
		}

		if ( ! $user instanceof \WP_User || ! $user->exists() ) {
			return false;
		}

		$flag = (bool) get_user_meta( $user->ID, static::get_flag(), true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_user( $user->ID, get_current_user_id() );
	}

	/**
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$user_id = wp_insert_user( $data );

		if ( ! is_numeric( $user_id ) ) {
			return false;
		}

		// Only set role if needed
		if ( ! is_null( $data['role'] ) ) {
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $data['role'] );
		}

		// Flag the Object as FakerPress
		update_user_meta( $user_id, static::get_flag(), 1 );

		return $user_id;
	}

	/**
	 * @inheritDoc
	 */
	public function parse_request( $qty, $request = [] ) {
		if ( is_null( $qty ) ) {
			$qty = get_request_var( [ Plugin::$slug, 'qty' ] );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->get_faker()->numberBetween( $min, $max );
		}

		if ( 0 === $qty ) {
			return esc_attr__( 'Zero is not a good number of users to fake...', 'fakerpress' );
		}

		$description_size      = get( $request, 'description_size', [ 1, 5 ] );
		$description_use_html  = get( $request, 'use_html', 'off' ) === 'on';
		$description_html_tags = array_map( 'trim', explode( ',', get( $request, 'html_tags' ) ) );

		$roles = array_intersect( array_keys( get_editable_roles() ), array_map( 'trim', explode( ',', get( $request, 'roles' ) ) ) );
		$metas = get( $request, 'meta', [] );

		$results = [];

		for ( $i = 0; $i < $qty; $i ++ ) {
			$this->set( 'role', $roles );
			$this->set(
				'description',
				$description_use_html,
				[
					'qty'      => $description_size,
					'elements' => $description_html_tags,
				]
			);
			$this->set( 'user_registered', 'yesterday', 'now' );

			$this->set( [
				'user_login',
				'user_pass',
				'user_nicename',
				'user_url',
				'user_email',
				'display_name',
				'nickname',
				'first_name',
				'last_name',
			] );

			$user_id = $this->generate()->save();

			if ( $user_id && is_numeric( $user_id ) ) {
				foreach ( $metas as $meta_index => $meta ) {
					make( Meta::class )->object( $user_id, 'user' )->generate( $meta['type'], $meta['name'], $meta )->save();
				}
			}
			$results[] = $user_id;
		}
		$results = array_filter( $results, 'absint' );

		return $results;
	}
}
