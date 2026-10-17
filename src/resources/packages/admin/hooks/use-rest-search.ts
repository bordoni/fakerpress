import { useState, useCallback, useRef } from 'react';
import apiFetch from '@wordpress/api-fetch';
import type { SearchResult } from '../lib/types';

interface UseRestSearchReturn {
	search: ( query: string, params?: Record< string, unknown > ) => void;
	results: SearchResult[];
	isSearching: boolean;
}

interface RestSearchResponse {
	success: boolean;
	data?: { results?: SearchResult[] };
	message?: string;
}

/**
 * Build a query string from a params object.
 *
 * Arrays are appended as `key[]=value` so the WordPress REST API parses them
 * back into an array parameter.
 *
 * @param params Key/value pairs to serialize.
 * @return Query string prefixed with `?`, or an empty string when no params.
 */
function buildQuery( params: Record< string, unknown > ): string {
	const sp = new URLSearchParams();

	Object.entries( params ).forEach( ( [ key, val ] ) => {
		if ( Array.isArray( val ) ) {
			val.forEach( ( v ) => sp.append( `${ key }[]`, String( v ) ) );
		} else if ( val !== undefined && val !== null ) {
			sp.append( key, String( val ) );
		}
	} );

	const qs = sp.toString();
	return qs ? `?${ qs }` : '';
}

/**
 * Hook for debounced search against a FakerPress REST endpoint.
 *
 * Uses `@wordpress/api-fetch`, so the REST nonce is applied automatically.
 *
 * @param path  The REST path to search against (e.g., '/fakerpress/v1/terms/search').
 * @param delay Debounce delay in milliseconds.
 * @return Search controls and state.
 */
export function useRestSearch(
	path: string,
	delay: number = 300
): UseRestSearchReturn {
	const [ results, setResults ] = useState< SearchResult[] >( [] );
	const [ isSearching, setIsSearching ] = useState( false );
	const timerRef = useRef< ReturnType< typeof setTimeout > | null >( null );
	const seqRef = useRef( 0 );

	const search = useCallback(
		( query: string, params: Record< string, unknown > = {} ) => {
			// Clear pending debounce.
			if ( timerRef.current ) {
				clearTimeout( timerRef.current );
			}

			if ( ! query.trim() ) {
				// Invalidate any in-flight request and reset.
				seqRef.current += 1;
				setResults( [] );
				setIsSearching( false );
				return;
			}

			setIsSearching( true );
			const seq = ++seqRef.current;

			timerRef.current = setTimeout( async () => {
				try {
					const response = await apiFetch< RestSearchResponse >( {
						path: path + buildQuery( { search: query, ...params } ),
						method: 'GET',
					} );

					// Ignore stale responses.
					if ( seq !== seqRef.current ) {
						return;
					}

					setResults( response?.data?.results ?? [] );
				} catch {
					if ( seq === seqRef.current ) {
						setResults( [] );
					}
				} finally {
					if ( seq === seqRef.current ) {
						setIsSearching( false );
					}
				}
			}, delay );
		},
		[ path, delay ]
	);

	return { search, results, isSearching };
}
