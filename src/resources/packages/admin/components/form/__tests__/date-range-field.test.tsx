import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { DateRangeField } from '../date-range-field';

describe( 'DateRangeField', () => {
	const defaultProps = {
		startDate: '',
		endDate: '',
		onStartChange: jest.fn(),
		onEndChange: jest.fn(),
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders preset select and date buttons', () => {
		render( <DateRangeField { ...defaultProps } /> );

		// The Select trigger should show the placeholder text.
		expect( screen.getByText( 'Select an Interval' ) ).toBeInTheDocument();

		// Both date picker buttons show their placeholder text when no date is set.
		expect( screen.getByText( 'Start date' ) ).toBeInTheDocument();
		expect( screen.getByText( 'End date' ) ).toBeInTheDocument();
	} );

	it( 'renders dates when provided', () => {
		render(
			<DateRangeField
				{ ...defaultProps }
				startDate="2024-01-15"
				endDate="2024-02-15"
			/>
		);

		expect( screen.getByText( '2024-01-15' ) ).toBeInTheDocument();
		expect( screen.getByText( '2024-02-15' ) ).toBeInTheDocument();
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
