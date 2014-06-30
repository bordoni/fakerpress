<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $provider = '\Faker\Provider\WP_Comment';

	public function save() {
		$comment_id = wp_insert_comment( $this->params );

		// Relate this post to FakerPress to make it possible to delete
		add_comment_meta( $comment_id, $this->flag, 1 );

		return $comment_id;
	}
}
