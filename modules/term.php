<?php
namespace FakerPress\Module;

class Term extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
	);

	public $provider = '\Faker\Provider\WP_Term';

	public function save() {
		$args = array(
			'description' => $this->params['description'],
			'parent' => $this->params['parent_term'],
		);

		$term_object = wp_insert_term( $this->params['name'], $this->params['taxonomy'], $args );

		$flagged = get_option( 'fakerpress.module_flag.' . $this->slug , array() );

		// Ensure that this option is an Array by reseting the variable.
		if ( ! is_array( $flagged ) ){
			$flagged = array();
		}

		if ( ! isset( $flagged[ $this->params['taxonomy'] ] ) || ! is_array( $flagged[ $this->params['taxonomy'] ] ) ){
			$flagged[ $this->params['taxonomy'] ] = array();
		}
		$flagged[ $this->params['taxonomy'] ] = array_merge( $flagged[ $this->params['taxonomy'] ], (array) $term_object['term_id'] );

		// When in posts relating is harder so we store in the Options Table
		update_option( 'fakerpress.module_flag.' . $this->slug, $flagged );

		return $term_object;
	}
}
