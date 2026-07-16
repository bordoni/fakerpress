import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import PostsPage from '../posts';

describe( 'PostsPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'posts',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {
				wp_query: 'nonce1',
				search_authors: 'nonce2',
				search_terms: 'nonce3',
			},
			data: {
				post_types: {
					post: { name: 'post', label: 'Posts' },
				},
				taxonomies: {
					category: { name: 'category', label: 'Categories' },
				},
				comment_statuses: [ 'open', 'closed' ],
				html_tags: [ 'h1', 'h2', 'p' ],
				image_providers: [
					{ value: 'placeholder', label: 'Placehold.co' },
				],
			},
		};
	} );

	it( 'renders all form fields', () => {
		render( <PostsPage /> );

		expect( screen.getByText( 'Quantity' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Post Type' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Comment Status' ) ).toBeInTheDocument();
		expect( screen.getByText( 'Content Size' ) ).toBeInTheDocument();
	} );

	it( 'renders Generate button', () => {
		render( <PostsPage /> );

		const generateButton = screen.getByRole( 'button', {
			name: /Generate/i,
		} );
		expect( generateButton ).toBeInTheDocument();
	} );

	it( 'renders taxonomy and meta rule sections', () => {
		render( <PostsPage /> );

		expect(
			screen.getByText( 'Taxonomy Field Rules' )
		).toBeInTheDocument();
		expect( screen.getByText( 'Meta Field Rules' ) ).toBeInTheDocument();
	} );
} );
