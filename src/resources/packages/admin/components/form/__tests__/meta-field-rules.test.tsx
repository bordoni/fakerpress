import { render, screen } from '@testing-library/react';
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
			{
				type: 'text',
				name: '',
				weight: 100,
				config: { text_type: 'paragraphs', qty: [ 1, 3 ], separator: '\\n' },
			},
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

		// The rule name renders as the value of its Name input.
		expect( screen.getByDisplayValue( 'test_meta' ) ).toBeInTheDocument();
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

		// Each rule renders an always-visible remove control titled "Remove rule".
		const removeButtons = screen.getAllByTitle( 'Remove rule' );
		await user.click( removeButtons[ 0 ] );

		expect( onChange ).toHaveBeenCalledWith( [
			{ type: 'number', name: 'rule2', config: {} },
		] );
	} );
} );
