<?php
/**
 * Tests for the WP_Post Faker provider.
 *
 * Locks in the fix for the wp.org bug report:
 * "Fatal error when using date option in meta field rules" where a preceding
 * "Undefined property: stdClass::$terms" warning was emitted by WP_Post::tax_input
 * when the supplied config did not include a `terms` key.
 *
 * @since 0.9.1
 *
 * @package FakerPress\Tests\Provider
 */

namespace FakerPress\Tests\Provider;

use FakerPress\Provider\WP_Post;
use FakerPress\ThirdParty\Faker\Factory;
use FakerPress\ThirdParty\Faker\Generator;

/**
 * Class WP_PostTest
 *
 * @since 0.9.1
 */
class WP_PostTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Build a Faker generator with the WP_Post provider attached.
	 *
	 * @since 0.9.1
	 *
	 * @return Generator
	 */
	private function make_generator(): Generator {
		$faker = Factory::create();
		$faker->addProvider( new WP_Post( $faker ) );

		return $faker;
	}

	/**
	 * Verify that tax_input does not emit an "Undefined property" warning when the
	 * config rows do not include `terms` or `taxonomies` keys.
	 *
	 * The original bug surfaced as:
	 *   PHP Warning: Undefined property: stdClass::$terms in WP_Post.php on line 224
	 *
	 * @test
	 */
	public function it_should_handle_tax_input_config_without_terms_key(): void {
		$faker = $this->make_generator();

		$previous_handler = set_error_handler(
			static function ( int $errno, string $errstr, string $errfile, int $errline ) {
				throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
			},
			E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE
		);

		try {
			$result = $faker->tax_input(
				[
					[ 'taxonomies' => 'category' ],
				]
			);

			$this->assertIsArray( $result );
		} finally {
			if ( null !== $previous_handler ) {
				set_error_handler( $previous_handler );
			} else {
				restore_error_handler();
			}
		}
	}

	/**
	 * Sanity: a fully empty config row should also not warn.
	 *
	 * @test
	 */
	public function it_should_handle_empty_tax_input_config_row(): void {
		$faker = $this->make_generator();

		$previous_handler = set_error_handler(
			static function ( int $errno, string $errstr, string $errfile, int $errline ) {
				throw new \ErrorException( $errstr, 0, $errno, $errfile, $errline );
			},
			E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE
		);

		try {
			$result = $faker->tax_input( [ [] ] );
			$this->assertIsArray( $result );
		} finally {
			if ( null !== $previous_handler ) {
				set_error_handler( $previous_handler );
			} else {
				restore_error_handler();
			}
		}
	}
}
