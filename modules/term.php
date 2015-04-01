<?php
namespace FakerPress\Module;

class Term extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
	);

	public $meta = false;

	public $provider = '\Faker\Provider\WP_Term';

	public function init() {
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
}
