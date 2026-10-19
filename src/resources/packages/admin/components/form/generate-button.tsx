import { useEffect, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { AlertTriangle, Check, ChevronRight, Loader2, X } from 'lucide-react';
import { Button } from '../ui/button';
import { cn } from '../../lib/utils';
import {
	formatDuration,
	generatedHeadline,
	type GenerateModule,
} from '../../lib/generate-labels';
import { GenerateDetailsDialog } from './generate-details-dialog';
import type { GenerateError, GenerateResult } from '../../lib/types';

interface GenerateButtonProps {
	onClick: () => void;
	isGenerating: boolean;
	progress: { current: number; total: number } | null;
	results: GenerateResult | null;
	error: string | null;
	/** Module the button generates, for the right noun in the result. */
	module?: GenerateModule;
	/** Code, status and raw data of a failed run, shown in the details dialog. */
	errorDetails?: GenerateError | null;
	/** Clears the result or error. */
	onDismiss?: () => void;
}

/**
 * Seconds since generating started, ticking every 100ms while it runs.
 *
 * @param running Whether a generate run is in progress.
 * @return Elapsed seconds, or 0 when idle.
 */
function useElapsed( running: boolean ): number {
	const [ elapsed, setElapsed ] = useState( 0 );

	useEffect( () => {
		if ( ! running ) {
			setElapsed( 0 );
			return;
		}
		const start = Date.now();
		const timer = window.setInterval(
			() => setElapsed( ( Date.now() - start ) / 1000 ),
			100
		);
		return () => window.clearInterval( timer );
	}, [ running ] );

	return elapsed;
}

/**
 * Generate button with its status docked to it: progress while it runs, then what was
 * generated (or what went wrong), and a way into the details dialog.
 *
 * On wide screens the status sits to the right of the button as one control; on narrow
 * screens it hangs below the full-width button.
 *
 * @param props              Component props.
 * @param props.onClick
 * @param props.isGenerating
 * @param props.progress
 * @param props.results
 * @param props.error
 * @param props.module
 * @param props.errorDetails
 * @param props.onDismiss
 */
export function GenerateButton( {
	onClick,
	isGenerating,
	progress,
	results,
	error,
	module = 'posts',
	errorDetails = null,
	onDismiss,
}: GenerateButtonProps ) {
	const [ detailsOpen, setDetailsOpen ] = useState( false );
	const elapsed = useElapsed( isGenerating );

	const status: 'generating' | 'success' | 'error' | null = isGenerating
		? 'generating'
		: ( error && 'error' ) || ( results && 'success' ) || null;

	const progressPercent = progress
		? Math.round( ( progress.current / progress.total ) * 100 )
		: 0;
	const thumbnails = ( results?.items ?? [] )
		.filter( ( item ) => item.thumbnail )
		.slice( 0, 3 );
	const partial = errorDetails?.batches.reduce(
		( sum, batch ) => sum + batch.generated,
		0
	);

	return (
		<div className="fp:pt-4">
			<div className="fp:flex fp:flex-col fp:sm:flex-row fp:sm:items-stretch">
				<Button
					type="button"
					size="lg"
					onClick={ onClick }
					disabled={ isGenerating }
					className={ cn(
						'fp:relative fp:z-10 fp:w-full fp:sm:w-auto fp:sm:min-w-32',
						status &&
							'fp:rounded-b-none fp:sm:rounded-r-none fp:sm:rounded-bl-md'
					) }
				>
					{ isGenerating && <Loader2 className="fp:animate-spin" /> }
					{ isGenerating
						? __( 'Generating…', 'fakerpress' )
						: __( 'Generate', 'fakerpress' ) }
				</Button>

				{ status && (
					<div
						key={
							results?.finishedAt ??
							errorDetails?.finishedAt ??
							'generating'
						}
						role="status"
						aria-live="polite"
						data-status={ status }
						className={ cn(
							'fp:relative fp:flex fp:min-h-10 fp:min-w-0 fp:items-center fp:gap-3 fp:overflow-hidden fp:border fp:px-3 fp:py-1.5 fp:text-sm',
							'fp:rounded-b-md fp:border-t-0 fp:sm:rounded-bl-none fp:sm:rounded-r-md fp:sm:border-t fp:sm:border-l-0',
							'fp:animate-result-in fp:motion-reduce:animate-none',
							status === 'generating' &&
								'fp:border-border/40 fp:bg-muted fp:text-muted-foreground',
							status === 'success' &&
								'fp:border-emerald-700/25 fp:bg-emerald-50 fp:text-emerald-950',
							status === 'error' &&
								'fp:border-red-700/25 fp:bg-red-50 fp:text-red-950'
						) }
					>
						{ status === 'generating' && (
							<>
								<span className="fp:tabular-nums">
									{ progress
										? sprintf(
												/* translators: 1: Items generated so far, 2: Items requested. */
												__(
													'%1$d of %2$d generated',
													'fakerpress'
												),
												progress.current,
												progress.total
										  )
										: __( 'Working on it', 'fakerpress' ) }
								</span>
								<span className="fp:ml-auto fp:text-xs fp:tabular-nums">
									{ formatDuration( elapsed ) }
								</span>
								<span
									aria-hidden="true"
									className="fp:absolute fp:inset-x-0 fp:bottom-0 fp:h-0.5 fp:bg-primary/15"
								>
									<span
										className={ cn(
											'fp:block fp:h-full fp:bg-primary fp:transition-[width] fp:duration-300',
											! progress &&
												'fp:w-1/3 fp:animate-pulse'
										) }
										style={
											progress
												? {
														width: `${ progressPercent }%`,
												  }
												: undefined
										}
									/>
								</span>
							</>
						) }

						{ status === 'success' && results && (
							<>
								{ /* Thumbnails on wide screens only; the narrow bar needs the room for the headline. */ }
								<span
									className={ cn(
										'fp:flex fp:size-5 fp:shrink-0 fp:items-center fp:justify-center fp:rounded-full fp:bg-emerald-600 fp:text-white',
										thumbnails.length > 0 && 'fp:sm:hidden'
									) }
								>
									<Check
										className="fp:size-3.5"
										strokeWidth={ 3 }
									/>
								</span>
								{ thumbnails.length > 0 && (
									<span className="fp:hidden fp:shrink-0 fp:-space-x-2 fp:sm:flex">
										{ thumbnails.map( ( item ) => (
											<img
												key={ item.id }
												src={ item.thumbnail ?? '' }
												alt=""
												className="fp:size-6 fp:rounded-full fp:object-cover fp:ring-2 fp:ring-emerald-50"
											/>
										) ) }
									</span>
								) }
								<span className="fp:min-w-0 fp:truncate">
									<strong className="fp:font-semibold">
										{ generatedHeadline(
											module,
											results.generated
										) }
									</strong>
									<span className="fp:hidden fp:text-emerald-900/70 fp:sm:inline">
										{ ' · ' }
										<span className="fp:tabular-nums">
											{ formatDuration( results.time ) }
										</span>
									</span>
								</span>
								<button
									type="button"
									onClick={ () => setDetailsOpen( true ) }
									className="fp:ml-auto fp:inline-flex fp:shrink-0 fp:cursor-pointer fp:items-center fp:gap-0.5 fp:rounded-sm fp:bg-transparent fp:px-1 fp:py-0.5 fp:font-medium fp:text-emerald-800 fp:underline-offset-4 fp:hover:underline fp:focus-visible:ring-2 fp:focus-visible:ring-emerald-700/40 fp:focus-visible:outline-none"
								>
									{ __( 'View details', 'fakerpress' ) }
									<ChevronRight className="fp:size-4" />
								</button>
							</>
						) }

						{ status === 'error' && (
							<>
								<AlertTriangle className="fp:size-4 fp:shrink-0 fp:text-red-700" />
								<span className="fp:min-w-0 fp:truncate">
									<strong className="fp:font-semibold">
										{ partial
											? sprintf(
													/* translators: %d: Number of items generated before the error. */
													__(
														'Stopped after %d',
														'fakerpress'
													),
													partial
											  )
											: __(
													"Couldn't generate",
													'fakerpress'
											  ) }
									</strong>
									<span className="fp:text-red-900/75">
										{ ' · ' }
										{ error }
									</span>
								</span>
								<button
									type="button"
									onClick={ () => setDetailsOpen( true ) }
									className="fp:ml-auto fp:inline-flex fp:shrink-0 fp:cursor-pointer fp:items-center fp:gap-0.5 fp:rounded-sm fp:bg-transparent fp:px-1 fp:py-0.5 fp:font-medium fp:text-red-800 fp:underline-offset-4 fp:hover:underline fp:focus-visible:ring-2 fp:focus-visible:ring-red-700/40 fp:focus-visible:outline-none"
								>
									{ __( 'Details', 'fakerpress' ) }
									<ChevronRight className="fp:size-4" />
								</button>
							</>
						) }

						{ status !== 'generating' && onDismiss && (
							<button
								type="button"
								onClick={ onDismiss }
								aria-label={ __( 'Dismiss', 'fakerpress' ) }
								className="fp:-mr-1 fp:inline-flex fp:size-6 fp:shrink-0 fp:cursor-pointer fp:items-center fp:justify-center fp:rounded-sm fp:bg-transparent fp:opacity-60 fp:hover:opacity-100 fp:focus-visible:ring-2 fp:focus-visible:ring-current/30 fp:focus-visible:outline-none"
							>
								<X className="fp:size-4" />
							</button>
						) }
					</div>
				) }
			</div>

			{ ( results || errorDetails ) && (
				<GenerateDetailsDialog
					open={ detailsOpen }
					onOpenChange={ setDetailsOpen }
					module={ module }
					results={ results }
					errorDetails={ errorDetails }
				/>
			) }
		</div>
	);
}
