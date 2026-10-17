<?php
/**
 * Faker provider for WordPress term fields.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\Utils;
use function FakerPress\make;

/**
 * Generates the fields of a fake term: name, taxonomy, description and parent.
 */
class WP_Term extends Base {
	/**
	 * Generates a term name: a random sentence without its final full stop.
	 *
	 * @param array|int $qty Number of words, or a [ min, max ] range to pick it from.
	 *
	 * @return string The term name.
	 */
	public function name( $qty = 4 ) {
		$qty  = make( Utils::class )->get_qty_from_range( $qty );
		$name = $this->generator->sentence( $qty );

		// This removes the last dot on the end of the sentence.
		$name = rtrim( $name, '.' );

		return $name;
	}

	/**
	 * Picks a random taxonomy name.
	 *
	 * @param string[]|string $taxonomies Taxonomies to pick from. When empty, every taxonomy matching `$args` is used.
	 * @param array           $args       Arguments for get_taxonomies(), only used when `$taxonomies` is empty.
	 *
	 * @return string The taxonomy name.
	 */
	public function taxonomy( $taxonomies = [ 'category', 'post_tag' ], $args = [] ) {
		if ( empty( $taxonomies ) ) {
			// No taxonomies given, pick from every taxonomy that matches $args.
			$taxonomies = get_taxonomies( $args, 'names' );
		}

		return $this->generator->randomElement( (array) $taxonomies );
	}

	/**
	 * Generates a term description.
	 *
	 * Passing an array as `$min` picks one of its values instead of generating text.
	 *
	 * @param array|int $min Minimum number of words (non-numeric values fall back to 5), or a list of descriptions to pick from.
	 * @param int       $max Maximum number of words (non-numeric values fall back to 50).
	 *
	 * @return string The description, without its final full stop when generated.
	 */
	public function description( $min = 5, $max = 50 ) {
		if ( is_array( $min ) ) {
			$description = $this->generator->randomElement( $min );
		} else {
			// Not sure if this is the best approach, but it will work no metter what...
			if ( ! is_numeric( $min ) ) {
				$min = 5;
			}
			if ( ! is_numeric( $max ) ) {
				$max = 50;
			}
			$description = $this->generator->sentence( $this->generator->numberBetween( $min, $max ) );

			// This removes the last dot on the end of the sentence.
			$description = substr( $description, 0, strlen( $description ) - 1 );
		}

		return $description;
	}

	/**
	 * Picks a random parent term ID.
	 *
	 * @param int[]|int       $terms      Term IDs to pick from.
	 * @param string[]|string $taxonomies Taxonomies whose existing term IDs are added to the pool.
	 * @param array           $args       Extra get_terms() arguments used when `$taxonomies` is set.
	 *
	 * @return int|null The picked term ID, or null when the pool is empty.
	 */
	public function parent_term( $terms = [], $taxonomies = [], $args = [] ) {
		if ( ! empty( $taxonomies ) ) {
			// We only need the ids to be returned.
			$args['fields'] = 'ids';

			// Merge the returned terms to the one provided.
			$terms = array_merge( (array) $terms, get_terms( array_merge( $args, [ 'taxonomy' => $taxonomies ] ) ) );
		}

		return $this->generator->randomElement( (array) $terms );
	}

	// There is no slug() formatter on purpose: WordPress generates the slug from the name.
}
