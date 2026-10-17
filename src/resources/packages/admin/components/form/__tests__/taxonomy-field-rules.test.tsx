import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import userEvent from '@testing-library/user-event';
import {
	TaxonomyFieldRules,
	EMPTY_TAXONOMY_RULE,
} from '../taxonomy-field-rules';

const taxonomyOptions = [
	{ value: 'category', label: 'Categories' },
	{ value: 'post_tag', label: 'Tags' },
];

const filledRule = {
	taxonomies: [ 'category', 'post_tag' ],
	terms: [],
	rate: 85,
	qty: { min: 1, max: 4 },
};

describe( 'TaxonomyFieldRules', () => {
	it( 'renders the stable generator labels and copy', () => {
		render(
			<TaxonomyFieldRules
				value={ [ filledRule ] }
				onChange={ jest.fn() }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		expect(
			screen.getByRole( 'heading', { name: 'Taxonomy Field Rules' } )
		).toBeInTheDocument();
		expect(
			screen.getByText(
				'Use the fields below to configure the rules for the Taxonomy and Terms selected'
			)
		).toBeInTheDocument();
		expect( screen.getByText( 'Taxonomies' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Terms' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Rate' ) ).toBeInTheDocument();
		expect(
			screen.getByText(
				'Percentage rate of posts that will have terms generated for the amount below'
			)
		).toBeInTheDocument();
		expect(
			screen.getByText( /How many terms will be selected\./ )
		).toBeInTheDocument();
		expect( screen.getByText( 'Categories' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Tags' ) ).toBeInTheDocument();
		expect(
			screen.getByText( 'Which terms can be used' )
		).toBeInTheDocument();
		expect(
			screen.queryByRole( 'button', { name: /add taxonomy rule/i } )
		).not.toBeInTheDocument();
	} );

	it( 'shows one blank rule with remove disabled when there are none', () => {
		render(
			<TaxonomyFieldRules
				value={ [] }
				onChange={ jest.fn() }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		expect(
			screen.getByText( 'Select Which taxonomies' )
		).toBeInTheDocument();
		expect(
			screen.getByRole( 'button', { name: 'Remove rule' } )
		).toBeDisabled();
	} );

	it( 'appends a blank rule when + is clicked', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<TaxonomyFieldRules
				value={ [ filledRule ] }
				onChange={ onChange }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		await user.click( screen.getByRole( 'button', { name: 'Add rule' } ) );

		expect( onChange ).toHaveBeenCalledWith( [
			filledRule,
			EMPTY_TAXONOMY_RULE,
		] );
	} );

	it( 'clears the only rule instead of removing it', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<TaxonomyFieldRules
				value={ [ filledRule ] }
				onChange={ onChange }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		await user.click(
			screen.getByRole( 'button', { name: 'Remove rule' } )
		);

		expect( onChange ).toHaveBeenCalledWith( [] );
	} );

	it( 'removes the clicked rule when there are several', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		const second = { ...filledRule, taxonomies: [ 'post_tag' ] };
		render(
			<TaxonomyFieldRules
				value={ [ filledRule, second ] }
				onChange={ onChange }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		await user.click(
			screen.getAllByRole( 'button', { name: 'Remove rule' } )[ 0 ]
		);

		expect( onChange ).toHaveBeenCalledWith( [ second ] );
	} );

	it( 'updates the rate', async () => {
		const onChange = jest.fn();
		const user = userEvent.setup();
		render(
			<TaxonomyFieldRules
				value={ [ { ...filledRule, rate: 5 } ] }
				onChange={ onChange }
				taxonomyOptions={ taxonomyOptions }
			/>
		);

		await user.type( screen.getByPlaceholderText( 'Rate' ), '0' );

		expect( onChange ).toHaveBeenLastCalledWith( [
			{ ...filledRule, rate: 50 },
		] );
	} );
} );
