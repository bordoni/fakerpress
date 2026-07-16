import { useState, useCallback, useRef } from 'react';
import type { SearchResult, AjaxSearchResponse } from '../lib/types';
import { usePageConfig } from './use-page-config';

interface UseAsyncSearchReturn {
	search: ( query: string ) => void;
	results: SearchResult[];
	isSearching: boolean;
}

/**
 * Hook for debounced search against existing wp_ajax_fakerpress.* endpoints.
 *
 * Replaces the Select2 AJAX functionality with a React-friendly interface.
 *
 * @param action The AJAX action name (e.g., 'fakerpress.search_authors').
 * @param nonce  The nonce for this action.
 * @param delay  Debounce delay in milliseconds.
 * @return Search controls and state.
 */
export function useAsyncSearch(
	action: string,
	nonce: string,
	delay: number = 300
): UseAsyncSearchReturn {
	const { ajaxUrl } = usePageConfig();
	const [ results, setResults ] = useState< SearchResult[] >( [] );
	const [ isSearching, setIsSearching ] = useState( false );
	const timerRef = useRef< ReturnType< typeof setTimeout > | null >( null );
	const abortRef = useRef< AbortController | null >( null );

	const search = useCallback(
		( query: string ) => {
			// Clear pending debounce.
			if ( timerRef.current ) {
				clearTimeout( timerRef.current );
			}

			// Abort in-flight request.
			if ( abortRef.current ) {
				abortRef.current.abort();
			}

			if ( ! query.trim() ) {
				setResults( [] );
				setIsSearching( false );
				return;
			}

			setIsSearching( true );

			timerRef.current = setTimeout( async () => {
				const controller = new AbortController();
				abortRef.current = controller;

				try {
					const body = new FormData();
					body.append( 'action', action );
					body.append( 'nonce', nonce );
					body.append( 'search', query );

					const response = await fetch( ajaxUrl, {
						method: 'POST',
						body,
						signal: controller.signal,
					} );

					const data: AjaxSearchResponse = await response.json();

					if ( data.status && data.results ) {
						setResults( data.results );
					} else {
						setResults( [] );
					}
				} catch ( err: unknown ) {
					if ( err instanceof DOMException && err.name === 'AbortError' ) {
						return;
					}
					setResults( [] );
				} finally {
					setIsSearching( false );
				}
			}, delay );
		},
		[ action, nonce, ajaxUrl, delay ]
	);

	return { search, results, isSearching };
}
