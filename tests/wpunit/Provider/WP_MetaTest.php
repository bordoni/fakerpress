<?php
/**
 * Tests for the WP_Meta Faker provider.
 *
 * Locks in the fix for the wp.org bug report:
 * "Fatal error when using date option in meta field rules" where calling
 * meta_type_date triggered "Class 'FakerPress\Provider\Chronos' not found"
 * because Chronos was not imported into the file's namespace.
 *
 * @since 0.9.1
 *
 * @package FakerPress\Tests\Provider
 */

namespace FakerPress\Tests\Provider;

use FakerPress\Provider\WP_Meta;
use FakerPress\ThirdParty\Cake\Chronos\Chronos;
use FakerPress\ThirdParty\Faker\Factory;
use FakerPress\ThirdParty\Faker\Generator;

/**
 * Class WP_MetaTest
 *
 * @since 0.9.1
 */
class WP_MetaTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Build a Faker generator with the WP_Meta provider attached.
	 *
	 * @since 0.9.1
	 *
	 * @return Generator
	 */
	private function make_generator(): Generator {
		$faker = Factory::create();
		$faker->addProvider( new WP_Meta( $faker ) );

		return $faker;
	}

	/**
	 * Verify meta_type_date returns a formatted string inside the requested interval.
	 *
	 * @test
	 */
	public function it_should_generate_a_date_meta_value_with_default_format(): void {
		$faker  = $this->make_generator();
		$min    = '2020-01-01';
		$max    = '2020-12-31';
		$result = $faker->meta_type_date(
			[
				'min' => $min,
				'max' => $max,
			],
			'Y-m-d H:i:s',
			100
		);

		$this->assertIsString( $result );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result );

		$generated = Chronos::parse( $result );
		$this->assertTrue( $generated->greaterThanOrEquals( Chronos::parse( $min )->startOfDay() ) );
		$this->assertTrue( $generated->lessThanOrEquals( Chronos::parse( $max )->endOfDay() ) );
	}

	/**
	 * Verify that an invalid min date falls back to today rather than fataling.
	 *
	 * @test
	 */
	public function it_should_fall_back_to_today_when_min_is_invalid(): void {
		$faker = $this->make_generator();

		$result = $faker->meta_type_date(
			[
				'min' => 'not-a-date',
				'max' => Chronos::today()->addDays( 30 )->toDateString(),
			],
			'Y-m-d H:i:s',
			100
		);

		$this->assertIsString( $result );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $result );
	}

	/**
	 * Verify the route the Meta module actually takes — calling meta_type_date via
	 * Faker's Generator::format, which is what triggered the original fatal in the
	 * user's stack trace.
	 *
	 * @test
	 */
	public function it_should_handle_meta_type_date_via_faker_generator(): void {
		$faker = $this->make_generator();

		$result = $faker->format(
			'meta_type_date',
			[
				[
					'min' => '2021-06-01',
					'max' => '2021-06-30',
				],
				'Y-m-d H:i:s',
				100,
			]
		);

		$this->assertIsString( $result );
		$this->assertMatchesRegularExpression( '/^2021-06-\d{2} \d{2}:\d{2}:\d{2}$/', $result );
	}
}
