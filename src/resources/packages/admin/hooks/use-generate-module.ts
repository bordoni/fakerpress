import { useState, useCallback } from 'react';
import apiFetch from '@wordpress/api-fetch';
import type { GenerateResponse, GenerateResult } from '../lib/types';

interface UseGenerateModuleReturn {
	generate: ( data: Record< string, unknown > ) => void;
	isGenerating: boolean;
	progress: { current: number; total: number } | null;
	results: GenerateResult | null;
	error: string | null;
	reset: () => void;
}

/**
 * Hook that handles the full generate lifecycle for a module.
 *
 * POSTs to /fakerpress/v1/{endpoint}/generate, handles batching
 * when the response indicates `is_capped`, and tracks progress.
 *
 * @param endpoint The REST endpoint path (e.g., 'terms', 'posts').
 * @return Generate controls and state.
 */
export function useGenerateModule( endpoint: string ): UseGenerateModuleReturn {
	const [ isGenerating, setIsGenerating ] = useState( false );
	const [ progress, setProgress ] = useState< { current: number; total: number } | null >( null );
	const [ results, setResults ] = useState< GenerateResult | null >( null );
	const [ error, setError ] = useState< string | null >( null );

	const reset = useCallback( () => {
		setIsGenerating( false );
		setProgress( null );
		setResults( null );
		setError( null );
	}, [] );

	const generate = useCallback(
		async ( data: Record< string, unknown > ) => {
			setIsGenerating( true );
			setError( null );
			setResults( null );
			setProgress( null );

			const allIds: number[] = [];
			const allLinks: Record< number, string > = {};
			let totalGenerated = 0;
			let totalTime = 0;
			let offset = 0;
			// Set from the first capped batch, so later batches don't re-roll the quantity range.
			let total: number | undefined;

			try {
				let hasMore = true;

				while ( hasMore ) {
					const body = await apiFetch< GenerateResponse | { data: GenerateResponse } >( {
						path: `/fakerpress/v1/${ endpoint }/generate`,
						method: 'POST',
						data: {
							...data,
							offset,
							...( total !== undefined ? { total } : {} ),
						},
					} );
					// The endpoints wrap the batch in `{ success, data, message }`.
					const response = 'data' in body && body.data ? body.data : ( body as GenerateResponse );

					totalGenerated += response.generated ?? 0;
					totalTime += response.time ?? 0;
					allIds.push( ...( response.ids ?? [] ) );
					Object.assign( allLinks, response.links ?? {} );

					if ( response.is_capped && response.total > totalGenerated ) {
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
				} );
			} catch ( err: unknown ) {
				const message =
					err instanceof Error ? err.message : 'An unknown error occurred.';
				setError( message );
			} finally {
				setIsGenerating( false );
				setProgress( null );
			}
		},
		[ endpoint ]
	);

	return { generate, isGenerating, progress, results, error, reset };
}
