import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import UsersPage from '../users';

describe( 'UsersPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'users',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {},
			data: {
				roles: [
					{ value: 'administrator', label: 'Administrator' },
					{ value: 'subscriber', label: 'Subscriber' },
				],
				html_tags: [ 'h3', 'h4', 'p' ],
			},
		};
	} );

	it( 'renders all form fields', () => {
		render( <UsersPage /> );

		expect( screen.getByText( 'Quantity' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Roles' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Description Size' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Use HTML' ) ).toBeInTheDocument();
	} );

	it( 'renders Generate button', () => {
		render( <UsersPage /> );

		const generateButton = screen.getByRole( 'button', {
			name: /Generate/i,
		} );
		expect( generateButton ).toBeInTheDocument();
	} );

	it( 'shows HTML tags field when Use HTML is enabled', () => {
		render( <UsersPage /> );

		expect( screen.getByText( 'HTML Tags' ) ).toBeInTheDocument();
	} );
} );
