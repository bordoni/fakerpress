import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import AttachmentsPage from '../attachments';

describe( 'AttachmentsPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'attachments',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {
				wp_query: 'nonce1',
				search_authors: 'nonce2',
			},
			data: {
				image_providers: [
					{ value: 'placeholder', label: 'Placehold.co' },
				],
			},
		};
	} );

	it( 'renders all form fields', () => {
		render( <AttachmentsPage /> );

		expect( screen.getByText( 'Quantity' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Width' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Height' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Aspect Ratio' ) ).toBeInTheDocument();
	} );

	it( 'renders Generate button', () => {
		render( <AttachmentsPage /> );

		const generateButton = screen.getByRole( 'button', {
			name: /Generate/i,
		} );
		expect( generateButton ).toBeInTheDocument();
	} );

	it( 'renders checkbox options', () => {
		render( <AttachmentsPage /> );

		expect( screen.getByText( 'Alt Text' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Caption' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Description' ) ).toBeInTheDocument();
	} );
} );
