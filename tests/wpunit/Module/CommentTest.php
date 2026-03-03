<?php
/**
 * Tests for the Comment module.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\Module
 */

namespace FakerPress\Tests\Module;

use FakerPress\Module\Comment;

/**
 * Class CommentTest
 *
 * @since 0.9.0
 */
class CommentTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify that filter_save_response stores the FakerPress flag in comment meta.
	 *
	 * @test
	 */
	public function it_should_store_flag_in_comment_meta(): void {
		$post_id = static::factory()->post->create();

		$comment_data = [
			'comment_post_ID'      => $post_id,
			'comment_author'       => 'Test Author',
			'comment_author_email' => 'test@example.com',
			'comment_content'      => 'Test comment content.',
			'comment_approved'     => 1,
		];

		$module     = new Comment();
		$comment_id = $module->filter_save_response( null, $comment_data, $module );

		$this->assertIsInt( $comment_id );
		$this->assertGreaterThan( 0, $comment_id );

		// Verify the flag is in comment_meta, not post_meta.
		$flag = Comment::get_flag();
		$this->assertSame( '1', get_comment_meta( $comment_id, $flag, true ) );
		$this->assertEmpty( get_post_meta( $comment_id, $flag, true ) );
	}

	/**
	 * Verify that the Comment::fetch method can find flagged comments.
	 *
	 * @test
	 */
	public function it_should_fetch_flagged_comments(): void {
		$post_id = static::factory()->post->create();

		$comment_data = [
			'comment_post_ID'      => $post_id,
			'comment_author'       => 'Test Author',
			'comment_author_email' => 'test@example.com',
			'comment_content'      => 'Flagged comment content.',
			'comment_approved'     => 1,
		];

		$module     = new Comment();
		$comment_id = $module->filter_save_response( null, $comment_data, $module );

		$flagged = Comment::fetch();
		$this->assertContains( $comment_id, $flagged );
	}

	/**
	 * Verify that Comment::delete can remove flagged comments.
	 *
	 * @test
	 */
	public function it_should_delete_flagged_comments(): void {
		$post_id = static::factory()->post->create();

		$comment_data = [
			'comment_post_ID'      => $post_id,
			'comment_author'       => 'Test Author',
			'comment_author_email' => 'test@example.com',
			'comment_content'      => 'Deletable comment.',
			'comment_approved'     => 1,
		];

		$module     = new Comment();
		$comment_id = $module->filter_save_response( null, $comment_data, $module );

		$result = Comment::delete( $comment_id );
		$this->assertTrue( $result );
		$this->assertNull( get_comment( $comment_id ) );
	}
}
