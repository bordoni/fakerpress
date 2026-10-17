import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import { MetaFieldRules, EMPTY_META_RULE } from '../meta-field-rules';

describe( 'MetaFieldRules', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'always shows one blank rule, like the stable generator', () => {
		render( <MetaFieldRules value={ [] } onChange={ jest.fn() } /> );

		expect(
			screen.getByRole( 'heading', { name: 'Meta Field Rules' } )
		).toBeInTheDocument();
		expect(
			screen.getByRole( 'button', { name: 'Rule 1' } )
		).toBeDisabled();
		expect( screen.getByText( 'Select a Field type' ) ).toBeInTheDocument();
		expect(
			screen.getByPlaceholderText(
				'Newborn Meta needs a Name, E.g.: _new_image'
			)
		).toBeInTheDocument();
		// No type yet, so no configuration row.
		expect( screen.queryByText( 'Configuration' ) ).not.toBeInTheDocument();
		// There is no separate "Add Meta Rule" button any more.
		expect(
			screen.queryByRole( 'button', { name: /add meta rule/i } )
		).not.toBeInTheDocument();
	} );

	it( 'disables remove when the only rule is blank', () => {
		render( <MetaFieldRules value={ [] } onChange={ jest.fn() } /> );

		expect(
			screen.getByRole( 'button', { name: 'Remove rule' } )
		).toBeDisabled();
		expect(
			screen.getByRole( 'button', { name: 'Add rule' } )
		).toBeEnabled();
	} );

	it( 'appends a blank rule when + is clicked', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		const rule = {
			type: 'letter',
			name: 'initial',
			weight: 50,
			config: {},
		};
		render( <MetaFieldRules value={ [ rule ] } onChange={ onChange } /> );

		await user.click( screen.getByRole( 'button', { name: 'Add rule' } ) );

		expect( onChange ).toHaveBeenCalledWith( [ rule, EMPTY_META_RULE ] );
	} );

	it( 'adds a second blank rule when + is clicked on the placeholder rule', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render( <MetaFieldRules value={ [] } onChange={ onChange } /> );

		await user.click( screen.getByRole( 'button', { name: 'Add rule' } ) );

		expect( onChange ).toHaveBeenCalledWith( [
			EMPTY_META_RULE,
			EMPTY_META_RULE,
		] );
	} );

	it( 'renders existing rules with numbered badges', () => {
		render(
			<MetaFieldRules
				value={ [
					{ type: 'letter', name: 'first_meta', config: {} },
					{ type: 'email', name: 'second_meta', config: {} },
				] }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByDisplayValue( 'first_meta' ) ).toBeInTheDocument();
		expect( screen.getByDisplayValue( 'second_meta' ) ).toBeInTheDocument();
		expect(
			screen.getByRole( 'button', { name: 'Rule 1' } )
		).toBeInTheDocument();
		expect(
			screen.getByRole( 'button', { name: 'Rule 2' } )
		).toBeInTheDocument();
	} );

	it( 'removes the clicked rule when there are several', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<MetaFieldRules
				value={ [
					{ type: 'text', name: 'rule1', config: {} },
					{ type: 'numbers', name: 'rule2', config: {} },
				] }
				onChange={ onChange }
			/>
		);

		await user.click(
			screen.getAllByRole( 'button', { name: 'Remove rule' } )[ 0 ]
		);

		expect( onChange ).toHaveBeenCalledWith( [
			{ type: 'numbers', name: 'rule2', config: {} },
		] );
	} );

	it( 'clears the only rule instead of removing it', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<MetaFieldRules
				value={ [ { type: 'letter', name: 'only', config: {} } ] }
				onChange={ onChange }
			/>
		);

		await user.click(
			screen.getByRole( 'button', { name: 'Remove rule' } )
		);

		expect( onChange ).toHaveBeenCalledWith( [] );
	} );

	it( 'shows the attachment configuration in the stable order, with weight before the sizes', () => {
		render(
			<MetaFieldRules
				value={ [
					{
						type: 'attachment',
						name: '_thumbnail_id',
						weight: 75,
						config: {
							store: 'id',
							providers: 'placeholder,lorempicsum',
						},
					},
				] }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByText( 'Configuration' ) ).toBeInTheDocument();
		const labels = [
			'Stored Data',
			'Which image services will the generator use?',
			'Weight',
			'Range of possible width sizes for the image',
			'Range of possible height sizes for the image',
		].map( ( text ) => screen.getByText( text ) );
		// Document order: each label appears after the previous one.
		const text = document.body.textContent ?? '';
		const positions = labels.map( ( label ) =>
			text.indexOf( label.textContent ?? '' )
		);
		expect( positions ).toEqual(
			[ ...positions ].sort( ( a, b ) => a - b )
		);
		expect( screen.getByLabelText( 'Weight' ) ).toHaveValue( 75 );
		expect( screen.getByText( 'Placehold.co' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Lorem Picsum' ) ).toBeInTheDocument();
		expect(
			screen.getByPlaceholderText( 'e.g.: 350' )
		).toBeInTheDocument();
	} );

	it( 'shows the person template as tags joined with |', () => {
		render(
			<MetaFieldRules
				value={ [
					{
						type: 'person',
						name: 'full_name',
						config: {
							template: '{% first_name %}|{% last_name %}',
							gender: 'female',
						},
					},
				] }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByText( 'First Name' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Last Name' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Name Template' ) ).toBeInTheDocument();
	} );

	it( 'offers the date type with an interval and a format', () => {
		render(
			<MetaFieldRules
				value={ [
					{
						type: 'date',
						name: 'event_date',
						config: {
							interval: { name: '', min: '', max: '' },
							format: 'Y-m-d H:i:s',
						},
					},
				] }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByText( 'Select an Interval' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Date Format' ) ).toHaveValue(
			'Y-m-d H:i:s'
		);
		expect( screen.getByLabelText( 'Weight' ) ).toBeInTheDocument();
	} );

	it( 'shows only the weight for types without configuration', () => {
		render(
			<MetaFieldRules
				value={ [
					{ type: 'email', name: 'contact', weight: 90, config: {} },
				] }
				onChange={ jest.fn() }
			/>
		);

		expect( screen.getByText( 'Configuration' ) ).toBeInTheDocument();
		expect( screen.getByLabelText( 'Weight' ) ).toHaveValue( 90 );
	} );

	it( 'updates the weight', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<MetaFieldRules
				value={ [
					{ type: 'email', name: 'contact', weight: 9, config: {} },
				] }
				onChange={ onChange }
			/>
		);

		await user.type( screen.getByLabelText( 'Weight' ), '5' );

		expect( onChange ).toHaveBeenLastCalledWith( [
			{ type: 'email', name: 'contact', weight: 95, config: {} },
		] );
	} );
} );
