<?php

namespace FakerPress\Tests\Admin;

use FakerPress\Admin\View\Settings_View;
use FakerPress\Admin\View\Term_View;
use FakerPress\Admin\View\Post_View;
use FakerPress\Admin\View\User_View;
use FakerPress\Admin\View\Attachment_View;
use function FakerPress\make;

/**
 * Tests for page configuration data localized to React scripts.
 *
 * @since 0.9.0
 */
class PageConfigTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify that Term_View page data includes taxonomies.
	 *
	 * @test
	 */
	public function it_should_include_taxonomies_in_terms_page_data(): void {
		$view = make( Term_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'taxonomies', $data );
		$this->assertNotEmpty( $data['taxonomies'] );
	}

	/**
	 * Verify that Post_View page data includes post_types.
	 *
	 * @test
	 */
	public function it_should_include_post_types_in_posts_page_data(): void {
		$view = make( Post_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'post_types', $data );
		$this->assertNotEmpty( $data['post_types'] );
	}

	/**
	 * Verify that Post_View page data includes html_tags.
	 *
	 * @test
	 */
	public function it_should_include_html_tags_in_posts_page_data(): void {
		$view = make( Post_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'html_tags', $data );
		$this->assertIsArray( $data['html_tags'] );
		$this->assertContains( 'p', $data['html_tags'] );
	}

	/**
	 * Verify that User_View page data includes roles.
	 *
	 * @test
	 */
	public function it_should_include_roles_in_users_page_data(): void {
		$view = make( User_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'roles', $data );
		$this->assertNotEmpty( $data['roles'] );
	}

	/**
	 * Verify that Attachment_View page data includes image_providers.
	 *
	 * @test
	 */
	public function it_should_include_image_providers_in_attachments_page_data(): void {
		$view = make( Attachment_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'image_providers', $data );
		$this->assertNotEmpty( $data['image_providers'] );
	}

	/**
	 * Verify that Settings_View page data includes erase_phrase.
	 *
	 * @test
	 */
	public function it_should_include_erase_phrase_in_settings_page_data(): void {
		$view = make( Settings_View::class );
		$data = $this->invoke_get_page_data( $view );

		$this->assertArrayHasKey( 'erase_phrase', $data );
		$this->assertEquals( 'Let it Go!', $data['erase_phrase'] );
	}

	/**
	 * Helper: invoke the protected get_page_data() method via reflection.
	 *
	 * @param object $view The view instance.
	 *
	 * @return array
	 */
	private function invoke_get_page_data( $view ): array {
		$reflection = new \ReflectionMethod( $view, 'get_page_data' );
		$reflection->setAccessible( true );

		return $reflection->invoke( $view );
	}
}
