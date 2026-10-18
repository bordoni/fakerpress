<?php
/**
 * Trait for describing generated items in REST API responses.
 *
 * Gives the admin enough about each generated item to show it without another request: a title,
 * a short summary, edit and view links, a thumbnail, and the full record for the Advanced view.
 *
 * @since 0.10.0
 * @package FakerPress
 */

namespace FakerPress\REST\Traits;

/**
 * Trait Describes_Generated_Items
 *
 * @since 0.10.0
 */
trait Describes_Generated_Items {

	/**
	 * Longest string kept in the full data of an item; longer values are cut and end in an ellipsis.
	 *
	 * @since 0.10.0
	 *
	 * @var int
	 */
	protected static $detail_string_limit = 500;

	/**
	 * Meta keys left out of the full data: sessions and capabilities are not useful and can be sensitive.
	 *
	 * @since 0.10.0
	 *
	 * @var string[]
	 */
	protected static $hidden_meta_keys = [ 'session_tokens', '_edit_lock', '_edit_last' ];

	/**
	 * Describes each generated item for the admin.
	 *
	 * @since 0.10.0
	 *
	 * @param string $item_type Type of item: post, user, term, comment or attachment.
	 * @param int[]  $ids       IDs of the generated items.
	 *
	 * @return array[] One description per item that still exists, in the order given.
	 */
	protected function describe_generated_items( $item_type, $ids ) {
		$items = [];

		foreach ( (array) $ids as $id ) {
			$id = absint( $id );

			switch ( $item_type ) {
				case 'post':
				case 'attachment':
					$item = $this->describe_generated_post( $id );
					break;
				case 'user':
					$item = $this->describe_generated_user( $id );
					break;
				case 'term':
					$item = $this->describe_generated_term( $id );
					break;
				case 'comment':
					$item = $this->describe_generated_comment( $id );
					break;
				default:
					$item = null;
			}

			if ( $item ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Gets the admin screen that lists items like the generated ones.
	 *
	 * @since 0.10.0
	 *
	 * @param string  $item_type Type of item: post, user, term, comment or attachment.
	 * @param array[] $items     Items from describe_generated_items().
	 *
	 * @return string|null The admin URL, or null when the items span more than one list.
	 */
	protected function get_generated_items_list_url( $item_type, $items ) {
		$types = array_unique( wp_list_pluck( $items, 'type' ) );

		switch ( $item_type ) {
			case 'post':
				return 1 === count( $types ) ? admin_url( 'edit.php?post_type=' . rawurlencode( reset( $types ) ) ) : null;
			case 'attachment':
				return admin_url( 'upload.php?mode=list' );
			case 'user':
				return admin_url( 'users.php' );
			case 'term':
				return 1 === count( $types ) ? admin_url( 'edit-tags.php?taxonomy=' . rawurlencode( reset( $types ) ) ) : null;
			case 'comment':
				return admin_url( 'edit-comments.php' );
			default:
				return null;
		}
	}

	/**
	 * Describes a generated post of any type, attachments included.
	 *
	 * @since 0.10.0
	 *
	 * @param int $id Post ID.
	 *
	 * @return array|null The description, or null when the post no longer exists.
	 */
	protected function describe_generated_post( $id ) {
		$post = get_post( $id );
		if ( ! $post ) {
			return null;
		}

		$type_object   = get_post_type_object( $post->post_type );
		$status_object = get_post_status_object( $post->post_status );
		$author        = get_userdata( (int) $post->post_author );
		$is_attachment = 'attachment' === $post->post_type;
		$title         = get_the_title( $post );

		// One taxonomy at a time: with post_format in a multi-taxonomy query, core blanks every term name.
		$terms_by_taxonomy = [];
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$names = wp_get_object_terms( $id, $taxonomy, [ 'fields' => 'names' ] );
			if ( ! is_wp_error( $names ) && $names ) {
				$terms_by_taxonomy[ $taxonomy ] = $names;
			}
		}

		$summary = [
			[
				'label' => __( 'Type', 'fakerpress' ),
				'value' => $type_object ? $type_object->labels->singular_name : $post->post_type,
			],
		];

		if ( $is_attachment ) {
			$metadata  = wp_get_attachment_metadata( $id );
			$summary[] = [
				'label' => __( 'File type', 'fakerpress' ),
				'value' => (string) $post->post_mime_type,
			];
			if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
				$summary[] = [
					'label' => __( 'Dimensions', 'fakerpress' ),
					/* translators: 1: Image width in pixels, 2: Image height in pixels. */
					'value' => sprintf( __( '%1$d × %2$d', 'fakerpress' ), $metadata['width'], $metadata['height'] ),
				];
			}
			if ( ! empty( $metadata['filesize'] ) ) {
				$summary[] = [
					'label' => __( 'Size', 'fakerpress' ),
					'value' => size_format( $metadata['filesize'] ),
				];
			}
			if ( $post->post_parent ) {
				$summary[] = [
					'label' => __( 'Attached to', 'fakerpress' ),
					'value' => get_the_title( $post->post_parent ),
				];
			}
		} else {
			$summary[] = [
				'label' => __( 'Status', 'fakerpress' ),
				'value' => $status_object ? $status_object->label : $post->post_status,
			];
			$summary[] = [
				'label' => __( 'Author', 'fakerpress' ),
				'value' => $author ? $author->display_name : '',
			];

			foreach ( $terms_by_taxonomy as $taxonomy => $names ) {
				$taxonomy_object = get_taxonomy( $taxonomy );
				$summary[]       = [
					'label' => $taxonomy_object ? $taxonomy_object->labels->name : $taxonomy,
					'value' => implode( ', ', $names ),
				];
			}
		}

		$details = get_post( $id, ARRAY_A );
		unset( $details['filter'] );
		$details['terms'] = $terms_by_taxonomy;
		if ( $is_attachment ) {
			$details['attachment_metadata'] = wp_get_attachment_metadata( $id );
		}
		$details['meta'] = $this->get_generated_item_meta( get_post_meta( $id ) );

		return [
			'id'        => $id,
			'title'     => '' !== $title ? html_entity_decode( $title, ENT_QUOTES, get_bloginfo( 'charset' ) ) : __( '(no title)', 'fakerpress' ),
			'type'      => $post->post_type,
			'status'    => $post->post_status,
			'date'      => get_post_time( 'c', true, $post ),
			'summary'   => array_values( array_filter( $summary, [ $this, 'has_summary_value' ] ) ),
			'edit_url'  => get_edit_post_link( $id, 'raw' ),
			'view_url'  => $is_attachment ? wp_get_attachment_url( $id ) : get_permalink( $id ),
			'thumbnail' => $is_attachment ? wp_get_attachment_image_url( $id, 'thumbnail' ) : get_the_post_thumbnail_url( $id, 'thumbnail' ),
			'details'   => $this->prepare_generated_item_details( $details ),
		];
	}

	/**
	 * Describes a generated user.
	 *
	 * @since 0.10.0
	 *
	 * @param int $id User ID.
	 *
	 * @return array|null The description, or null when the user no longer exists.
	 */
	protected function describe_generated_user( $id ) {
		$user = get_userdata( $id );
		if ( ! $user ) {
			return null;
		}

		$role_names = wp_roles()->get_names();
		$roles      = array_map(
			static function ( $role ) use ( $role_names ) {
				return isset( $role_names[ $role ] ) ? translate_user_role( $role_names[ $role ] ) : $role;
			},
			(array) $user->roles
		);

		$details = $user->to_array();
		unset( $details['user_pass'], $details['user_activation_key'] );
		$details['roles'] = array_values( (array) $user->roles );
		$details['meta']  = $this->get_generated_item_meta( get_user_meta( $id ) );

		return [
			'id'        => $id,
			'title'     => $user->display_name,
			'type'      => 'user',
			'status'    => null,
			'date'      => $this->format_generated_item_date( $user->user_registered ),
			'summary'   => array_values(
				array_filter(
					[
						[
							'label' => __( 'Username', 'fakerpress' ),
							'value' => $user->user_login,
						],
						[
							'label' => __( 'Role', 'fakerpress' ),
							'value' => implode( ', ', $roles ),
						],
						[
							'label' => __( 'Email', 'fakerpress' ),
							'value' => $user->user_email,
						],
					],
					[ $this, 'has_summary_value' ]
				)
			),
			'edit_url'  => get_edit_user_link( $id ),
			'view_url'  => get_author_posts_url( $id ),
			'thumbnail' => get_avatar_url( $id, [ 'size' => 96 ] ),
			'details'   => $this->prepare_generated_item_details( $details ),
		];
	}

	/**
	 * Describes a generated term.
	 *
	 * @since 0.10.0
	 *
	 * @param int $id Term ID.
	 *
	 * @return array|null The description, or null when the term no longer exists.
	 */
	protected function describe_generated_term( $id ) {
		$term = get_term( $id );
		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		$taxonomy = get_taxonomy( $term->taxonomy );
		$link     = get_term_link( $term );
		$parent   = $term->parent ? get_term( $term->parent ) : null;

		$details         = get_term( $id, '', ARRAY_A );
		$details['meta'] = $this->get_generated_item_meta( get_term_meta( $id ) );

		return [
			'id'        => $id,
			'title'     => html_entity_decode( $term->name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'type'      => $term->taxonomy,
			'status'    => null,
			'date'      => null,
			'summary'   => array_values(
				array_filter(
					[
						[
							'label' => __( 'Taxonomy', 'fakerpress' ),
							'value' => $taxonomy ? $taxonomy->labels->singular_name : $term->taxonomy,
						],
						[
							'label' => __( 'Slug', 'fakerpress' ),
							'value' => $term->slug,
						],
						[
							'label' => __( 'Parent', 'fakerpress' ),
							'value' => $parent && ! is_wp_error( $parent ) ? $parent->name : '',
						],
					],
					[ $this, 'has_summary_value' ]
				)
			),
			'edit_url'  => get_edit_term_link( $term ),
			'view_url'  => is_wp_error( $link ) ? null : $link,
			'thumbnail' => null,
			'details'   => $this->prepare_generated_item_details( $details ),
		];
	}

	/**
	 * Describes a generated comment.
	 *
	 * @since 0.10.0
	 *
	 * @param int $id Comment ID.
	 *
	 * @return array|null The description, or null when the comment no longer exists.
	 */
	protected function describe_generated_comment( $id ) {
		$comment = get_comment( $id );
		if ( ! $comment ) {
			return null;
		}

		$status_labels = [
			'approved'   => __( 'Approved', 'fakerpress' ),
			'unapproved' => __( 'Pending', 'fakerpress' ),
			'spam'       => __( 'Spam', 'fakerpress' ),
			'trash'      => __( 'Trash', 'fakerpress' ),
		];
		$status        = wp_get_comment_status( $comment );
		$excerpt       = wp_trim_words( wp_strip_all_tags( $comment->comment_content ), 10 );

		$details         = get_comment( $id, ARRAY_A );
		$details['meta'] = $this->get_generated_item_meta( get_comment_meta( $id ) );

		return [
			'id'        => $id,
			'title'     => '' !== $excerpt ? html_entity_decode( $excerpt, ENT_QUOTES, get_bloginfo( 'charset' ) ) : __( '(empty comment)', 'fakerpress' ),
			'type'      => '' !== $comment->comment_type ? $comment->comment_type : 'comment',
			'status'    => $status ? $status : null,
			'date'      => $this->format_generated_item_date( $comment->comment_date_gmt ),
			'summary'   => array_values(
				array_filter(
					[
						[
							'label' => __( 'Author', 'fakerpress' ),
							'value' => $comment->comment_author,
						],
						[
							'label' => __( 'On', 'fakerpress' ),
							'value' => get_the_title( (int) $comment->comment_post_ID ),
						],
						[
							'label' => __( 'Status', 'fakerpress' ),
							'value' => $status_labels[ $status ] ?? (string) $status,
						],
					],
					[ $this, 'has_summary_value' ]
				)
			),
			// Built by hand: get_edit_comment_link() returns `&amp;` before WordPress 6.7.
			'edit_url'  => admin_url( 'comment.php?action=editcomment&c=' . $id ),
			'view_url'  => get_comment_link( $comment ),
			'thumbnail' => get_avatar_url( $comment, [ 'size' => 96 ] ),
			'details'   => $this->prepare_generated_item_details( $details ),
		];
	}

	/**
	 * Formats a GMT MySQL datetime as ISO 8601 with its UTC offset, so browsers read it correctly.
	 *
	 * @since 0.10.0
	 *
	 * @param string $gmt Datetime in GMT, `Y-m-d H:i:s`.
	 *
	 * @return string|null The ISO 8601 date, or null for an empty date.
	 */
	protected function format_generated_item_date( $gmt ) {
		$date = date_create_immutable_from_format( 'Y-m-d H:i:s', (string) $gmt, new \DateTimeZone( 'UTC' ) );

		return $date && '0000-00-00 00:00:00' !== $gmt ? $date->format( DATE_ATOM ) : null;
	}

	/**
	 * Whether a summary row has a value worth showing.
	 *
	 * @since 0.10.0
	 *
	 * @param array $row A row with `label` and `value`.
	 *
	 * @return bool
	 */
	protected function has_summary_value( $row ) {
		return isset( $row['value'] ) && '' !== (string) $row['value'];
	}

	/**
	 * Flattens raw meta from get_*_meta() into one value per key, without hidden keys.
	 *
	 * @since 0.10.0
	 *
	 * @param array $meta Meta as returned by get_post_meta( $id ) and friends.
	 *
	 * @return array
	 */
	protected function get_generated_item_meta( $meta ) {
		$flat = [];

		foreach ( (array) $meta as $key => $values ) {
			if ( in_array( $key, static::$hidden_meta_keys, true ) || str_ends_with( (string) $key, 'capabilities' ) ) {
				continue;
			}

			$values       = array_map( 'maybe_unserialize', (array) $values );
			$flat[ $key ] = 1 === count( $values ) ? reset( $values ) : $values;
		}

		ksort( $flat );

		return $flat;
	}

	/**
	 * Makes the full data of an item safe to send: long strings are cut and objects become arrays.
	 *
	 * @since 0.10.0
	 *
	 * @param mixed $value The value to prepare.
	 * @param int   $depth How deep the recursion already is.
	 *
	 * @return mixed
	 */
	protected function prepare_generated_item_details( $value, $depth = 0 ) {
		if ( is_object( $value ) ) {
			$value = get_object_vars( $value );
		}

		if ( is_array( $value ) ) {
			if ( $depth >= 5 ) {
				return '…';
			}

			foreach ( $value as $key => $child ) {
				$value[ $key ] = $this->prepare_generated_item_details( $child, $depth + 1 );
			}

			return $value;
		}

		if ( is_string( $value ) && mb_strlen( $value ) > static::$detail_string_limit ) {
			return mb_substr( $value, 0, static::$detail_string_limit ) . '…';
		}

		return $value;
	}
}
