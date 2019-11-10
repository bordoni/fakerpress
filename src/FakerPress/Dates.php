<?php
namespace FakerPress;

use \Carbon\Carbon;

Class Dates {

	public static function get_intervals(){
		return apply_filters(
			'fakerpress/date.get_intervals',
			[
				[
					'id' => 'today',
					'text' => esc_attr__( 'Today', 'fakerpress' ),
					'min' => Carbon::today()->toDateString(),
					'max' => Carbon::today()->toDateString(),
				],
				[
					'id' => 'yesterday',
					'text' => esc_attr__( 'Yesterday', 'fakerpress' ),
					'min' => Carbon::yesterday()->toDateString(),
					'max' => Carbon::yesterday()->toDateString(),
				],
				[
					'id' => 'tomorrow',
					'text' => esc_attr__( 'Tomorrow', 'fakerpress' ),
					'min' => Carbon::tomorrow()->toDateString(),
					'max' => Carbon::tomorrow()->toDateString(),
				],
				[
					'id' => 'this week',
					'text' => esc_attr__( 'This week', 'fakerpress' ),
					'min' => ( Carbon::today()->dayOfWeek === 1 ? Carbon::today()->toDateString() : Carbon::parse( 'last monday' )->toDateString() ),
					'max' => ( Carbon::today()->dayOfWeek === 0 ? Carbon::today()->toDateString() : Carbon::parse( 'next sunday' )->toDateString() ),
				],
				[
					'id' => 'this month',
					'text' => esc_attr__( 'This month', 'fakerpress' ),
					'min' => Carbon::today()->day( 1 )->toDateString(),
					'max' => Carbon::parse( 'last day of this month' )->toDateString(),
				],
				[
					'id' => 'this year',
					'text' => esc_attr__( 'This year', 'fakerpress' ),
					'min' => Carbon::today()->day( 1 )->month( 1 )->toDateString(),
					'max' => Carbon::parse( 'last day of december' )->toDateString(),
				],
				[
					'id' => 'last 15 days',
					'text' => esc_attr__( 'Last 15 days', 'fakerpress' ),
					'min' => Carbon::today()->subDays( 15 )->toDateString(),
					'max' => Carbon::today()->toDateString(),
				],
				[
					'id' => 'next 15 days',
					'text' => esc_attr__( 'Next 15 Days', 'fakerpress' ),
					'min' => Carbon::today()->toDateString(),
					'max' => Carbon::today()->addDays( 15 )->toDateString(),
				],
			]
		);
	}

}
