import { useState, useCallback } from 'react';
import apiFetch from '@wordpress/api-fetch';
import type {
	GenerateBatch,
	GenerateError,
	GenerateResponse,
	GenerateResult,
	GeneratedItem,
} from '../lib/types';

interface UseGenerateModuleReturn {
	generate: ( data: Record< string, unknown > ) => void;
	isGenerating: boolean;
	progress: { current: number; total: number } | null;
	results: GenerateResult | null;
	error: string | null;
	errorDetails: GenerateError | null;
	reset: () => void;
}

/**
 * Reads the message, code and status out of whatever api-fetch rejected with.
 *
 * REST errors reject with the parsed body (`{ code, message, data: { status } }`), not an Error.
 *
 * @param err The rejection value.
 * @return The message, code and status.
 */
function describeError( err: unknown ): {
	message: string;
	code: string | null;
	status: number | null;
} {
	if ( err instanceof Error ) {
		return { message: err.message, code: null, status: null };
	}

	if ( err && typeof err === 'object' ) {
		const body = err as {
			message?: unknown;
			code?: unknown;
			data?: { status?: unknown };
		};
		return {
			message:
				typeof body.message === 'string' && body.message
					? body.message
					: 'An unknown error occurred.',
			code: typeof body.code === 'string' ? body.code : null,
			status:
				typeof body.data?.status === 'number' ? body.data.status : null,
		};
	}

	return { message: 'An unknown error occurred.', code: null, status: null };
}

/**
 * Hook that handles the full generate lifecycle for a module.
 *
 * POSTs to /fakerpress/v1/{endpoint}/generate, handles batching
 * when the response indicates `is_capped`, and tracks progress.
 * Keeps every batch's request and raw response for the details view.
 *
 * @param endpoint The REST endpoint path (e.g., 'terms', 'posts').
 * @return Generate controls and state.
 */
export function useGenerateModule( endpoint: string ): UseGenerateModuleReturn {
	const [ isGenerating, setIsGenerating ] = useState( false );
	const [ progress, setProgress ] = useState< {
		current: number;
		total: number;
	} | null >( null );
	const [ results, setResults ] = useState< GenerateResult | null >( null );
	const [ errorDetails, setErrorDetails ] = useState< GenerateError | null >(
		null
	);

	const reset = useCallback( () => {
		setIsGenerating( false );
		setProgress( null );
		setResults( null );
		setErrorDetails( null );
	}, [] );

	const generate = useCallback(
		async ( data: Record< string, unknown > ) => {
			setIsGenerating( true );
			setErrorDetails( null );
			setResults( null );
			setProgress( null );

			const allIds: number[] = [];
			const allLinks: ( string | number )[] = [];
			const allItems: GeneratedItem[] = [];
			const batches: GenerateBatch[] = [];
			let listUrl: string | null = null;
			let totalGenerated = 0;
			let totalTime = 0;
			let offset = 0;
			// Set from the first capped batch, so later batches don't re-roll the quantity range.
			let total: number | undefined;
			let request: Record< string, unknown > | null = null;

			try {
				let hasMore = true;

				while ( hasMore ) {
					request = {
						...data,
						offset,
						...( total !== undefined ? { total } : {} ),
					};
					const body = await apiFetch<
						GenerateResponse | { data: GenerateResponse }
					>( {
						path: `/fakerpress/v1/${ endpoint }/generate`,
						method: 'POST',
						data: request,
					} );
					// The endpoints wrap the batch in `{ success, data, message }`.
					const response =
						'data' in body && body.data
							? body.data
							: ( body as GenerateResponse );

					totalGenerated += response.generated ?? 0;
					totalTime += response.time ?? 0;
					allIds.push( ...( response.ids ?? [] ) );
					// Links arrive as a list; older responses keyed them by index, which made batches overwrite each other.
					allLinks.push( ...Object.values( response.links ?? {} ) );
					allItems.push( ...( response.items ?? [] ) );
					listUrl = listUrl ?? response.list_url ?? null;
					batches.push( {
						generated: response.generated ?? 0,
						time: response.time ?? 0,
						request,
						response: body,
					} );

					if (
						response.is_capped &&
						response.total > totalGenerated
					) {
						total = response.total;
						offset = totalGenerated;
						setProgress( {
							current: totalGenerated,
							total: response.total,
						} );
					} else {
						hasMore = false;
					}
				}

				setResults( {
					generated: totalGenerated,
					ids: allIds,
					links: allLinks,
					time: totalTime,
					items: allItems,
					listUrl,
					batches,
					finishedAt: new Date().toISOString(),
				} );
			} catch ( err: unknown ) {
				setErrorDetails( {
					...describeError( err ),
					request,
					response: err instanceof Error ? null : err,
					batches,
					finishedAt: new Date().toISOString(),
				} );
			} finally {
				setIsGenerating( false );
				setProgress( null );
			}
		},
		[ endpoint ]
	);

	return {
		generate,
		isGenerating,
		progress,
		results,
		error: errorDetails?.message ?? null,
		errorDetails,
		reset,
	};
}
