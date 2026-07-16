import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { DateRangeField } from '../date-range-field';

describe( 'DateRangeField', () => {
	const defaultProps = {
		value: { preset: '', start: '', end: '' },
		onChange: jest.fn(),
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders preset select and date inputs', () => {
		render( <DateRangeField { ...defaultProps } /> );

		// The Select trigger should show the placeholder text.
		expect( screen.getByText( 'Select an Interval' ) ).toBeInTheDocument();

		// Both native date inputs render when no date is set.
		const dateInputs = document.querySelectorAll( 'input[type="date"]' );
		expect( dateInputs ).toHaveLength( 2 );
	} );

	it( 'renders dates when provided', () => {
		render(
			<DateRangeField
				value={ { preset: 'custom', start: '2024-01-15', end: '2024-02-15' } }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByDisplayValue( '2024-01-15' ) ).toBeInTheDocument();
		expect( screen.getByDisplayValue( '2024-02-15' ) ).toBeInTheDocument();
	} );

	it( 'renders the preset select trigger', () => {
		render( <DateRangeField { ...defaultProps } /> );

		// Verify the select trigger renders with the placeholder.
		const selectTrigger = screen.getByText( 'Select an Interval' );
		expect( selectTrigger ).toBeInTheDocument();

		// The trigger should be within a combobox element (Radix Select renders role=combobox).
		const combobox = screen.getByRole( 'combobox' );
		expect( combobox ).toBeInTheDocument();
	} );
} );
