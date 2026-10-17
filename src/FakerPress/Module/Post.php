<?php
/**
 * Generation module for posts of any public post type.
 *
 * @package FakerPress
 */

namespace FakerPress\Module;

use FakerPress;
use WP_Error;

use function FakerPress\make;
use function FakerPress\get;

/**
 * Generates posts with the WP_Post provider, flags them as FakerPress content and adds their meta.
 */
class Post extends Abstract_Module {
	/**
	 * Faker providers this module needs besides the WP_Post provider.
	 *
	 * @var string[]
	 */
	protected $dependencies = [
		FakerPress\ThirdParty\Faker\Provider\Lorem::class,
		FakerPress\ThirdParty\Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
	];

	/**
	 * Faker provider that generates the post fields.
	 *
	 * @var string
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
	 * Fetches the IDs of every post, of any type and status, flagged as created by FakerPress.
	 *
	 * @param array $args WP_Query arguments that override the defaults.
	 *
	 * @return int[] Post IDs.
	 */
	public static function fetch( array $args = [] ): array {
		$defaults = [
			'post_type'      => 'any',
			'post_status'    => 'any',
			'nopaging'       => true, // phpcs:ignore WordPressVIPMinimum.Performance.NoPaging.nopaging_nopaging -- Callers need every generated item, not one page (bulk delete, reuse of generated images).
			'posts_per_page' => - 1,
			'fields'         => 'ids',
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Lists every item FakerPress generated, found by its flag meta (bulk delete, reuse of generated images).
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
	 * Permanently deletes a post, or a list of posts, created by FakerPress.
	 *
	 * Posts without the FakerPress flag are left alone.
	 *
	 * @param int|\WP_Post|array $item Post ID, post object, or a list of either.
	 *
	 * @return array|\WP_Post|false|null Results keyed by post ID for a list, otherwise the `wp_delete_post()` result, or false when the post is missing or not flagged.
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
	 * Inserts the post with `wp_insert_post()` and flags it as created by FakerPress.
	 *
	 * @param mixed           $response Response so far, ignored.
	 * @param array           $data     Generated post fields for `wp_insert_post()`.
	 * @param Abstract_Module $module   Module being saved.
	 *
	 * @return int|false The new post ID, or false when the insert failed.
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$post_id = wp_insert_post( $data );

		if ( ! is_numeric( $post_id ) ) {
			return false;
		}

		// Flag the Object as FakerPress.
		update_post_meta( $post_id, static::get_flag(), 1 );

		return $post_id;
	}

	/**
	 * Parse the request data and generate the posts.
	 *
	 * @since 0.6.4
	 *
	 * @throws \Exception Exceptions from the Faker generators are not caught.
	 *
	 * @param int   $qty      The quantity of posts to generate.
	 * @param array $request  The request data.
	 *
	 * @return array|WP_Error IDs of the created posts, or a WP_Error when the quantity is below one.
	 */
	public function parse_request( int $qty, array $request = [] ) {
		if ( 0 === $qty || ! is_numeric( $qty ) || $qty < 1 ) {
			return new WP_Error( 'fakerpress_zero_posts', __( 'Zero is not a good number of posts to fake...', 'fakerpress' ) );
		}

		// Fetch Comment Status.
		$comment_status = get( $request, 'comment_status', '' );
		$comment_status = array_map( 'trim', explode( ',', $comment_status ) );

		// Fetch Post Author.
		$post_author = get( $request, 'author', '' );
		$post_author = array_map( 'trim', explode( ',', $post_author ) );
		$post_author = array_intersect( get_users( [ 'fields' => 'ID' ] ), $post_author );

		// Fetch the dates.
		$date = [
			get( $request, [ 'interval_date', 'min' ] ),
			get( $request, [ 'interval_date', 'max' ] ),
		];

		// Fetch Post Types.
		$post_types = get( $request, 'post_types', '' );
		$post_types = array_map( 'trim', explode( ',', $post_types ) );
		$post_types = array_intersect( get_post_types( [ 'public' => true ] ), $post_types );

		// Fetch Post Content.
		$post_content_size      = get( $request, 'content_size', [ 5, 15 ] );
		$post_content_use_html  = ( (int) get( $request, 'use_html', 0 ) ) === 1;
		$post_content_html_tags = array_map( 'trim', explode( ',', get( $request, 'html_tags', '' ) ) );

		// Fetch Post Excerpt.
		$post_excerpt_size = get( $request, 'excerpt_size', [ 1, 3 ] );

		// Fetch and clean Post Parents.
		$post_parents = get( $request, 'post_parent', '' );
		$post_parents = array_map( 'trim', explode( ',', $post_parents ) );

		$images_origin = array_map( 'trim', explode( ',', get( $request, 'images_origin', '' ) ) );

		// Fetch Taxonomies.
		$taxonomies_configuration = get( $request, 'taxonomy' );

		// Fetch Metas It will be parsed later!
		$metas = get( $request, 'meta', [] );

		$results = [];

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'post_title' );
			$this->set( 'post_status', get( $request, 'post_status', 'publish' ) );
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

					$type = get( $meta, 'type' );
					$name = get( $meta, 'name' );
					unset( $meta['type'], $meta['name'] );

					if ( isset( $meta['weight'] ) ) {
						$meta['weight'] = absint( $meta['weight'] );
						$meta['weight'] = $meta['weight'] > 0 ? $meta['weight'] : 100;
					} else {
						$meta['weight'] = 100;
					}

					make( Meta::class )->object( $post_id )->with( $type, $name, $meta )->generate()->save();
				}
			}

			$results[] = $post_id;
		}

		$results = array_filter( (array) $results, 'absint' );

		return $results;
	}
}
