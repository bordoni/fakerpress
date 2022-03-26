<?php
namespace FakerPress\Module;
use function FakerPress\make;
use function FakerPress\get;
use function FakerPress\get_request_var;
use FakerPress\Plugin;
use FakerPress\Utils;
use Faker;
use FakerPress;

class Term extends Abstract_Module {
	/**
	 * @inheritDoc
	 */
	protected $dependencies = [
		Faker\Provider\Lorem::class,
	];

	public $meta = false;

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public static function fetch( array $args = [] ): array {
		$terms = get_option( 'fakerpress.module_flag.term', [] );

		return $terms;
	}

	/**
	 * @inheritDoc
	 */
	public static function delete( $items ) {
		$deleted = [];
		foreach ( $items as $taxonomy => $terms ){
			$deleted[ $taxonomy ] = [];

			foreach ( $terms as $term ){
				$deleted[ $taxonomy ][ $term ] = wp_delete_term( $term, $taxonomy );
			}
		}

		delete_option( 'fakerpress.module_flag.term' );

		return $deleted;
	}

	/**
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$args = [
			'description' => $data['description'],
			'parent' => $data['parent_term'],
		];

		$term_object = wp_insert_term( $data['name'], $data['taxonomy'], $args );
		if ( is_wp_error( $term_object ) ) {
			return false;
		}

		$flagged = get_option( 'fakerpress.module_flag.' . $this::get_slug(), [] );

		// Ensure that this option is an Array by reseting the variable.
		if ( ! is_array( $flagged ) ){
			$flagged = [];
		}

		if ( ! isset( $flagged[ $data['taxonomy'] ] ) || ! is_array( $flagged[ $data['taxonomy'] ] ) ){
			$flagged[ $data['taxonomy'] ] = [];
		}
		$flagged[ $data['taxonomy'] ] = array_merge( $flagged[ $data['taxonomy'] ], (array) $term_object['term_id'] );

		// When in posts relating is harder so we store in the Options Table
		update_option( 'fakerpress.module_flag.' . $this::get_slug(), $flagged );

		return $term_object['term_id'];
	}

	public function parse_request( $qty, $request = [] ) {
		if ( is_null( $qty ) ) {
			$qty = make( Utils::class )->get_qty_from_range( get_request_var( [ Plugin::$slug, 'qty' ] ) );
		}

		if ( 0 === $qty ){
			return esc_attr__( 'Zero is not a good number of terms to fake...', 'fakerpress' );
		}

		$name_size = get_request_var( [ Plugin::$slug, 'size' ] );

		// Fetch taxomies
		$taxonomies = get( $request, 'taxonomies' );
		$taxonomies = array_map( 'trim', explode( ',', $taxonomies ) );
		$taxonomies = array_intersect( get_taxonomies( [ 'public' => true ] ), $taxonomies );

		// Only has meta after 4.4-beta
		$has_metas = version_compare( $GLOBALS['wp_version'], '4.4-beta', '>=' );

		if ( $has_metas ) {
			$metas = get( $request, 'meta' );
		}

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'taxonomy', $taxonomies );
			$this->set( 'name', $name_size );
			$this->set( 'description' );
			$this->set( 'parent_term' );

			$term_id = $this->generate()->save();

			if ( $has_metas && $term_id && is_numeric( $term_id ) ){
				foreach ( $metas as $meta_index => $meta ) {
					make( Meta::class )->object( $term_id, 'term' )->generate( $meta['type'], $meta['name'], $meta )->save();
				}
			}

			$results[] = $term_id;
		}

		$results = array_filter( (array) $results, 'absint' );

		return $results;
	}
}
