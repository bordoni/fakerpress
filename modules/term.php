<?php
namespace FakerPress\Module;
use FakerPress\Admin;
use FakerPress\Filter;
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

		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		$args = array(
			'description' => $params['description'],
			'parent' => $params['parent_term'],
		);

		$term_object = wp_insert_term( $params['name'], $params['taxonomy'], $args );

		$flagged = get_option( 'fakerpress.module_flag.' . $this->slug, array() );

		// Ensure that this option is an Array by reseting the variable.
		if ( ! is_array( $flagged ) ){
			$flagged = array();
		}

		if ( ! isset( $flagged[ $params['taxonomy'] ] ) || ! is_array( $flagged[ $params['taxonomy'] ] ) ){
			$flagged[ $params['taxonomy'] ] = array();
		}
		$flagged[ $params['taxonomy'] ] = array_merge( $flagged[ $params['taxonomy'] ], (array) $term_object['term_id'] );

		// When in posts relating is harder so we store in the Options Table
		update_option( 'fakerpress.module_flag.' . $this->slug, $flagged );

		return $term_object['term_id'];
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
		$qty_min = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'min' ), FILTER_SANITIZE_NUMBER_INT ) );
		$qty_max = absint( Filter::super( INPUT_POST, array( 'fakerpress', 'qty', 'max' ), FILTER_SANITIZE_NUMBER_INT ) );

		$taxonomies = array_intersect( get_taxonomies( array( 'public' => true ) ), array_map( 'trim', explode( ',', Filter::super( INPUT_POST, array( 'fakerpress', 'taxonomies' ), FILTER_SANITIZE_STRING ) ) ) );

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
			$this->param( 'taxonomy', $taxonomies );
			$this->generate();

			$results->all[] = $this->save();
		}

		$results->success = array_filter( $results->all, 'absint' );

		if ( ! empty( $results->success ) ){
			return Admin::add_message(
				sprintf(
					__( 'Faked %d new %s: [ %s ]', 'fakerpress' ),
					count( $results->all ),
					_n( 'term', 'terms', count( $results->all ), 'fakerpress' ),
					implode( ', ', $results->all )
				)
			);
		}
	}
}
