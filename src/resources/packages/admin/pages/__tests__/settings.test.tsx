import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import '@testing-library/jest-dom';
import SettingsPage from '../settings';

describe( 'SettingsPage', () => {
	beforeEach( () => {
		( window as any ).fakerpressPageConfig = {
			page: 'settings',
			restRoot: 'http://localhost/wp-json/',
			restNonce: 'test-nonce',
			ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
			ajaxNonces: {},
			data: {
				erase_phrase: 'Let it Go!',
			},
		};
	} );

	it( 'renders erase phrase input', () => {
		render( <SettingsPage /> );

		const input = screen.getByPlaceholderText(
			'The cold never bothered me anyway!'
		);
		expect( input ).toBeInTheDocument();
	} );

	it( 'Delete button disabled when phrase does not match', () => {
		render( <SettingsPage /> );

		const deleteButton = screen.getByRole( 'button', {
			name: /Delete!/i,
		} );
		expect( deleteButton ).toBeDisabled();
	} );

	it( 'Delete button enabled when phrase matches', async () => {
		render( <SettingsPage /> );

		const input = screen.getByPlaceholderText(
			'The cold never bothered me anyway!'
		);
		await userEvent.type( input, 'Let it Go!' );

		const deleteButton = screen.getByRole( 'button', {
			name: /Delete!/i,
		} );
		expect( deleteButton ).not.toBeDisabled();
	} );
} );
