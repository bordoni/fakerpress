import { renderHook, act, waitFor } from '@testing-library/react';
import apiFetch from '@wordpress/api-fetch';
import { useGenerateModule } from '../use-generate-module';

const mockApiFetch = apiFetch as jest.MockedFunction< typeof apiFetch >;

describe( 'useGenerateModule', () => {
	beforeEach( () => {
		mockApiFetch.mockReset();
	} );

	it( 'calls correct REST endpoint', async () => {
		mockApiFetch.mockResolvedValueOnce( {
			generated: 3,
			ids: [ 1, 2, 3 ],
			links: {},
			time: 0.5,
			is_capped: false,
			total: 3,
		} );

		const { result } = renderHook( () => useGenerateModule( 'terms' ) );

		await act( async () => {
			await result.current.generate( { quantity: 3 } );
		} );

		expect( mockApiFetch ).toHaveBeenCalledWith(
			expect.objectContaining( {
				path: expect.stringContaining( 'terms/generate' ),
			} )
		);
	} );

	it( 'sets results on success', async () => {
		mockApiFetch.mockResolvedValueOnce( {
			generated: 3,
			ids: [ 1, 2, 3 ],
			links: {},
			time: 0.5,
			is_capped: false,
			total: 3,
		} );

		const { result } = renderHook( () => useGenerateModule( 'terms' ) );

		await act( async () => {
			await result.current.generate( { quantity: 3 } );
		} );

		await waitFor( () => {
			expect( result.current.results ).not.toBeNull();
		} );

		expect( result.current.results ).toEqual(
			expect.objectContaining( {
				generated: 3,
				ids: [ 1, 2, 3 ],
				links: {},
				time: 0.5,
			} )
		);
	} );

	it( 'sets error on failure', async () => {
		mockApiFetch.mockRejectedValueOnce( new Error( 'Server error' ) );

		const { result } = renderHook( () => useGenerateModule( 'terms' ) );

		await act( async () => {
			await result.current.generate( { quantity: 3 } );
		} );

		await waitFor( () => {
			expect( result.current.error ).toBe( 'Server error' );
		} );
	} );

	it( 'handles batched generation', async () => {
		mockApiFetch
			.mockResolvedValueOnce( {
				generated: 5,
				ids: [ 1, 2, 3, 4, 5 ],
				links: {},
				time: 0.2,
				is_capped: true,
				total: 10,
			} )
			.mockResolvedValueOnce( {
				generated: 5,
				ids: [ 6, 7, 8, 9, 10 ],
				links: {},
				time: 0.2,
				is_capped: false,
				total: 10,
			} );

		const { result } = renderHook( () => useGenerateModule( 'posts' ) );

		await act( async () => {
			await result.current.generate( { quantity: 10 } );
		} );

		await waitFor( () => {
			expect( mockApiFetch ).toHaveBeenCalledTimes( 2 );
		} );

		expect( result.current.results ).toEqual(
			expect.objectContaining( {
				generated: 10,
			} )
		);
	} );
} );
