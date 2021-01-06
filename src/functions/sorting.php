<?php

/**
 * Sorting function based on Priority
 *
 * @since  0.5.1
 *
 * @param object|array $a First Subject to compare.
 * @param object|array $b Second subject to compare.
 *
 * @return int
 */
function fp_sort_by_priority( $a, $b ) {
	if ( is_array( $a ) ) {
		$a_priority = $a['priority'];
	} else {
		$a_priority = $a->priority;
	}

	if ( is_array( $b ) ) {
		$b_priority = $b['priority'];
	} else {
		$b_priority = $b->priority;
	}

	if ( (int) $a_priority === (int) $b_priority ) {
		return 0;
	}

	return (int) $a_priority < (int) $b_priority ? -1 : 1;
}