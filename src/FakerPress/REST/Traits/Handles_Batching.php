<?php
/**
 * Trait for handling batching functionality in REST API endpoints.
 *
 * Provides common batching logic for generation endpoints to handle
 * large requests that need to be split into smaller batches.
 *
 * @since TBD
 * @package FakerPress
 */

namespace FakerPress\REST\Traits;

/**
 * Trait Handles_Batching
 *
 * Provides batching functionality for generation endpoints.
 *
 * @since 0.9.0
 */
trait Handles_Batching {
	use Describes_Generated_Items;

	/**
	 * Calculate the quantity to generate with batching support.
	 *
	 * @since 0.9.0
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
				$qty      += $total - ( $offset + $allowed );
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
	 * @since 0.9.0
	 * @since 0.10.0 Adds `items` and `list_url` when the item type is given.
	 *
	 * @param array       $results         The generation results.
	 * @param array       $batch_info      Batching information from calculate_batched_quantity.
	 * @param array       $formatted_links Formatted links for the generated items.
	 * @param float       $generation_time Time taken for generation.
	 * @param string|null $item_type       Type of item (post, user, term, comment or attachment), to describe each one.
	 *
	 * @return array
	 */
	protected function build_batched_response_data( $results, $batch_info, $formatted_links, $generation_time, $item_type = null ) {
		$response_data = [
			'generated' => count( $results ),
			'ids'       => $results,
			'links'     => $formatted_links,
			'time'      => round( $generation_time, 3 ),
		];

		if ( null !== $item_type ) {
			$response_data['items']    = $this->describe_generated_items( $item_type, $results );
			$response_data['list_url'] = $this->get_generated_items_list_url( $item_type, $response_data['items'] );
		}

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
	 * @since 0.9.0
	 *
	 * @param int    $generated_count Number of items generated in this batch.
	 * @param string $item_type       Type of items (posts, users, etc.).
	 * @param array  $batch_info      Batching information.
	 *
	 * @return string
	 */
	protected function format_batched_success_message( $generated_count, $item_type, $batch_info ) {
		$message = sprintf(
			/* translators: 1: Number of items generated, 2: Item type label (singular or plural). */
			__( 'Successfully generated %1$d %2$s.', 'fakerpress' ),
			$generated_count,
			$this->get_batched_item_label( $item_type, $generated_count )
		);

		// Add batching information if this is part of a larger batch.
		if ( $batch_info['is_capped'] || $batch_info['offset'] > 0 ) {
			$current_total = $batch_info['offset'] + $batch_info['quantity'];
			$message      .= ' ' . sprintf(
				/* translators: 1: Number of items generated so far, 2: Total number of items requested. */
				__( '(Batch %1$d of %2$d total)', 'fakerpress' ),
				$current_total,
				$batch_info['total']
			);
		}

		return $message;
	}

	/**
	 * Gets the translated singular or plural label for a generated item type.
	 *
	 * Each known type uses a literal `_n()` call so the strings can be extracted for translation.
	 *
	 * @since 0.10.0
	 *
	 * @param string $item_type Type of item: post, user, term, comment or attachment.
	 * @param int    $count     Number of items, used to pick the singular or plural form.
	 *
	 * @return string The label for the item type.
	 */
	protected function get_batched_item_label( $item_type, $count ) {
		switch ( $item_type ) {
			case 'post':
				return _n( 'post', 'posts', $count, 'fakerpress' );
			case 'user':
				return _n( 'user', 'users', $count, 'fakerpress' );
			case 'term':
				return _n( 'term', 'terms', $count, 'fakerpress' );
			case 'comment':
				return _n( 'comment', 'comments', $count, 'fakerpress' );
			case 'attachment':
				return _n( 'attachment', 'attachments', $count, 'fakerpress' );
			default:
				// Unknown types keep the untranslated label with a plural "s", as _n() did without a translation.
				return 1 === (int) $count ? $item_type : $item_type . 's';
		}
	}
} 
