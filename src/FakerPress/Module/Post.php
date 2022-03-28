<?php

namespace FakerPress\Module;

use FakerPress\Admin;
use FakerPress\Plugin;
use Faker;
use FakerPress;
use function FakerPress\make;
use function FakerPress\get_request_var;
use function FakerPress\is_truthy;
use function FakerPress\get;

class Post extends Abstract_Module {
	/**
	 * @inheritDoc
	 */
	protected $dependencies = [
		Faker\Provider\Lorem::class,
		Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
	];

	/**
	 * @inheritDoc
	 */
	protected $provider_class = FakerPress\Provider\WP_Post::class;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'posts';
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
		$defaults = [
			'post_type'      => 'any',
			'post_status'    => 'any',
			'nopaging'       => true,
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'meta_query'     => [
				[
					'key'   => static::get_flag(),
					'value' => true,
					'type'  => 'BINARY',
				],
			],
		];

		$args        = wp_parse_args( $args, $defaults );
		$query_posts = new \WP_Query( $args );

		return array_map( 'absint', $query_posts->posts );
	}

	/**
	 * @inheritDoc
	 */
	public static function delete( $item ) {
		if ( is_array( $item ) ) {
			$deleted = [];

			foreach ( $item as $id ) {
				$id = $id instanceof \WP_Post ? $id->ID : $id;

				if ( ! is_numeric( $id ) ) {
					continue;
				}

				$deleted[ $id ] = static::delete( $id );
			}

			return $deleted;
		}

		if ( is_numeric( $item ) ) {
			$item = \WP_Post::get_instance( $item );
		}

		if ( ! $item instanceof \WP_Post ) {
			return false;
		}

		$flag = (bool) get_post_meta( $item->ID, static::get_flag(), true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_post( $item->ID, true );
	}

	/**
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$post_id = wp_insert_post( $data );

		if ( ! is_numeric( $post_id ) ) {
			return false;
		}

		// Flag the Object as FakerPress
		update_post_meta( $post_id, static::get_flag(), 1 );

		return $post_id;
	}

	/**
	 * @since TBD
	 *
	 * @throws \Exception
	 *
	 * @param $request
	 * @param $qty
	 *
	 * @return array|string
	 */
	public function parse_request( $qty, $request = [] ) {
		if ( is_null( $qty ) ) {
			$qty = get_request_var( [ Plugin::$slug, 'qty' ] );
			$min = absint( $qty['min'] );
			$max = max( absint( isset( $qty['max'] ) ? $qty['max'] : 0 ), $min );
			$qty = $this->get_faker()->numberBetween( $min, $max );
		}

		if ( 0 === $qty ) {
			return esc_attr__( 'Zero is not a good number of posts to fake...', 'fakerpress' );
		}

		// Fetch Comment Status
		$comment_status = get( $request, 'comment_status' );
		$comment_status = array_map( 'trim', explode( ',', $comment_status ) );

		// Fetch Post Author
		$post_author = get( $request, 'author' );
		$post_author = array_map( 'trim', explode( ',', $post_author ) );
		$post_author = array_intersect( get_users( [ 'fields' => 'ID' ] ), $post_author );

		// Fetch the dates
		$date = [
			get( $request, [ 'interval_date', 'min' ] ),
			get( $request, [ 'interval_date', 'max' ] ),
		];

		// Fetch Post Types
		$post_types = get( $request, 'post_types' );
		$post_types = array_map( 'trim', explode( ',', $post_types ) );
		$post_types = array_intersect( get_post_types( [ 'public' => true ] ), $post_types );

		// Fetch Post Content
		$post_content_size      = get( $request, 'content_size', [ 5, 15 ] );
		$post_content_use_html  = ( (int) get( $request, 'use_html', 0 ) ) === 1;
		$post_content_html_tags = array_map( 'trim', explode( ',', get( $request, 'html_tags' ) ) );

		// Fetch Post Excerpt.
		$post_excerpt_size = get( $request, 'excerpt_size', [ 1, 3 ] );

		// Fetch and clean Post Parents
		$post_parents = get( $request, 'post_parent' );
		$post_parents = array_map( 'trim', explode( ',', $post_parents ) );

		$images_origin = array_map( 'trim', explode( ',', get( $request, 'images_origin' ) ) );

		// Fetch Taxonomies
		$taxonomies_configuration = get( $request, 'taxonomy' );

		// Fetch Metas It will be parsed later!
		$metas = get( $request, 'meta', [] );

		$results = [];

		for ( $i = 0; $i < $qty; $i ++ ) {
			$this->set( 'post_title' );
			$this->set( 'post_status', 'publish' );
			$this->set( 'post_date', $date );
			$this->set( 'post_parent', $post_parents );
			$this->set(
				'post_content',
				$post_content_use_html,
				[
					'qty'      => $post_content_size,
					'elements' => $post_content_html_tags,
					'sources'  => $images_origin,
				]
			);
			$this->set( 'post_excerpt', $post_excerpt_size );
			$this->set( 'post_author', $post_author );
			$this->set( 'post_type', $post_types );
			$this->set( 'comment_status', $comment_status );
			$this->set( 'ping_status' );
			$this->set( 'tax_input', $taxonomies_configuration );

			$generated = $this->generate();
			$post_id   = $generated->save();

			if ( $post_id && is_numeric( $post_id ) ) {
				foreach ( $metas as $meta_index => $meta ) {
					if ( ! isset( $meta['type'], $meta['name'] ) ) {
						continue;
					}

					make( Meta::class )->object( $post_id )->generate( $meta['type'], $meta['name'], $meta )->save();

				}
			}

			$results[] = $post_id;
		}

		$results = array_filter( (array) $results, 'absint' );

		return $results;
	}
}
