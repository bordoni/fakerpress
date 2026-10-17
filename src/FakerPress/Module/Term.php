<?php
/**
 * Generation module for taxonomy terms.
 *
 * @package FakerPress
 */

namespace FakerPress\Module;

use FakerPress;
use WP_Error;

use function FakerPress\make;
use function FakerPress\get;

/**
 * Generates terms with the WP_Term provider and records their IDs in an option, since terms have no flag meta here.
 */
class Term extends Abstract_Module {
	/**
	 * Faker providers this module needs besides the WP_Term provider.
	 *
	 * @var string[]
	 */
	protected $dependencies = [
		FakerPress\ThirdParty\Faker\Provider\Lorem::class,
	];

	/**
	 * Unused flag, kept for backwards compatibility.
	 *
	 * @var bool
	 */
	public $meta = false;

	/**
	 * Faker provider that generates the term fields.
	 *
	 * @var string
	 */
	protected $provider_class = FakerPress\Provider\WP_Term::class;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'terms';
	}

	/**
	 * @inheritDoc
	 */
	public function hook(): void {
	}

	/**
	 * Fetches the terms created by FakerPress from the `fakerpress.module_flag.terms` option.
	 *
	 * @param array $args Unused, the list always comes from the option.
	 *
	 * @return array Term IDs grouped by taxonomy.
	 */
	public static function fetch( array $args = [] ): array {
		return get_option( 'fakerpress.module_flag.' . static::get_slug(), [] );
	}

	/**
	 * Deletes the given terms and then clears the option that lists the FakerPress terms.
	 *
	 * @param array $items Term IDs grouped by taxonomy, as returned by `fetch()`.
	 *
	 * @return array `wp_delete_term()` results grouped by taxonomy and keyed by term ID.
	 */
	public static function delete( $items ) {
		$deleted = [];
		foreach ( $items as $taxonomy => $terms ) {
			$deleted[ $taxonomy ] = [];

			foreach ( $terms as $term ) {
				$deleted[ $taxonomy ][ $term ] = wp_delete_term( $term, $taxonomy );
			}
		}

		delete_option( 'fakerpress.module_flag.' . static::get_slug() );

		return $deleted;
	}

	/**
	 * Inserts the term with `wp_insert_term()` and adds its ID to the option that lists the FakerPress terms.
	 *
	 * @param mixed           $response Response so far, ignored.
	 * @param array           $data     Generated `name`, `taxonomy`, `description` and `parent_term`.
	 * @param Abstract_Module $module   Module being saved.
	 *
	 * @return int|false The new term ID, or false when the insert failed.
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$args = [
			'description' => $data['description'],
			'parent'      => $data['parent_term'],
		];

		$term_object = wp_insert_term( $data['name'], $data['taxonomy'], $args );
		if ( is_wp_error( $term_object ) ) {
			return false;
		}

		$flagged = get_option( 'fakerpress.module_flag.' . $this::get_slug(), [] );

		// Ensure that this option is an Array by resetting the variable.
		if ( ! is_array( $flagged ) ) {
			$flagged = [];
		}

		if ( ! isset( $flagged[ $data['taxonomy'] ] ) || ! is_array( $flagged[ $data['taxonomy'] ] ) ) {
			$flagged[ $data['taxonomy'] ] = [];
		}
		$flagged[ $data['taxonomy'] ] = array_merge( $flagged[ $data['taxonomy'] ], (array) $term_object['term_id'] );

		// When in posts relating is harder so we store in the Options Table.
		update_option( 'fakerpress.module_flag.' . $this::get_slug(), $flagged );

		return $term_object['term_id'];
	}


	/**
	 * Parse the request data and generate the terms.
	 *
	 * @since 0.6.4
	 *
	 * @throws \Exception Exceptions from the Faker generators are not caught.
	 *
	 * @param int   $qty      The quantity of terms to generate.
	 * @param array $request  The request data.
	 *
	 * @return array|WP_Error IDs of the created terms, or a WP_Error when the quantity is below one.
	 */
	public function parse_request( int $qty, array $request = [] ) {
		if ( 0 === $qty || ! is_numeric( $qty ) || $qty < 1 ) {
			return new WP_Error( 'fakerpress_zero_terms', __( 'Zero is not a good number of terms to fake...', 'fakerpress' ) );
		}

		$name_size = get( $request, 'size' );

		// Fetch taxonomies.
		$taxonomies = get( $request, 'taxonomies' );
		$taxonomies = array_map( 'trim', explode( ',', $taxonomies ) );
		$taxonomies = array_intersect( get_taxonomies( [ 'public' => true ] ), $taxonomies );

		$metas = get( $request, 'meta', [] );

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'taxonomy', $taxonomies );
			if ( null !== $name_size ) {
				$this->set( 'name', $name_size );
			} else {
				$this->set( 'name' );
			}
			$this->set( 'description' );
			$this->set( 'parent_term' );

			$term_id = $this->generate()->save();

			if ( $term_id && is_numeric( $term_id ) ) {
				foreach ( $metas as $meta_index => $meta ) {
					if ( ! isset( $meta['type'], $meta['name'] ) ) {
						continue;
					}

					$type = get( $meta, 'type' );
					$name = get( $meta, 'name' );
					unset( $meta['type'], $meta['name'] );

					if ( isset( $meta['weight'] ) ) {
						$meta['weight'] = absint( $meta['weight'] );
						$meta['weight'] = $meta['weight'] > 0 ? $meta['weight'] : 100;
					} else {
						$meta['weight'] = 100;
					}

					make( Meta::class )->object( $term_id, 'term' )->with( $type, $name, $meta )->generate()->save();
				}
			}

			$results[] = $term_id;
		}

		$results = array_filter( (array) $results, 'absint' );

		return $results;
	}
}
