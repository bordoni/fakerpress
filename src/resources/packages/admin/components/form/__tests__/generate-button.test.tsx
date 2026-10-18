import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import { GenerateButton } from '../generate-button';
import type { GenerateResult } from '../../../lib/types';

const results: GenerateResult = {
	generated: 2,
	ids: [ 11, 12 ],
	links: [],
	time: 1.234,
	items: [
		{
			id: 11,
			title: 'Hello Faker',
			type: 'post',
			status: 'publish',
			date: '2026-10-18T14:00:00+00:00',
			summary: [
				{ label: 'Status', value: 'Published' },
				{ label: 'Author', value: 'Jane Doe' },
			],
			edit_url:
				'https://example.test/wp-admin/post.php?post=11&action=edit',
			view_url: 'https://example.test/?p=11',
			thumbnail: null,
			details: {
				post_content: 'Lorem ipsum',
				meta: { fp_email: 'a@b.c' },
			},
		},
		{
			id: 12,
			title: 'Second post',
			type: 'post',
			status: 'draft',
			date: null,
			summary: [],
			edit_url: null,
			view_url: null,
			thumbnail: null,
			details: {},
		},
	],
	listUrl: 'https://example.test/wp-admin/edit.php?post_type=post',
	batches: [
		{
			generated: 2,
			time: 1.234,
			request: { qty: { min: 2, max: 2 }, offset: 0 },
			response: { success: true, data: { generated: 2 } },
		},
	],
	finishedAt: '2026-10-18T14:00:01.000Z',
};

describe( 'GenerateButton', () => {
	const defaultProps = {
		onClick: jest.fn(),
		isGenerating: false,
		progress: null,
		results: null,
		error: null,
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders Generate button', () => {
		render( <GenerateButton { ...defaultProps } /> );

		expect(
			screen.getByRole( 'button', { name: /generate/i } )
		).toBeInTheDocument();
		expect( screen.getByRole( 'button' ) ).toHaveTextContent( 'Generate' );
		expect( screen.queryByRole( 'status' ) ).not.toBeInTheDocument();
	} );

	it( 'is a plain button, so a click does not also submit the form', () => {
		render( <GenerateButton { ...defaultProps } /> );

		expect( screen.getByRole( 'button' ) ).toHaveAttribute(
			'type',
			'button'
		);
	} );

	it( 'shows progress next to the button during generation', () => {
		render(
			<GenerateButton
				{ ...defaultProps }
				isGenerating={ true }
				progress={ { current: 5, total: 10 } }
			/>
		);

		expect(
			screen.getByRole( 'button', { name: /generating/i } )
		).toBeDisabled();
		expect( screen.getByRole( 'status' ) ).toHaveTextContent(
			'5 of 10 generated'
		);
	} );

	it( 'names what was generated and how long it took', () => {
		render(
			<GenerateButton
				{ ...defaultProps }
				module="posts"
				results={ results }
			/>
		);

		const status = screen.getByRole( 'status' );
		expect( status ).toHaveAttribute( 'data-status', 'success' );
		expect( status ).toHaveTextContent( '2 posts generated' );
		expect( status ).toHaveTextContent( '1.23s' );
	} );

	it( 'uses the singular noun for one item', () => {
		render(
			<GenerateButton
				{ ...defaultProps }
				module="users"
				results={ { ...results, generated: 1 } }
			/>
		);

		expect( screen.getByRole( 'status' ) ).toHaveTextContent(
			'1 user generated'
		);
	} );

	it( 'opens the details dialog with every item, its links and the full data', async () => {
		const user = userEvent.setup();
		render(
			<GenerateButton
				{ ...defaultProps }
				module="posts"
				results={ results }
			/>
		);

		await user.click(
			screen.getByRole( 'button', { name: /view details/i } )
		);

		const dialog = screen.getByRole( 'dialog' );
		expect(
			within( dialog ).getByRole( 'heading', {
				name: '2 posts generated',
			} )
		).toBeInTheDocument();
		expect(
			within( dialog ).getByRole( 'link', { name: 'Hello Faker' } )
		).toHaveAttribute( 'href', results.items[ 0 ].edit_url );
		expect( within( dialog ).getByText( 'Jane Doe' ) ).toBeInTheDocument();
		expect(
			within( dialog ).getByText( 'Second post' )
		).toBeInTheDocument();
		expect(
			within( dialog ).getByRole( 'link', { name: /open list/i } )
		).toHaveAttribute( 'href', results.listUrl );

		await user.click(
			within( dialog ).getByRole( 'button', {
				name: /advanced: full data/i,
			} )
		);
		expect( within( dialog ).getByText( /fp_email/ ) ).toBeInTheDocument();

		await user.click(
			within( dialog ).getByRole( 'tab', { name: 'Request' } )
		);
		expect(
			within( dialog ).getByText( /"offset": 0/ )
		).toBeInTheDocument();
	} );

	it( 'shows the error on the button and its code in the details', async () => {
		const user = userEvent.setup();
		render(
			<GenerateButton
				{ ...defaultProps }
				module="posts"
				error="Sorry, you are not allowed to do that."
				errorDetails={ {
					message: 'Sorry, you are not allowed to do that.',
					code: 'rest_forbidden',
					status: 403,
					request: { offset: 0 },
					response: { code: 'rest_forbidden' },
					batches: [],
					finishedAt: '2026-10-18T14:00:01.000Z',
				} }
			/>
		);

		const status = screen.getByRole( 'status' );
		expect( status ).toHaveAttribute( 'data-status', 'error' );
		expect( status ).toHaveTextContent( "Couldn't generate" );
		expect( status ).toHaveTextContent( 'not allowed' );

		await user.click( screen.getByRole( 'button', { name: /details/i } ) );
		const dialog = screen.getByRole( 'dialog' );
		expect(
			within( dialog ).getByText( 'rest_forbidden' )
		).toBeInTheDocument();
		expect( within( dialog ).getByText( '403' ) ).toBeInTheDocument();
	} );

	it( 'dismisses the result', async () => {
		const user = userEvent.setup();
		const onDismiss = jest.fn();
		render(
			<GenerateButton
				{ ...defaultProps }
				results={ results }
				onDismiss={ onDismiss }
			/>
		);

		await user.click( screen.getByRole( 'button', { name: 'Dismiss' } ) );
		expect( onDismiss ).toHaveBeenCalled();
	} );
} );
