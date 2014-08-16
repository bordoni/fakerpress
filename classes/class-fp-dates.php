<?php
namespace FakerPress;

use \Carbon\Carbon;

Class Dates {

	public static function get_intervals(){
		return apply_filters( 
			'fakerpress/date.get_intervals',
			array(
				array(
					'id' => 'today',
					'text' => esc_attr__( 'Today', 'fakerpress' ),
					'min' => Carbon::today(),
					'max' => Carbon::today(),
				),
				array(
					'id' => 'yesterday',
					'text' => esc_attr__( 'Yesterday', 'fakerpress' ),
					'min' => Carbon::yesterday(),
					'max' => Carbon::yesterday(),
				),
				array(
					'id' => 'tomorrow',
					'text' => esc_attr__( 'Tomorrow', 'fakerpress' ),
					'min' => Carbon::tomorrow(),
					'max' => Carbon::tomorrow(),
				),
				array(
					'id' => 'this week',
					'text' => esc_attr__( 'This week', 'fakerpress' ),
					'min' => ( Carbon::today()->dayOfWeek === 1 ? Carbon::today() : Carbon::parse( 'last monday' ) ),
					'max' => ( Carbon::today()->dayOfWeek === 0 ? Carbon::today() : Carbon::parse( 'next sunday' ) ),
				),
				array(
					'id' => 'this month',
					'text' => esc_attr__( 'This month', 'fakerpress' ),
					'min' => Carbon::today()->day( 1 ),
					'max' => Carbon::parse( 'last day of this month' ),
				),
				array(
					'id' => 'this year',
					'text' => esc_attr__( 'This year', 'fakerpress' ),
					'min' => Carbon::today()->day( 1 )->month( 1 ),
					'max' =>  Carbon::parse( 'last day of december' ),
				),
				array(
					'id' => 'last 15 days',
					'text' => esc_attr__( 'Last 15 days', 'fakerpress' ),
					'min' => Carbon::today()->subDays( 15 ),
					'max' => Carbon::today(),
				),
				array(
					'id' => 'next 15 days',
					'text' => esc_attr__( 'Next 15 Days', 'fakerpress' ),
					'min' => Carbon::today(),
					'max' => Carbon::today()->addDays( 15 ),
				),
			)
		);
	}

}
