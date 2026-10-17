import { renderHook, act, waitFor } from '@testing-library/react';
import { useAsyncSearch } from '../use-async-search';

const mockFetch = jest.fn();
global.fetch = mockFetch;

describe( 'useAsyncSearch', () => {
	const ajaxUrl =
		window.fakerpressPageConfig?.ajaxUrl ||
		'http://localhost/wp-admin/admin-ajax.php';
	const testAction = 'fakerpress_search';
	const testNonce = 'test-nonce-123';

	beforeEach( () => {
		jest.useFakeTimers();
		mockFetch.mockReset();
	} );

	afterEach( () => {
		jest.useRealTimers();
	} );

	it( 'clears results on empty query', async () => {
		const { result } = renderHook( () =>
			useAsyncSearch( testAction, testNonce, 0 )
		);

		await act( async () => {
			result.current.search( '' );
		} );

		expect( result.current.results ).toEqual( [] );
	} );

	it( 'calls AJAX endpoint with correct params', async () => {
		mockFetch.mockResolvedValueOnce( {
			ok: true,
			json: async () => ( {
				status: true,
				results: [ { id: 1, value: 1, name: 'Test' } ],
				more: false,
			} ),
		} );

		const { result } = renderHook( () =>
			useAsyncSearch( testAction, testNonce, 0 )
		);

		await act( async () => {
			result.current.search( 'test' );
		} );

		// Advance timers to flush the debounce.
		await act( async () => {
			jest.advanceTimersByTime( 10 );
		} );

		await waitFor( () => {
			expect( mockFetch ).toHaveBeenCalled();
		} );

		expect( mockFetch ).toHaveBeenCalledWith(
			ajaxUrl,
			expect.objectContaining( {
				method: 'POST',
			} )
		);
	} );

	it( 'returns search results', async () => {
		const expectedResults = [
			{ id: 1, value: 1, name: 'Test Item' },
			{ id: 2, value: 2, name: 'Another Item' },
		];

		mockFetch.mockResolvedValueOnce( {
			ok: true,
			json: async () => ( {
				status: true,
				results: expectedResults,
				more: false,
			} ),
		} );

		const { result } = renderHook( () =>
			useAsyncSearch( testAction, testNonce, 0 )
		);

		await act( async () => {
			result.current.search( 'test' );
		} );

		// Advance timers to flush the debounce.
		await act( async () => {
			jest.advanceTimersByTime( 10 );
		} );

		await waitFor( () => {
			expect( result.current.results.length ).toBeGreaterThan( 0 );
		} );

		expect( result.current.results ).toEqual( expectedResults );
	} );
} );
