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
			requestNonce: 'settings-request-nonce',
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

	it( 'posts the settings request nonce, not the REST nonce, when deleting', async () => {
		let submitted: HTMLFormElement | null = null;
		const submit = jest
			.spyOn( HTMLFormElement.prototype, 'submit' )
			.mockImplementation( function ( this: HTMLFormElement ) {
				submitted = this;
			} );

		render( <SettingsPage /> );

		await userEvent.type(
			screen.getByPlaceholderText( 'The cold never bothered me anyway!' ),
			'Let it Go!'
		);
		await userEvent.click(
			screen.getByRole( 'button', { name: /Delete!/i } )
		);
		await userEvent.click(
			screen.getByRole( 'button', { name: /Delete All Data/i } )
		);

		expect( submit ).toHaveBeenCalledTimes( 1 );
		const data = new FormData( submitted! );
		expect( data.getAll( '_wpnonce' ) ).toEqual( [
			'settings-request-nonce',
		] );
		expect( data.get( 'fakerpress[erase_phrase]' ) ).toBe( 'Let it Go!' );
		expect( data.get( 'fakerpress[actions][delete]' ) ).toBe( '1' );

		submit.mockRestore();
	} );
} );
