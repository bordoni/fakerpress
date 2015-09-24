<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Variable;
use FakerPress\Plugin;


class Term extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
	);

	public $meta = false;

	public $provider = '\Faker\Provider\WP_Term';

	public function init() {
		$this->page = (object) array(
			'menu' => esc_attr__( 'Terms', 'fakerpress' ),
			'title' => esc_attr__( 'Generate Terms', 'fakerpress' ),
			'view' => 'terms',
		);

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 3 );
	}

	public function format_link( $id ) {
		return absint( $id );
	}

	public function do_save( $return_val, $data, $module ) {
		$args = array(
			'description' => $data['description'],
			'parent' => $data['parent_term'],
		);

		$term_object = wp_insert_term( $data['name'], $data['taxonomy'], $args );
		if ( is_wp_error( $term_object ) ) {
			return false;
		}

		$flagged = get_option( 'fakerpress.module_flag.' . $this->slug, array() );

		// Ensure that this option is an Array by reseting the variable.
		if ( ! is_array( $flagged ) ){
			$flagged = array();
		}

		if ( ! isset( $flagged[ $data['taxonomy'] ] ) || ! is_array( $flagged[ $data['taxonomy'] ] ) ){
			$flagged[ $data['taxonomy'] ] = array();
		}
		$flagged[ $data['taxonomy'] ] = array_merge( $flagged[ $data['taxonomy'] ], (array) $term_object['term_id'] );

		// When in posts relating is harder so we store in the Options Table
		update_option( 'fakerpress.module_flag.' . $this->slug, $flagged );

		return $term_object['term_id'];
	}

	public function parse_request( $qty, $request = array() ) {
		if ( is_null( $qty ) ) {
			$qty = Variable::super( INPUT_POST, array( Plugin::$slug, 'qty' ), FILTER_UNSAFE_RAW );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->faker->numberBetween( $min, $max );
		}

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of terms to fake...', 'fakerpress' );
		}

		$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Variable::super( $request, array( 'taxonomies' ), FILTER_SANITIZE_STRING ) ) ) );

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'taxonomy', $taxonomies );
			$this->set( 'name' );
			$this->set( 'description' );
			$this->set( 'parent_term' );

			$results[] = $this->generate()->save();
		}

		$results = array_filter( (array) $results, 'absint' );

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

		if ( is_string( $results ) ) {
			return Admin::add_message( $results, 'error' );
		} else {
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results ),
					_n( 'term', 'terms', count( $results ), 'fakerpress' ),
					implode( ', ', array_map( array( $this, 'format_link' ), $results ) )
				)
			);
		}

	}
}
