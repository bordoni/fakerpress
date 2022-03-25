<?php

namespace FakerPress\Module;

use FakerPress\Plugin;
use FakerPress\Utils;
use Faker;
use FakerPress;
use function FakerPress\make;

class Comment extends Abstract_Module {
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
	 * @inheritDoc
	 */
	public static function fetch( array $args = [] ): array {
		$defaults = [
			'meta_query' => [
				[
					'key'   => static::get_flag(),
					'value' => true,
					'type'  => 'BINARY',
				],
			],
		];
		$comments = [];

		$query_comments = new \WP_Comment_Query;
		$args           = wp_parse_args( $args, $defaults );

		$query_comments = $query_comments->query( $args );

		foreach ( $query_comments as $comment ) {
			$comments[] = absint( $comment->comment_ID );
		}

		return $comments;
	}

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		$comment_id = wp_insert_comment( $data );

		if ( ! is_numeric( $comment_id ) ) {
			return false;
		}

		// Flag the Object as FakerPress
		update_post_meta( $comment_id, static::get_flag(), 1 );

		return $comment_id;
	}

	public function parse_request( $qty, $request = [] ) {
		if ( is_null( $qty ) ) {
			$qty = make( Utils::class )->get_qty_from_range( fp_get_global_var( INPUT_POST, [ Plugin::$slug, 'qty' ], FILTER_UNSAFE_RAW ) );
		}

		if ( 0 === $qty ) {
			return esc_attr__( 'Zero is not a good number of comments to fake...', 'fakerpress' );
		}

		$comment_content_size      = fp_array_get( $request, [ 'content_size' ], FILTER_UNSAFE_RAW, [ 1, 5 ] );
		$comment_content_use_html  = make( Utils::class )->is_truthy( fp_array_get( $request, [ 'use_html' ], FILTER_SANITIZE_STRING, 'off' ) );
		$comment_content_html_tags = array_map( 'trim', explode( ',', fp_array_get( $request, [ 'html_tags' ], FILTER_SANITIZE_STRING ) ) );
		$comment_type              = array_map( 'trim', explode( ',', fp_array_get( $request, [ 'type' ], FILTER_SANITIZE_STRING ) ) );
		$post_types                = array_map( 'trim', explode( ',', fp_array_get( $request, [ 'post_types' ], FILTER_SANITIZE_STRING ) ) );

		$min_date = fp_array_get( $request, [ 'interval_date', 'min' ] );
		$max_date = fp_array_get( $request, [ 'interval_date', 'max' ] );
		$metas    = fp_array_get( $request, [ 'meta' ], FILTER_UNSAFE_RAW );

		$results = [];

		for ( $i = 0; $i < $qty; $i ++ ) {
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
			$this->set( 'comment_approved' );
			$this->set( 'comment_post_ID', null, [ 'post_type' => $post_types ] );
			$this->set( 'comment_author_email' );
			$this->set( 'comment_author_url' );

			$comment_id = $this->generate()->save();

			if ( $comment_id && is_numeric( $comment_id ) ) {
				foreach ( $metas as $meta_index => $meta ) {
					make( Meta::class )->object( $comment_id, 'comment' )->generate( $meta['type'], $meta['name'], $meta )->save();
				}
			}
			$results[] = $comment_id;
		}
		$results = array_filter( $results, 'absint' );

		return $results;
	}
}
