<?php
namespace FakerPress\Provider;

use Faker\Provider\Base;
use FakerPress;
use FakerPress\Utils;
use function FakerPress\make;

class WP_Term extends Base {
	public function name( $qty = 4 ) {
		$qty = make( Utils::class )->get_qty_from_range( $qty );
		$name = $this->generator->sentence( $qty );

		// This removes the last dot on the end of the sentence
		$name = rtrim( $name, '.' );

		return $name;
	}

	public function taxonomy( $taxonomies = [ 'category', 'post_tag' ], $args = [] ) {
		if ( empty( $taxonomies ) ){
			// Merge the returned terms to those provided
			$taxonomies = get_taxonomies( $args, 'names' );
		}

		return $this->generator->randomElement( (array) $taxonomies );
	}

	public function description( $min = 5, $max = 50 ) {
		if ( is_array( $min ) ){
			$description = $this->generator->randomElement( $min );
		} else {
			// Not sure if this is the best approach, but it will work no metter what...
			if ( ! is_numeric( $min ) ){
				$min = 5;
			}
			if ( ! is_numeric( $max ) ){
				$max = 50;
			}
			$description = $this->generator->sentence( $this->generator->numberBetween( $min, $max ) );

			// This removes the last dot on the end of the sentence
			$description = substr( $description, 0, strlen( $description ) - 1 );
		}

		return $description;
	}

	public function parent_term( $terms = [], $taxonomies = [], $args = [] ) {
		if ( ! empty( $taxonomies ) ){
			// We only need the ids to be returned
			$args['fields'] = 'ids';

			// Merge the returned terms to the one provided
			$terms = array_merge( (array) $terms, get_terms( $taxonomies, $args ) );
		}

		return $this->generator->randomElement( (array) $terms );
	}


	// For now I think we should omit the slug, since it's auto-gen, but we need to figure a way to do it later
	/*
	public function slug(){

		return $slug;
	}
	*/

}
