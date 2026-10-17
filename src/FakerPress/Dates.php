<?php
/**
 * Date interval presets used by the date range fields.
 *
 * @package FakerPress
 */

namespace FakerPress;

use FakerPress\ThirdParty\Cake\Chronos\Chronos;

/**
 * Builds the named date intervals (today, this week, last 15 days, ...) offered by date fields.
 */
class Dates {

	/**
	 * Gets the list of date interval presets, with start and end dates computed relative to today.
	 *
	 * @return array List of intervals, each an array with `id`, translated `text`, and `min` / `max`
	 *               dates as `Y-m-d` strings. Filtered by `fakerpress/date.get_intervals`.
	 */
	public static function get_intervals() {
		return apply_filters(
			'fakerpress/date.get_intervals',
			[
				[
					'id'   => 'today',
					'text' => esc_attr__( 'Today', 'fakerpress' ),
					'min'  => Chronos::today()->toDateString(),
					'max'  => Chronos::today()->toDateString(),
				],
				[
					'id'   => 'yesterday',
					'text' => esc_attr__( 'Yesterday', 'fakerpress' ),
					'min'  => Chronos::yesterday()->toDateString(),
					'max'  => Chronos::yesterday()->toDateString(),
				],
				[
					'id'   => 'tomorrow',
					'text' => esc_attr__( 'Tomorrow', 'fakerpress' ),
					'min'  => Chronos::tomorrow()->toDateString(),
					'max'  => Chronos::tomorrow()->toDateString(),
				],
				[
					'id'   => 'this week',
					'text' => esc_attr__( 'This week', 'fakerpress' ),
					'min'  => ( Chronos::today()->dayOfWeek === 1 ? Chronos::today()->toDateString() : Chronos::parse( 'last monday' )->toDateString() ),
					'max'  => ( Chronos::today()->dayOfWeek === 0 ? Chronos::today()->toDateString() : Chronos::parse( 'next sunday' )->toDateString() ),
				],
				[
					'id'   => 'this month',
					'text' => esc_attr__( 'This month', 'fakerpress' ),
					'min'  => Chronos::today()->day( 1 )->toDateString(),
					'max'  => Chronos::parse( 'last day of this month' )->toDateString(),
				],
				[
					'id'   => 'this year',
					'text' => esc_attr__( 'This year', 'fakerpress' ),
					'min'  => Chronos::today()->day( 1 )->month( 1 )->toDateString(),
					'max'  => Chronos::parse( 'last day of december' )->toDateString(),
				],
				[
					'id'   => 'last 15 days',
					'text' => esc_attr__( 'Last 15 days', 'fakerpress' ),
					'min'  => Chronos::today()->subDays( 15 )->toDateString(),
					'max'  => Chronos::today()->toDateString(),
				],
				[
					'id'   => 'next 15 days',
					'text' => esc_attr__( 'Next 15 Days', 'fakerpress' ),
					'min'  => Chronos::today()->toDateString(),
					'max'  => Chronos::today()->addDays( 15 )->toDateString(),
				],
			]
		);
	}
}
