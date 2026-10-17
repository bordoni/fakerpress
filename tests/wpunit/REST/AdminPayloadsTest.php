<?php
/**
 * Tests that each generate endpoint accepts the payload the React admin sends.
 *
 * @since TBD
 *
 * @package FakerPress\Tests\REST
 */

namespace FakerPress\Tests\REST;

use FakerPress\Provider\Image\Placeholder;
use FakerPress\Provider\WP_Meta;
use FakerPress\Tests\Traits\REST_Test_Case;
use FakerPress\ThirdParty\Faker\Factory;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class AdminPayloadsTest
 *
 * The payloads below mirror what `src/resources/packages/admin/lib/transform.ts` builds, sent as JSON
 * the way `@wordpress/api-fetch` sends them. HTTP is mocked, so attachments get a local JPEG.
 *
 * @since TBD
 */
class AdminPayloadsTest extends \Codeception\TestCase\WPTestCase {
	use REST_Test_Case;

	/**
	 * Body served for every mocked HTTP request.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private string $image_body = '';

	/**
	 * Content type served for every mocked HTTP request.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	private string $image_type = 'image/jpeg';

	/**
	 * A meta rule as the React rules UI sends it.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	private array $email_rule = [
		'type'   => 'email',
		'name'   => 'fp_admin_email',
		'weight' => 100,
	];

	/**
	 * Set up each test.
	 *
	 * @since TBD
	 */
	public function set_up(): void {
		parent::set_up();
		$this->init_rest_server();
		$this->set_admin_user();

		// A 1×1 JPEG.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$this->image_body = base64_decode(
			'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'
			. 'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwh'
			. 'MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAAR'
			. 'CAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAA'
			. 'AAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMR'
			. 'AD8AKwA//9k='
		);

		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * Tear down each test.
	 *
	 * @since TBD
	 */
	public function tear_down(): void {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10 );
		parent::tear_down();
	}

	/**
	 * Serve the configured image for every HTTP request.
	 *
	 * @since TBD
	 *
	 * @param false|array $preempt Whether to preempt the request.
	 * @param array       $args    HTTP request arguments.
	 * @param string      $url     The request URL.
	 *
	 * @return array
	 */
	public function mock_http_request( $preempt, $args, $url ): array {
		if ( ! empty( $args['filename'] ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $args['filename'], $this->image_body );
		}

		return [
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'headers'  => [
				'content-type'   => $this->image_type,
				'content-length' => strlen( $this->image_body ),
			],
			'body'     => $this->image_body,
			'filename' => $args['filename'] ?? '',
		];
	}

	/**
	 * Dispatch a JSON request, as api-fetch does.
	 *
	 * @since TBD
	 *
	 * @param string $module Endpoint name, e.g. 'posts'.
	 * @param array  $body   Request body.
	 *
	 * @return WP_REST_Response
	 */
	private function generate( string $module, array $body ): WP_REST_Response {
		$request = new WP_REST_Request( 'POST', "/fakerpress/v1/{$module}/generate" );
		$request->set_header( 'Content-Type', 'application/json' );
		$request->set_body( wp_json_encode( $body ) );

		return rest_get_server()->dispatch( $request );
	}

	/**
	 * Assert a successful generation and return the generated IDs.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Response $response The response.
	 * @param int              $min      Fewest items expected.
	 * @param int              $max      Most items expected.
	 *
	 * @return int[]
	 */
	private function assert_generated( WP_REST_Response $response, int $min, int $max ): array {
		$this->assert_success_response( $response );
		$ids = $response->get_data()['data']['ids'];
		$this->assertGreaterThanOrEqual( $min, count( $ids ) );
		$this->assertLessThanOrEqual( $max, count( $ids ) );

		return array_map( 'intval', $ids );
	}

	/**
	 * Posts: lists as comma-separated strings, interval_date, qty range, taxonomy and meta rules.
	 *
	 * @test
	 */
	public function it_should_generate_posts_from_the_admin_payload(): void {
		$category = static::factory()->category->create();

		$ids = $this->assert_generated(
			$this->generate(
				'posts',
				[
					'qty'            => [
						'min' => 2,
						'max' => 3,
					],
					'interval_date'  => [
						'min' => '2026-01-01',
						'max' => '2026-01-31',
					],
					'post_types'     => 'post',
					'post_parent'    => '',
					'comment_status' => 'closed',
					'author'         => '',
					'use_html'       => true,
					'content_size'   => [ 1, 2 ],
					'html_tags'      => 'p,h2',
					'images_origin'  => '',
					'excerpt_size'   => [ 1, 2 ],
					'taxonomy'       => [
						[
							'taxonomies' => [ 'category' ],
							'terms'      => [ (string) $category ],
							'rate'       => 100,
							'qty'        => [ 1, 1 ],
						],
					],
					'meta'           => [ $this->email_rule ],
					'offset'         => 0,
				]
			),
			2,
			3
		);

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			$this->assertSame( 'post', $post->post_type );
			$this->assertSame( 'closed', $post->comment_status );
			$this->assertStringStartsWith( '2026-01-', $post->post_date );
			$this->assertContains( $category, wp_get_object_terms( $id, 'category', [ 'fields' => 'ids' ] ) );
			$this->assertNotFalse( is_email( get_post_meta( $id, 'fp_admin_email', true ) ) );
		}
	}

	/**
	 * Users: html_tags as a string and a boolean use_html used to fatal (explode() on an array / === 'on').
	 *
	 * @test
	 */
	public function it_should_generate_users_from_the_admin_payload(): void {
		$ids = $this->assert_generated(
			$this->generate(
				'users',
				[
					'qty'              => [
						'min' => 2,
						'max' => 2,
					],
					'roles'            => 'editor',
					'description_size' => [ 1, 2 ],
					'use_html'         => true,
					'html_tags'        => 'blockquote',
					'meta'             => [ $this->email_rule ],
					'offset'           => 0,
				]
			),
			2,
			2
		);

		foreach ( $ids as $id ) {
			$user = get_userdata( $id );
			$this->assertContains( 'editor', $user->roles );
			// WordPress runs wp_filter_kses on user bios, which keeps only inline tags such as blockquote.
			$this->assertStringContainsString( '<blockquote', $user->description );
			$this->assertNotFalse( is_email( get_user_meta( $id, 'fp_admin_email', true ) ) );
		}

		// And use_html: false keeps descriptions plain.
		$plain = $this->assert_generated(
			$this->generate(
				'users',
				[
					'qty'              => [
						'min' => 1,
						'max' => 1,
					],
					'roles'            => 'editor',
					'description_size' => [ 1, 2 ],
					'use_html'         => false,
					'html_tags'        => 'p',
					'offset'           => 0,
				]
			),
			1,
			1
		);
		$this->assertStringNotContainsString( '<', get_userdata( $plain[0] )->description );
	}

	/**
	 * Terms: `size` (not `name_size`) and taxonomies as a string.
	 *
	 * @test
	 */
	public function it_should_generate_terms_from_the_admin_payload(): void {
		$ids = $this->assert_generated(
			$this->generate(
				'terms',
				[
					'qty'        => [
						'min' => 2,
						'max' => 2,
					],
					'size'       => [ 12, 12 ],
					'taxonomies' => 'post_tag',
					'meta'       => [ $this->email_rule ],
					'offset'     => 0,
				]
			),
			2,
			2
		);

		foreach ( $ids as $id ) {
			$term = get_term( $id );
			$this->assertSame( 'post_tag', $term->taxonomy );
			// Faker varies sentence length by up to 40%; the default size (4) never reaches 7 words.
			$this->assertGreaterThanOrEqual( 7, str_word_count( $term->name ) );
			$this->assertNotFalse( is_email( get_term_meta( $id, 'fp_admin_email', true ) ) );
		}
	}

	/**
	 * Comments: type, post_types and html_tags as strings and interval_date.
	 *
	 * @test
	 */
	public function it_should_generate_comments_from_the_admin_payload(): void {
		$page = static::factory()->post->create( [ 'post_type' => 'page' ] );

		$ids = $this->assert_generated(
			$this->generate(
				'comments',
				[
					'type'          => 'comment',
					'post_types'    => 'page',
					'qty'           => [
						'min' => 2,
						'max' => 2,
					],
					'interval_date' => [
						'min' => '2026-02-01',
						'max' => '2026-02-28',
					],
					'content_size'  => [ 1, 2 ],
					'use_html'      => false,
					'html_tags'     => '',
					'meta'          => [ $this->email_rule ],
					'offset'        => 0,
				]
			),
			2,
			2
		);

		foreach ( $ids as $id ) {
			$comment = get_comment( $id );
			$this->assertSame( $page, (int) $comment->comment_post_ID );
			$this->assertStringStartsWith( '2026-02-', $comment->comment_date );
			$this->assertNotFalse( is_email( get_comment_meta( $id, 'fp_admin_email', true ) ) );
		}
	}

	/**
	 * Attachments: generate_* flags, {min,max} sizes, and meta rules generated rather than stored raw.
	 *
	 * @test
	 */
	public function it_should_generate_attachments_from_the_admin_payload(): void {
		$ids = $this->assert_generated(
			$this->generate(
				'attachments',
				[
					'qty'                  => [
						'min' => 1,
						'max' => 1,
					],
					'provider'             => 'placeholder',
					'width'                => [
						'min' => 100,
						'max' => 120,
					],
					'post_parent'          => [],
					'author'               => '',
					'generate_alt_text'    => true,
					'generate_caption'     => false,
					'generate_description' => false,
					'meta'                 => [ $this->email_rule ],
					'offset'               => 0,
				]
			),
			1,
			1
		);

		$id = $ids[0];
		$this->assertSame( 'attachment', get_post_type( $id ) );
		$this->assertNotEmpty( get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		$this->assertNotFalse( is_email( get_post_meta( $id, 'fp_admin_email', true ) ) );
		// Before the fix, each rule array was stored under its numeric index.
		$this->assertFalse( metadata_exists( 'post', $id, '0' ) );
	}

	/**
	 * Placehold.co serves SVG without an extension, and attachments reject SVG.
	 *
	 * @test
	 */
	public function it_should_ask_placehold_co_for_a_png(): void {
		$faker = Factory::create();
		$faker->addProvider( new Placeholder( $faker ) );

		$this->assertMatchesRegularExpression( '#^https://placehold\.co/150x80\.png$#', $faker->placeholder_image( 150, 80 ) );
	}

	/**
	 * A failed attachment (here: an SVG response) yields no meta value instead of pieces of the WP_Error.
	 *
	 * @test
	 */
	public function it_should_skip_the_attachment_meta_value_when_the_image_fails(): void {
		$this->image_body = '<svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"/>';
		$this->image_type = 'image/svg+xml';

		$faker = Factory::create();
		$faker->addProvider( new WP_Meta( $faker ) );
		$faker->set_meta_object( 'post', static::factory()->post->create() );

		$this->assertNull( $faker->meta_type_attachment( 'id', 'placeholder', 100 ) );
	}
}
