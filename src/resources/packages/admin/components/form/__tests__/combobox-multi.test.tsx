import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import { ComboboxMulti } from '../combobox-multi';

describe( 'ComboboxMulti', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders placeholder when empty', () => {
		render(
			<ComboboxMulti
				value={ [] }
				onChange={ jest.fn() }
				options={ [ { value: 'a', label: 'Alpha' } ] }
				placeholder="Select..."
			/>
		);

		expect( screen.getByText( 'Select...' ) ).toBeInTheDocument();
	} );

	it( 'renders selected values as badges', () => {
		render(
			<ComboboxMulti
				value={ [ 'a' ] }
				onChange={ jest.fn() }
				options={ [ { value: 'a', label: 'Alpha' } ] }
			/>
		);

		expect( screen.getByText( 'Alpha' ) ).toBeInTheDocument();
	} );

	it( 'calls onChange when removing a badge', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<ComboboxMulti
				value={ [ 'a', 'b' ] }
				onChange={ onChange }
				options={ [
					{ value: 'a', label: 'Alpha' },
					{ value: 'b', label: 'Beta' },
				] }
			/>
		);

		// The X button is inside the Alpha badge span.
		// Each badge has a child <button> for removal.
		const alphaBadge = screen.getByText( 'Alpha' );
		const removeButton = alphaBadge.closest( '[data-slot="badge"]' )!.querySelector( 'button' )!;
		await user.click( removeButton );

		expect( onChange ).toHaveBeenCalledWith( [ 'b' ] );
	} );

	it( 'shows searching state', async () => {
		const user = userEvent.setup();
		render(
			<ComboboxMulti
				value={ [] }
				onChange={ jest.fn() }
				onSearch={ jest.fn() }
				searchResults={ [] }
				isSearching={ true }
			/>
		);

		// Open the popover by clicking the combobox button.
		const comboboxButton = screen.getByRole( 'combobox' );
		await user.click( comboboxButton );

		expect( screen.getByText( 'Searching...' ) ).toBeInTheDocument();
	} );
} );
