<?php
/**
 * Trait for handling batching functionality in REST API endpoints.
 *
 * Provides common batching logic for generation endpoints to handle
 * large requests that need to be split into smaller batches.
 *
 * @since   TBD
 * @package FakerPress
 */

namespace FakerPress\REST\Traits;

/**
 * Trait Handles_Batching
 *
 * Provides batching functionality for generation endpoints.
 *
 * @since TBD
 */
trait Handles_Batching {

	/**
	 * Calculate the quantity to generate with batching support.
	 *
	 * @since TBD
	 *
	 * @param array $params Request parameters.
	 * @param mixed $module The module instance.
	 *
	 * @return array Array containing quantity, offset, total, and is_capped.
	 */
	protected function calculate_batched_quantity( $params, $module ) {
		$allowed = $module->get_amount_allowed();
		$offset  = absint( $params['offset'] ?? 0 );
		$total   = absint( $params['total'] ?? 0 );
		$qty     = $total;

		// If no total is set, calculate from quantity or qty range.
		if ( ! $total ) {
			$qty = $params['quantity'] ?? 10;

			// Handle quantity range.
			if ( isset( $params['qty'] ) && is_array( $params['qty'] ) ) {
				$min = absint( $params['qty']['min'] ?? 1 );
				$max = max( absint( $params['qty']['max'] ?? $min ), $min );
				$qty = $module->get_faker()->numberBetween( $min, $max );
			}
			$total = $qty;
		}

		$is_capped = false;

		// Check if we need to cap the quantity.
		if ( $qty > $allowed ) {
			$is_capped = true;
			$qty       = $allowed;

			// Adjust for final batch.
			if ( $total < $offset + $allowed ) {
				$qty       += $total - ( $offset + $allowed );
				$is_capped = false;
			}
		}

		// Ensure minimum quantity.
		$qty = max( 1, $qty );

		return [
			'quantity'  => $qty,
			'offset'    => $offset,
			'total'     => $total,
			'is_capped' => $is_capped,
		];
	}

	/**
	 * Build batched response data.
	 *
	 * @since TBD
	 *
	 * @param array $results        The generation results.
	 * @param array $batch_info     Batching information from calculate_batched_quantity.
	 * @param array $formatted_links Formatted links for the generated items.
	 * @param float $generation_time Time taken for generation.
	 *
	 * @return array
	 */
	protected function build_batched_response_data( $results, $batch_info, $formatted_links, $generation_time ) {
		$response_data = [
			'generated' => count( $results ),
			'ids'       => $results,
			'links'     => $formatted_links,
			'time'      => round( $generation_time, 3 ),
		];

		// Add batching information if this is a batched request.
		if ( $batch_info['is_capped'] || $batch_info['offset'] > 0 ) {
			$response_data['is_capped'] = $batch_info['is_capped'];
			$response_data['offset']    = $batch_info['offset'] + $batch_info['quantity'];
			$response_data['total']     = $batch_info['total'];
		}

		return $response_data;
	}

	/**
	 * Format success message with batching information.
	 *
	 * @since TBD
	 *
	 * @param int    $generated_count Number of items generated in this batch.
	 * @param string $item_type       Type of items (posts, users, etc.).
	 * @param array  $batch_info      Batching information.
	 *
	 * @return string
	 */
	protected function format_batched_success_message( $generated_count, $item_type, $batch_info ) {
		$message = sprintf(
			__( 'Successfully generated %d %s.', 'fakerpress' ),
			$generated_count,
			_n( $item_type, $item_type . 's', $generated_count, 'fakerpress' )
		);

		// Add batching information if this is part of a larger batch.
		if ( $batch_info['is_capped'] || $batch_info['offset'] > 0 ) {
			$current_total = $batch_info['offset'] + $batch_info['quantity'];
			$message .= ' ' . sprintf(
				__( '(Batch %d of %d total)', 'fakerpress' ),
				$current_total,
				$batch_info['total']
			);
		}

		return $message;
	}
} 
