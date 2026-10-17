<?php
/**
 * Tests for the WP_User provider.
 *
 * @since TBD
 *
 * @package FakerPress\Tests\Provider
 */

namespace FakerPress\Tests\Provider;

use FakerPress\Provider\WP_User;
use FakerPress\ThirdParty\Faker\Factory;

/**
 * Class WP_UserTest
 *
 * @since TBD
 */
class WP_UserTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Faker sometimes builds URLs longer than the 100 characters wp_insert_user() accepts, which made
	 * user generation silently come up short (e.g. 5 of 6 users).
	 *
	 * @test
	 */
	public function it_should_keep_generated_user_urls_within_the_wordpress_limit(): void {
		$faker = Factory::create();
		$faker->addProvider( new WP_User( $faker ) );

		for ( $i = 0; $i < 2000; $i++ ) {
			$url = $faker->user_url();
			$this->assertLessThanOrEqual( 100, mb_strlen( $url ), $url );
			$this->assertNotFalse( filter_var( $url, FILTER_VALIDATE_URL ), $url );
		}
	}

	/**
	 * A URL passed in is used as-is.
	 *
	 * @test
	 */
	public function it_should_return_a_given_user_url_unchanged(): void {
		$faker = Factory::create();
		$faker->addProvider( new WP_User( $faker ) );

		$this->assertSame( 'https://example.com/me', $faker->user_url( 'https://example.com/me' ) );
	}
}
