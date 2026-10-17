<?php
/**
 * Generation module for comments.
 *
 * @package FakerPress
 */

namespace FakerPress\Module;

use FakerPress;
use WP_Error;

use function FakerPress\make;
use function FakerPress\get;
use function FakerPress\is_truthy;

/**
 * Generates comments with the WP_Comment provider, flags them as FakerPress content and adds their meta.
 */
class Comment extends Abstract_Module {
	/**
	 * Faker providers this module needs besides the WP_Comment provider.
	 *
	 * @var string[]
	 */
	protected $dependencies = [
		FakerPress\ThirdParty\Faker\Provider\Lorem::class,
		FakerPress\ThirdParty\Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
	];

	/**
	 * Faker provider that generates the comment fields.
	 *
	 * @var string
	 */
	protected $provider_class = FakerPress\Provider\WP_Comment::class;

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'comments';
	}

	/**
	 * @inheritDoc
	 */
	public function hook(): void {
	}

	/**
	 * Fetches the IDs of every comment flagged as created by FakerPress.
	 *
	 * @param array $args WP_Comment_Query arguments that override the defaults.
	 *
	 * @return int[] Comment IDs.
	 */
	public static function fetch( array $args = [] ): array {
		$defaults = [
			'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Lists every item FakerPress generated, found by its flag meta (bulk delete, reuse of generated images).
				[
					'key'   => static::get_flag(),
					'value' => true,
					'type'  => 'BINARY',
				],
			],
		];
		$comments = [];

		$query_comments = new \WP_Comment_Query();
		$args           = wp_parse_args( $args, $defaults );

		$query_comments = $query_comments->query( $args );

		foreach ( $query_comments as $comment ) {
			$comments[] = absint( $comment->comment_ID );
		}

		return $comments;
	}

	/**
	 * Permanently deletes a comment, or a list of comments, created by FakerPress.
	 *
	 * Comments without the FakerPress flag are left alone.
	 *
	 * @param int|\WP_Comment|array $comment Comment ID, comment object, or a list of either.
	 *
	 * @return array|bool Results keyed by comment ID for a list, otherwise whether the comment was deleted.
	 */
	public static function delete( $comment ) {
		if ( is_array( $comment ) ) {
			$deleted = [];

			foreach ( $comment as $id ) {
				$id = $id instanceof \WP_Comment ? $id->comment_ID : $id;

				if ( ! is_numeric( $id ) ) {
					continue;
				}

				$deleted[ $id ] = static::delete( $id );
			}

			return $deleted;
		}

		if ( is_numeric( $comment ) ) {
			$comment = \WP_Comment::get_instance( $comment );
		}

		if ( ! $comment instanceof \WP_Comment ) {
			return false;
		}

		$flag = (bool) get_comment_meta( $comment->comment_ID, static::get_flag(), true );

		if ( true !== $flag ) {
			return false;
		}

		return wp_delete_comment( $comment->comment_ID, true );
	}

	/**
	 * Inserts the comment with `wp_insert_comment()` and flags it as created by FakerPress.
	 *
	 * @param mixed           $response Response so far, ignored.
	 * @param array           $data     Generated comment fields for `wp_insert_comment()`.
	 * @param Abstract_Module $module   Module being saved.
	 *
	 * @return int|false The new comment ID, or false when the insert failed.
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$comment_id = wp_insert_comment( $data );

		if ( ! is_numeric( $comment_id ) ) {
			return false;
		}

		// Flag the Object as FakerPress.
		update_comment_meta( $comment_id, static::get_flag(), 1 );

		return $comment_id;
	}

	/**
	 * Parse the request data and generate the comments.
	 *
	 * @since 0.6.4
	 *
	 * @throws \Exception Exceptions from the Faker generators are not caught.
	 *
	 * @param int   $qty     The quantity of comments to generate.
	 * @param array $request The request data.
	 *
	 * @return array|WP_Error IDs of the created comments, or a WP_Error when the quantity is below one.
	 */
	public function parse_request( int $qty, array $request = [] ) {
		if ( 0 === $qty || ! is_numeric( $qty ) || $qty < 1 ) {
			return new WP_Error( 'fakerpress_zero_comments', __( 'Zero is not a good number of comments to fake...', 'fakerpress' ) );
		}

		$comment_content_size      = get( $request, 'content_size', [ 1, 5 ] );
		$comment_content_use_html  = is_truthy( get( $request, 'use_html', 'off' ) );
		$comment_content_html_tags = array_map( 'trim', explode( ',', get( $request, 'html_tags' ) ) );
		$comment_type              = array_map( 'trim', explode( ',', get( $request, 'type' ) ) );
		$post_types                = array_map( 'trim', explode( ',', get( $request, 'post_types' ) ) );

		$min_date = get( $request, [ 'interval_date', 'min' ] );
		$max_date = get( $request, [ 'interval_date', 'max' ] );
		$metas    = get( $request, 'meta', [] );

		$results = [];

		for ( $i = 0; $i < $qty; $i++ ) {
			$this->set( 'comment_date', $min_date, $max_date );
			$this->set(
				'comment_content',
				$comment_content_use_html,
				[
					'qty'      => $comment_content_size,
					'elements' => $comment_content_html_tags,
				]
			);
			$this->set( 'user_id', 0 );
			$this->set( 'comment_type', $comment_type );

			$this->set( 'comment_author' );
			$this->set( 'comment_parent' );
			$this->set( 'comment_author_IP' );
			$this->set( 'comment_agent' );
			$comment_approved = get( $request, 'comment_approved' );
			if ( null !== $comment_approved ) {
				$this->set( 'comment_approved', $comment_approved );
			} else {
				$this->set( 'comment_approved' );
			}
			$this->set( 'comment_post_ID', null, [ 'post_type' => $post_types ] );
			$this->set( 'comment_author_email' );
			$this->set( 'comment_author_url' );

			$comment_id = $this->generate()->save();

			if ( $comment_id && is_numeric( $comment_id ) ) {
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

					make( Meta::class )->object( $comment_id, 'comment' )->with( $type, $name, $meta )->generate()->save();
				}
			}
			$results[] = $comment_id;
		}
		$results = array_filter( $results, 'absint' );

		return $results;
	}
}
