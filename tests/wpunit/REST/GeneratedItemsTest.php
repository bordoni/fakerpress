<?php
/**
 * Tests the `items` and `list_url` the generate endpoints return for the admin's details view.
 *
 * @since 0.10.0
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Tests\Traits\REST_Test_Case;
use WP_REST_Request;

/**
 * Class GeneratedItemsTest
 *
 * @since 0.10.0
 */
class GeneratedItemsTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * Set up each test.
	 *
	 * @since 0.10.0
	 */
	public function set_up(): void {
		parent::set_up();
		$this->init_rest_server();
		$this->set_admin_user();
	}

	/**
	 * Dispatch a JSON generate request and return the batch data.
	 *
	 * @since 0.10.0
	 *
	 * @param string $module Endpoint name, e.g. 'posts'.
	 * @param array  $body   Request body.
	 *
	 * @return array
	 */
	private function generate( string $module, array $body ): array {
		$request = new WP_REST_Request( 'POST', "/fakerpress/v1/{$module}/generate" );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $body + [ 'offset' => 0 ] ) );

		$response = rest_get_server()->dispatch( $request );
		$this->assert_success_response( $response );

		return $response->get_data()['data'];
	}

	/**
	 * Assert the fields every item carries.
	 *
	 * @since 0.10.0
	 *
	 * @param array $data Batch data from generate().
	 */
	private function assert_items_match_ids( array $data ): void {
		$this->assertArrayHasKey( 'items', $data );
		$this->assertSame( array_map( 'intval', $data['ids'] ), wp_list_pluck( $data['items'], 'id' ) );

		foreach ( $data['items'] as $item ) {
			foreach ( [ 'id', 'title', 'type', 'status', 'date', 'summary', 'edit_url', 'view_url', 'thumbnail', 'details' ] as $key ) {
				$this->assertArrayHasKey( $key, $item );
			}
			$this->assertNotSame( '', $item['title'] );
			$this->assertIsArray( $item['details'] );
			foreach ( $item['summary'] as $fact ) {
				$this->assertNotSame( '', $fact['value'], "Empty summary values are dropped: {$fact['label']}" );
			}
			if ( $item['edit_url'] ) {
				$this->assertStringNotContainsString( '&amp;', $item['edit_url'] );
			}
		}
	}

	/**
	 * Posts: title, status and author, raw edit URLs, terms and meta in the full data, and the list screen.
	 *
	 * @test
	 */
	public function it_should_describe_generated_posts(): void {
		$category = static::factory()->category->create( [ 'name' => 'Faker Category' ] );

		$data = $this->generate(
			'posts',
			[
				'qty'        => [
					'min' => 2,
					'max' => 2,
				],
				'post_types' => 'post',
				'taxonomy'   => [
					[
						'taxonomies' => [ 'category' ],
						'terms'      => [ (string) $category ],
						'rate'       => 100,
						'qty'        => [ 1, 1 ],
					],
				],
				'meta'       => [
					[
						'type'   => 'email',
						'name'   => 'fp_item_email',
						'weight' => 100,
					],
				],
			]
		);

		$this->assert_items_match_ids( $data );
		$this->assertSame( admin_url( 'edit.php?post_type=post' ), $data['list_url'] );

		$item = $data['items'][0];
		$post = get_post( $item['id'] );
		$this->assertSame( 'post', $item['type'] );
		$this->assertSame( $post->post_status, $item['status'] );
		$this->assertSame( get_edit_post_link( $post->ID, 'raw' ), $item['edit_url'] );
		$this->assertContains( 'Author', wp_list_pluck( $item['summary'], 'label' ) );
		// Named per taxonomy; a multi-taxonomy query that includes post_format used to blank every name.
		$facts = wp_list_pluck( $item['summary'], 'value', 'label' );
		$this->assertSame( 'Faker Category', $facts['Categories'] );
		$this->assertSame( [ 'Faker Category' ], $item['details']['terms']['category'] );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+00:00$/', $item['date'] );
		$this->assertSame( get_post_meta( $post->ID, 'fp_item_email', true ), $item['details']['meta']['fp_item_email'] );
		$this->assertArrayNotHasKey( '_edit_lock', $item['details']['meta'] );
	}

	/**
	 * Users: role and email in the summary, never the password hash or capabilities in the full data.
	 *
	 * @test
	 */
	public function it_should_describe_generated_users_without_secrets(): void {
		$data = $this->generate(
			'users',
			[
				'qty'   => [
					'min' => 1,
					'max' => 1,
				],
				'roles' => 'editor',
			]
		);

		$this->assert_items_match_ids( $data );
		$this->assertSame( admin_url( 'users.php' ), $data['list_url'] );

		$item = $data['items'][0];
		$this->assertSame( 'user', $item['type'] );
		$this->assertContains( 'Editor', wp_list_pluck( $item['summary'], 'value' ) );
		$this->assertArrayNotHasKey( 'user_pass', $item['details'] );
		$this->assertArrayNotHasKey( 'user_activation_key', $item['details'] );
		foreach ( array_keys( $item['details']['meta'] ) as $key ) {
			$this->assertStringEndsNotWith( 'capabilities', $key );
		}
		$this->assertNotEmpty( $item['thumbnail'] );
	}

	/**
	 * Terms: taxonomy and slug, the term's edit screen, and the taxonomy's list screen.
	 *
	 * @test
	 */
	public function it_should_describe_generated_terms(): void {
		$data = $this->generate(
			'terms',
			[
				'qty'        => [
					'min' => 2,
					'max' => 2,
				],
				'taxonomies' => 'post_tag',
			]
		);

		$this->assert_items_match_ids( $data );
		$this->assertSame( admin_url( 'edit-tags.php?taxonomy=post_tag' ), $data['list_url'] );

		$item = $data['items'][0];
		$this->assertSame( 'post_tag', $item['type'] );
		$this->assertSame( get_term( $item['id'] )->slug, $item['details']['slug'] );
		$this->assertStringContainsString( 'term.php', $item['edit_url'] );
	}

	/**
	 * Comments: author and the post they are on, and an edit URL without HTML entities.
	 *
	 * @test
	 */
	public function it_should_describe_generated_comments(): void {
		static::factory()->post->create( [ 'post_title' => 'Commented post' ] );

		$data = $this->generate(
			'comments',
			[
				'type'       => 'comment',
				'post_types' => 'post',
				'qty'        => [
					'min' => 1,
					'max' => 1,
				],
			]
		);

		$this->assert_items_match_ids( $data );

		$item = $data['items'][0];
		$this->assertSame( admin_url( 'comment.php?action=editcomment&c=' . $item['id'] ), $item['edit_url'] );
		$this->assertContains( get_the_title( (int) $item['details']['comment_post_ID'] ), wp_list_pluck( $item['summary'], 'value' ) );
		$this->assertSame( get_comment( $item['id'] )->comment_author, $item['summary'][0]['value'] );
	}
}
