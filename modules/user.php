<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
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

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 3 );
	}

	public function format_link( $id ) {
		return '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . absint( $id ) . '</a>';
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
		update_post_meta( $user_id, self::$flag, 1 );

		return $user_id;
	}

	public function parse_request( $qty, $request = array() ) {
		if ( is_null( $qty ) ) {
			$qty = Variable::super( INPUT_POST, array( Plugin::$slug, 'qty' ), FILTER_UNSAFE_RAW );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->faker->numberBetween( $min, $max );
		}

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of users to fake...', 'fakerpress' );
		}

		$description_use_html = Variable::super( $request, array( 'use_html' ), FILTER_SANITIZE_STRING, 'off' ) === 'on';
		$description_html_tags = array_map( 'trim', explode( ',', Variable::super( $request, array( 'html_tags' ), FILTER_SANITIZE_STRING ) ) );

		$roles = array_intersect( array_keys( get_editable_roles() ), array_map( 'trim', explode( ',', Variable::super( $request, array( 'roles' ), FILTER_SANITIZE_STRING ) ) ) );
		$metas = Variable::super( $request, array( 'meta' ), FILTER_UNSAFE_RAW );

		$results = array();

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'role', $roles );
			$this->set( 'description', $description_use_html, array( 'elements' => $description_html_tags ) );
			$this->set( 'user_registered', 'yesterday', 'now' );

			$this->set( array(
				'user_login',
				'user_pass',
				'user_nicename',
				'user_url',
				'user_email',
				'display_name',
				'nickname',
				'first_name',
				'last_name',
			) );

			$user_id = $this->generate()->save();

			if ( $user_id && is_numeric( $user_id ) ){
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
		$results = $this->parse_request( null, Variable::super( INPUT_POST, array( Plugin::$slug ), FILTER_UNSAFE_RAW ) );

		if ( ! empty( $results ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'user', 'users', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( array( $this, 'format_link' ), $results ) )
				)
			);
		}
	}
}