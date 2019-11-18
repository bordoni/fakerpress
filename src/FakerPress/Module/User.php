<?php
namespace FakerPress\Module;

use FakerPress\Admin;
use FakerPress\Variable;
use FakerPress\Plugin;
use FakerPress\Utils;
use Faker;
use FakerPress;

class User extends Base {

	public $dependencies = [
		Faker\Provider\Lorem::class,
		Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
	];

	public $provider = FakerPress\Provider\WP_User::class;

	public function init() {
		$this->page = (object) [
			'menu' => esc_attr__( 'Users', 'fakerpress' ),
			'title' => esc_attr__( 'Generate Users', 'fakerpress' ),
			'view' => 'users',
		];

		add_filter( "fakerpress.module.{$this->slug}.save", [ $this, 'do_save' ], 10, 3 );
	}

	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . absint( $id ) . '</a>';
	}

	/**
	 * Fetches all the FakerPress related Users
	 * @return array IDs of the Users
	 */
	public static function fetch() {
		$query_users = new \WP_User_Query(
			[
				'fields' => 'ID',
				'meta_query' => [
					[
						'key' => self::$flag,
						'value' => true,
						'type' => 'BINARY',
					],
				],
			]
		);

		return array_map( 'absint', $query_users->results );
	}

	/**
	 * Use this method to prevent excluding something that was not configured by FakerPress
	 *
	 * @param  array|int|\WP_User $user The ID for the user or the Object
	 * @return bool
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

		$flag = (bool) get_user_meta( $user->ID, self::$flag, true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_user( $user->ID, get_current_user_id() );
	}


	public function do_save( $return_val, $data, $module ) {
		$user_id = wp_insert_user( $data );

		if ( ! is_numeric( $user_id ) ){
			return false;
		}

		// Only set role if needed
		if ( ! is_null( $data['role'] ) ){
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $data['role'] );
		}

		// Flag the Object as FakerPress
		update_user_meta( $user_id, self::$flag, 1 );

		return $user_id;
	}

	public function parse_request( $qty, $request = [] ) {
		if ( is_null( $qty ) ) {
			$qty = fp_get_global_var( INPUT_POST, [ Plugin::$slug, 'qty' ], FILTER_UNSAFE_RAW );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->faker->numberBetween( $min, $max );
		}

		if ( 0 === $qty ) {
			return esc_attr__( 'Zero is not a good number of users to fake...', 'fakerpress' );
		}

		$description_size = fp_array_get( $request, [ 'description_size' ], FILTER_UNSAFE_RAW, [ 1, 5 ] );
		$description_use_html = Utils::instance()->is_truthy( fp_array_get( $request, [ 'use_html' ], FILTER_SANITIZE_STRING, 'off' ) );
		$description_use_html = fp_array_get( $request, [ 'use_html' ], FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$description_html_tags = array_map( 'trim', explode( ',', fp_array_get( $request, [ 'html_tags' ], FILTER_SANITIZE_STRING ) ) );

		$roles = array_intersect( array_keys( get_editable_roles() ), array_map( 'trim', explode( ',', fp_array_get( $request, [ 'roles' ], FILTER_SANITIZE_STRING ) ) ) );
		$metas = fp_array_get( $request, [ 'meta' ], FILTER_UNSAFE_RAW );

		$results = [];

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'role', $roles );
			$this->set(
				'description',
				$description_use_html,
				[
					'qty' => $description_size,
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
					Meta::instance()->object( $user_id, 'user' )->generate( $meta['type'], $meta['name'], $meta )->save();
				}
			}
			$results[] = $user_id;
		}
		$results = array_filter( $results, 'absint' );

		return $results;
	}

	public function _action_parse_request( $view ) {
		if ( 'post' !== Admin::$request_method || empty( $_POST ) ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}

		// After this point we are safe to say that we have a good POST request
		$results = $this->parse_request( null, fp_get_global_var( INPUT_POST, [ Plugin::$slug ], FILTER_UNSAFE_RAW ) );

		if ( ! empty( $results ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'user', 'users', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( [ $this, 'format_link' ], $results ) )
				)
			);
		}
	}
}