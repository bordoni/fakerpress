import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import { RangeInput } from '../range-input';

describe( 'RangeInput', () => {
	const defaultProps = {
		minValue: 3,
		maxValue: 12,
		onMinChange: jest.fn(),
		onMaxChange: jest.fn(),
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders both min and max inputs', () => {
		render( <RangeInput { ...defaultProps } /> );

		const inputs = screen.getAllByRole( 'spinbutton' );
		expect( inputs ).toHaveLength( 2 );
		expect( inputs[ 0 ] ).toHaveValue( 3 );
		expect( inputs[ 1 ] ).toHaveValue( 12 );
	} );

	it( 'calls onMinChange when min input changes', () => {
		const onMinChange = jest.fn();
		render( <RangeInput { ...defaultProps } onMinChange={ onMinChange } /> );

		const inputs = screen.getAllByRole( 'spinbutton' );
		fireEvent.change( inputs[ 0 ], { target: { value: '5' } } );

		expect( onMinChange ).toHaveBeenCalledWith( 5 );
	} );

	it( 'calls onMaxChange when max input changes', () => {
		const onMaxChange = jest.fn();
		render( <RangeInput { ...defaultProps } onMaxChange={ onMaxChange } /> );

		const inputs = screen.getAllByRole( 'spinbutton' );
		fireEvent.change( inputs[ 1 ], { target: { value: '20' } } );

		expect( onMaxChange ).toHaveBeenCalledWith( 20 );
	} );
} );
