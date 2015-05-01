<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Filter;
use FakerPress\Plugin;

class User extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
	);

	public $provider = '\Faker\Provider\WP_User';

	public function init() {
		$this->page = (object) array(
			'menu' => esc_attr__( 'Users', 'fakerpress' ),
			'title' => esc_attr__( 'Generate Users', 'fakerpress' ),
			'view' => 'users',
		);

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$user_id = wp_insert_user( $params );

		if ( ! is_numeric( $user_id ) ){
			return false;
		}

		// Only set role if needed
		if ( ! is_null( $params['role'] ) ){
			$user = new \WP_User( $user_id );

			// Here we could add in the future the possibility to set multiple roles at once
			$user->set_role( $params['role'] );
		}

		foreach ( $metas as $key => $value ) {
			update_user_meta( $user_id, $key, $value );
		}

		return $user_id;
	}

	public function _action_parse_request( $view ){
		if ( 'post' !== Admin::$request_method || empty( $_POST ) ) {
			return false;
		}

		$nonce_slug = Plugin::$slug . '.request.' . Admin::$view->slug . ( isset( Admin::$view->action ) ? '.' . Admin::$view->action : '' );

		if ( ! check_admin_referer( $nonce_slug ) ) {
			return false;
		}
		// After this point we are safe to say that we have a good POST request
		$meta_module = Meta::instance();

		$qty_min = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'min' ), FILTER_SANITIZE_NUMBER_INT ) );
		$qty_max = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'max' ), FILTER_SANITIZE_NUMBER_INT ) );

		$description_use_html = Filter::super( INPUT_POST, array( 'fakerpress', 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$description_html_tags = array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$roles = array_intersect( array_keys( get_editable_roles() ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'roles' ), FILTER_SANITIZE_STRING ) ) ) );
		$metas = Filter::super( INPUT_POST, array( 'fakerpress', 'meta' ), FILTER_UNSAFE_RAW );

		if ( 0 === $qty_min ){
			return Admin::add_message( sprintf( __( 'Zero is not a good number of %s to fake...', 'fakerpress' ), 'posts' ), 'error' );
		}

		if ( ! empty( $qty_min ) && ! empty( $qty_max ) ){
			$quantity = $this->faker->numberBetween( $qty_min, $qty_max );
		}

		if ( ! empty( $qty_min ) && empty( $qty_max ) ){
			$quantity = $qty_min;
		}

		$results = (object) array();

		for ( $i = 0; $i < $quantity; $i++ ) {
			$this->param( 'role', $roles );
			$this->param( 'description', $description_use_html, array( 'elements' => $description_html_tags ) );
			$this->generate();

			$user_id = $this->save();

			if ( $user_id && is_numeric( $user_id ) ){
				foreach ( $metas as $meta_index => $meta ) {
					$meta_module->object( $user_id, 'user' )->build( $meta['type'], $meta['name'], $meta )->save();
				}
			}
			$results->all[] = $user_id;
		}
		$results->success = array_filter( $results->all, 'absint' );

		if ( ! empty( $results->success ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results->success ),
					_n( 'user', 'users', count( $results->success ), 'fakerpress' ),
					implode( ', ', $results->success )
				)
			);
		}
	}
}