<?php
/**
 * Tests for the Settings page "Erase faked data" request.
 *
 * @since 0.10.1
 *
 * @package FakerPress\Tests\Admin
 */

namespace FakerPress\Tests\Admin;

use FakerPress\Admin\View\Settings_View;
use FakerPress\Module\Post;
use function FakerPress\make;

/**
 * Class SettingsEraseTest
 *
 * Regression coverage for issue #251: in 0.10.0 the React Settings page posted the REST nonce
 * (`wp_rest`) as `_wpnonce`, while `parse_request()` checks `fakerpress.request.settings`, so
 * erasing ended on "The link you followed has expired." and nothing was deleted.
 *
 * @since 0.10.1
 */
class SettingsEraseTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Request globals saved before each test.
	 *
	 * @var array
	 */
	private $saved = [];

	/**
	 * @inheritDoc
	 */
	public function setUp(): void {
		parent::setUp();

		$this->saved = [
			'post'    => $_POST,
			'request' => $_REQUEST,
			'method'  => $_SERVER['REQUEST_METHOD'] ?? null,
		];

		wp_set_current_user( static::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	/**
	 * @inheritDoc
	 */
	public function tearDown(): void {
		$_POST    = $this->saved['post'];
		$_REQUEST = $this->saved['request'];

		if ( null === $this->saved['method'] ) {
			unset( $_SERVER['REQUEST_METHOD'] );
		} else {
			$_SERVER['REQUEST_METHOD'] = $this->saved['method'];
		}

		parent::tearDown();
	}

	/**
	 * The Settings view's request nonce action is the one `parse_request()` checks.
	 *
	 * @test
	 */
	public function it_should_use_the_settings_request_nonce_action(): void {
		$action = Settings_View::get_request_nonce_action();

		$this->assertSame( 'fakerpress.request.settings', $action );
		$this->assertNotFalse( wp_verify_nonce( wp_create_nonce( $action ), $action ) );
		$this->assertFalse(
			wp_verify_nonce( wp_create_nonce( 'wp_rest' ), $action ),
			'A REST nonce must not pass for the settings request; that was the 0.10.0 bug.'
		);
	}

	/**
	 * A POST carrying the localized request nonce and the phrase erases flagged content.
	 *
	 * @test
	 */
	public function it_should_erase_flagged_posts_with_the_request_nonce(): void {
		$fake_id = static::factory()->post->create();
		update_post_meta( $fake_id, Post::get_flag(), 1 );
		$real_id = static::factory()->post->create();

		$this->post_erase( wp_create_nonce( Settings_View::get_request_nonce_action() ) );

		make( Settings_View::class )->parse_request();

		$this->assertNull( get_post( $fake_id ), 'The FakerPress post should be erased.' );
		$this->assertNotNull( get_post( $real_id ), 'Posts FakerPress did not create stay.' );
	}

	/**
	 * Fill the request globals the way the Settings page form posts them.
	 *
	 * @param string $nonce Value for `_wpnonce`.
	 *
	 * @return void
	 */
	private function post_erase( string $nonce ): void {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_POST                     = [
			'_wpnonce'   => $nonce,
			'fakerpress' => [
				'erase_phrase' => 'Let it Go!',
				'actions'      => [ 'delete' => '1' ],
			],
		];
		$_REQUEST                  = $_POST;
	}
}
