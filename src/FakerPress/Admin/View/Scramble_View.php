<?php
/**
 * Scramble admin view.
 *
 * @since 0.9.2
 * @package FakerPress
 */

namespace FakerPress\Admin\View;

use FakerPress\Admin;
use FakerPress\Module\User;
use FakerPress\ThirdParty\Faker\Factory as Faker_Factory;
use WP_User_Query;
use function FakerPress\get_request_var;

/**
 * Class Scramble_View.
 *
 * Replaces the names and emails of existing (real) users with realistic fake
 * data, so a cloned site can be demoed without exposing real customer info.
 *
 * @since 0.9.2
 *
 * @package FakerPress\Admin\View
 */
class Scramble_View extends Abstract_View {

	/**
	 * Maximum users scrambled in a single run. Protects against timing out on
	 * very large user tables; remaining users are eligible on the next run.
	 *
	 * @since 0.9.2
	 */
	const BATCH_LIMIT = 500;

	/**
	 * How many times to retry a colliding generated email before giving up on a user.
	 *
	 * @since 0.9.2
	 */
	const EMAIL_RETRIES = 5;

	/**
	 * Meta key used to mark a user as already scrambled so re-runs skip it.
	 *
	 * @since 0.9.2
	 */
	const SCRAMBLED_FLAG = '_fakerpress_scrambled';

	/**
	 * @inheritDoc
	 */
	public static function get_slug(): string {
		return 'scramble';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label(): string {
		return esc_attr__( 'Scramble', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_title(): string {
		return esc_attr__( 'Scramble Users', 'fakerpress' );
	}

	/**
	 * @inheritDoc
	 */
	public function has_menu(): bool {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_capability_required(): string {
		return 'manage_options';
	}

	/**
	 * @inheritDoc
	 */
	public function get_menu_priority(): int {
		return 15;
	}

	/**
	 * Handle the scramble form submission.
	 *
	 * Mirrors the destructive-action flow in Settings_View: a typed confirmation
	 * phrase gates an irreversible operation on real data.
	 *
	 * @since 0.9.2
	 *
	 * @return false|string
	 */
	public function parse_request() {
		// The Abstract just checks for a nonce actually super handy.
		if ( ! parent::parse_request() ) {
			return false;
		}

		$scramble_intention = is_string( get_request_var( [ 'fakerpress', 'actions', 'scramble' ] ) );
		$scramble_check     = in_array(
			strtolower( get_request_var( [ 'fakerpress', 'scramble_phrase' ] ) ),
			[
				'scramble',
				'scramble!',
			],
			true
		);

		if ( ! $scramble_intention ) {
			return false;
		}

		if ( ! $scramble_check ) {
			return Admin::add_message( __( 'The verification to scramble the users has failed, you have to type it...', 'fakerpress' ), 'error' );
		}

		if ( ! current_user_can( $this->get_capability_required() ) ) {
			return Admin::add_message( __( 'You do not have the permissions to scramble users!', 'fakerpress' ), 'error' );
		}

		$scrambled = 0;
		$failed    = 0;

		// Never scramble the admin running the action, and skip users FakerPress
		// already generated as well as users scrambled on a previous run.
		$query = new WP_User_Query(
			[
				'fields'     => 'ID',
				'number'     => static::BATCH_LIMIT,
				'exclude'    => [ get_current_user_id() ],
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => static::SCRAMBLED_FLAG,
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => User::get_flag(),
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		$user_ids = array_map( 'absint', $query->get_results() );
		$faker    = Faker_Factory::create();

		foreach ( $user_ids as $user_id ) {
			$first = $faker->firstName();
			$last  = $faker->lastName();

			$login = strtolower( "{$first}.{$last}" );

			$update = [
				'ID'           => $user_id,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => "$first $last",
				'nickname'     => $login,
				// user_login is left intact on purpose: changing it breaks logins
				// and display_name is what shows publicly anyway.
			];

			// Emails must be unique in wp_users, so retry on a collision.
			$result = null;
			for ( $attempt = 0; $attempt < static::EMAIL_RETRIES; $attempt++ ) {
				$update['user_email'] = $faker->safeEmail();

				$result = wp_update_user( $update );

				if ( ! is_wp_error( $result ) ) {
					break;
				}
			}

			if ( is_wp_error( $result ) ) {
				++$failed;
				continue;
			}

			update_user_meta( $user_id, static::SCRAMBLED_FLAG, 1 );
			++$scrambled;
		}

		$total = count( $user_ids );

		if ( $scrambled ) {
			Admin::add_message(
				sprintf(
					/* translators: %d: number of users scrambled. */
					_n( 'Successfully scrambled %d user.', 'Successfully scrambled %d users.', $scrambled, 'fakerpress' ),
					$scrambled
				),
				'success'
			);
		}

		if ( $failed ) {
			Admin::add_message(
				sprintf(
					/* translators: %d: number of users that could not be scrambled. */
					_n( '%d user could not be scrambled (skipped, remains eligible).', '%d users could not be scrambled (skipped, remain eligible).', $failed, 'fakerpress' ),
					$failed
				),
				'error'
			);
		}

		if ( 0 === $scrambled && 0 === $failed ) {
			Admin::add_message( __( 'No users were available to scramble. Users already scrambled or generated by FakerPress are skipped.', 'fakerpress' ), 'info' );
		}

		// Let the admin know if the batch limit was reached and more remain.
		if ( $total >= static::BATCH_LIMIT ) {
			Admin::add_message(
				sprintf(
					/* translators: %d: batch limit reached, more users remain. */
					__( 'Reached the batch limit of %d users; run again to scramble any remaining users.', 'fakerpress' ),
					static::BATCH_LIMIT
				),
				'info'
			);
		}

		return true;
	}
}
