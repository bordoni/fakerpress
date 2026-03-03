import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import TermsPage from '../terms';

describe( 'TermsPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'terms',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {},
			data: {
				taxonomies: {
					category: { name: 'category', label: 'Categories' },
					post_tag: { name: 'post_tag', label: 'Tags' },
				},
			},
		};
	} );

	it( 'renders all form fields', () => {
		render( <TermsPage /> );

		expect( screen.getByText( 'Quantity' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Name Size' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Taxonomies' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Meta Field Rules' ) ).toBeInTheDocument();
	} );

	it( 'renders Generate button', () => {
		render( <TermsPage /> );

		const generateButton = screen.getByRole( 'button', {
			name: /Generate/i,
		} );
		expect( generateButton ).toBeInTheDocument();
	} );

	it( 'renders taxonomy options', () => {
		render( <TermsPage /> );

		expect( screen.getByText( 'Categories' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Tags' ) ).toBeInTheDocument();
	} );
} );
