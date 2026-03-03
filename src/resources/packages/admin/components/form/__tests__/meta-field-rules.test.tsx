import { render, screen, fireEvent } from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import { MetaFieldRules } from '../meta-field-rules';

describe( 'MetaFieldRules', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders add button when empty', () => {
		const onChange = jest.fn();
		render( <MetaFieldRules value={ [] } onChange={ onChange } /> );

		expect(
			screen.getByRole( 'button', { name: /add meta rule/i } )
		).toBeInTheDocument();
	} );

	it( 'calls onChange with new rule when add button clicked', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render( <MetaFieldRules value={ [] } onChange={ onChange } /> );

		await user.click(
			screen.getByRole( 'button', { name: /add meta rule/i } )
		);

		expect( onChange ).toHaveBeenCalledWith( [
			{ type: 'text', name: '', config: {} },
		] );
	} );

	it( 'renders existing rules', () => {
		const onChange = jest.fn();
		render(
			<MetaFieldRules
				value={ [ { type: 'number', name: 'test_meta', config: {} } ] }
				onChange={ onChange }
			/>
		);

		expect( screen.getByText( 'test_meta' ) ).toBeInTheDocument();
	} );

	it( 'calls onChange without removed rule when remove clicked', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<MetaFieldRules
				value={ [
					{ type: 'text', name: 'rule1', config: {} },
					{ type: 'number', name: 'rule2', config: {} },
				] }
				onChange={ onChange }
			/>
		);

		// Expand the first accordion item by clicking its trigger.
		const trigger = screen.getByText( 'rule1' );
		await user.click( trigger );

		// Find and click the Remove button inside the expanded content.
		const removeButton = screen.getByRole( 'button', { name: /remove/i } );
		await user.click( removeButton );

		expect( onChange ).toHaveBeenCalledWith( [
			{ type: 'number', name: 'rule2', config: {} },
		] );
	} );
} );
