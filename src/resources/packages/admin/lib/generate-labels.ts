import { _n, sprintf } from '@wordpress/i18n';

/**
 * The modules that have a generate page.
 */
export type GenerateModule =
	| 'posts'
	| 'users'
	| 'terms'
	| 'comments'
	| 'attachments';

/**
 * "9 posts generated", with the right noun and plural for the module.
 *
 * Each module has its own literal `_n()` call so the strings can be extracted for translation.
 *
 * @param module Module that generated the items.
 * @param count  How many items were generated.
 * @return The headline.
 */
export function generatedHeadline(
	module: GenerateModule,
	count: number
): string {
	switch ( module ) {
		case 'posts':
			return sprintf(
				/* translators: %d: Number of posts generated. */
				_n(
					'%d post generated',
					'%d posts generated',
					count,
					'fakerpress'
				),
				count
			);
		case 'users':
			return sprintf(
				/* translators: %d: Number of users generated. */
				_n(
					'%d user generated',
					'%d users generated',
					count,
					'fakerpress'
				),
				count
			);
		case 'terms':
			return sprintf(
				/* translators: %d: Number of terms generated. */
				_n(
					'%d term generated',
					'%d terms generated',
					count,
					'fakerpress'
				),
				count
			);
		case 'comments':
			return sprintf(
				/* translators: %d: Number of comments generated. */
				_n(
					'%d comment generated',
					'%d comments generated',
					count,
					'fakerpress'
				),
				count
			);
		case 'attachments':
			return sprintf(
				/* translators: %d: Number of attachments generated. */
				_n(
					'%d attachment generated',
					'%d attachments generated',
					count,
					'fakerpress'
				),
				count
			);
	}
}

/**
 * Formats a duration in seconds for display: "0.42s", "12.4s", "1m 05s".
 *
 * @param seconds Duration in seconds.
 * @return The formatted duration.
 */
export function formatDuration( seconds: number ): string {
	if ( seconds < 10 ) {
		return `${ seconds.toFixed( 2 ) }s`;
	}
	if ( seconds < 60 ) {
		return `${ seconds.toFixed( 1 ) }s`;
	}
	const minutes = Math.floor( seconds / 60 );
	const rest = Math.round( seconds % 60 );
	return `${ minutes }m ${ String( rest ).padStart( 2, '0' ) }s`;
}
