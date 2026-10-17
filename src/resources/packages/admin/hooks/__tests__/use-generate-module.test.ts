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

	it( 'reads the batch from the { success, data, message } envelope the endpoints return', async () => {
		mockApiFetch.mockResolvedValueOnce( {
			success: true,
			data: { generated: 2, ids: [ 7, 8 ], links: { 7: 'a', 8: 'b' }, time: 0.2 },
			message: 'Successfully generated 2 terms.',
		} );

		const { result } = renderHook( () => useGenerateModule( 'terms' ) );

		await act( async () => {
			await result.current.generate( { qty: { min: 2, max: 2 } } );
		} );

		expect( result.current.error ).toBeNull();
		expect( result.current.results ).toEqual(
			expect.objectContaining( { generated: 2, ids: [ 7, 8 ], time: 0.2 } )
		);
	} );

	it( 'sends the first batch total back on later batches so the range is not re-rolled', async () => {
		mockApiFetch
			.mockResolvedValueOnce( {
				success: true,
				data: { generated: 25, ids: [ 1 ], links: {}, time: 1, is_capped: true, offset: 25, total: 40 },
			} )
			.mockResolvedValueOnce( {
				success: true,
				data: { generated: 15, ids: [ 2 ], links: {}, time: 1, is_capped: false, offset: 40, total: 40 },
			} );

		const { result } = renderHook( () => useGenerateModule( 'posts' ) );

		await act( async () => {
			await result.current.generate( { qty: { min: 30, max: 50 } } );
		} );

		expect( mockApiFetch ).toHaveBeenCalledTimes( 2 );
		expect( mockApiFetch.mock.calls[ 0 ][ 0 ].data ).not.toHaveProperty( 'total' );
		expect( mockApiFetch.mock.calls[ 1 ][ 0 ].data ).toEqual(
			expect.objectContaining( { offset: 25, total: 40 } )
		);
		expect( result.current.results ).toEqual( expect.objectContaining( { generated: 40 } ) );
	} );
} );
