import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import CommentsPage from '../comments';

describe( 'CommentsPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'comments',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {},
			data: {
				comment_types: [ 'default' ],
				post_types: {
					post: { name: 'post', label: 'Posts' },
				},
				html_tags: [ 'h1', 'h2', 'p' ],
			},
		};
	} );

	it( 'renders all form fields', () => {
		render( <CommentsPage /> );

		expect( screen.getByText( 'Type' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Post Type' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Quantity' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Date' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Content Size' ) ).toBeInTheDocument();
	} );

	it( 'renders Generate button', () => {
		render( <CommentsPage /> );

		const generateButton = screen.getByRole( 'button', {
			name: /Generate/i,
		} );
		expect( generateButton ).toBeInTheDocument();
	} );

	it( 'renders Use HTML toggle', () => {
		render( <CommentsPage /> );

		expect( screen.getByText( 'Use HTML' ) ).toBeInTheDocument();
	} );
} );
