import { useState, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { Input } from '../components/ui/input';
import { Button } from '../components/ui/button';
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
	DialogTrigger,
} from '../components/ui/dialog';

export default function SettingsPage() {
	const { data, restNonce } = usePageConfig();
	const erasePhrase = ( data.erase_phrase as string ) || 'Let it Go!';

	const [ phrase, setPhrase ] = useState( '' );
	const [ dialogOpen, setDialogOpen ] = useState( false );
	const [ status, setStatus ] = useState< { type: 'success' | 'error'; message: string } | null >( null );
	const [ isSubmitting, setIsSubmitting ] = useState( false );

	const phraseMatches = phrase.toLowerCase().replace( /!$/, '' ) === erasePhrase.toLowerCase().replace( /!$/, '' );

	const handleDelete = useCallback( async () => {
		setIsSubmitting( true );
		setStatus( null );

		try {
			// Submit as a traditional form POST to match the PHP handler.
			const form = document.createElement( 'form' );
			form.method = 'POST';
			form.style.display = 'none';

			const addField = ( name: string, value: string ) => {
				const input = document.createElement( 'input' );
				input.type = 'hidden';
				input.name = name;
				input.value = value;
				form.appendChild( input );
			};

			addField( '_wpnonce', restNonce );
			addField( 'fakerpress[erase_phrase]', phrase );
			addField( 'fakerpress[actions][delete]', '1' );
			addField( '_wp_http_referer', window.location.href );

			// Use the actual WP nonce from the page.
			const wpNonceInput = document.querySelector< HTMLInputElement >( 'input[name="_wpnonce"]' );
			if ( wpNonceInput ) {
				addField( '_wpnonce', wpNonceInput.value );
			}

			document.body.appendChild( form );
			form.submit();
		} catch {
			setStatus( { type: 'error', message: __( 'Failed to delete data. Please try again.', 'fakerpress' ) } );
			setIsSubmitting( false );
		}
	}, [ phrase, restNonce ] );

	return (
		<PageLayout title={ __( 'Settings', 'fakerpress' ) }>
			{ status && (
				<AdminNotice
					type={ status.type }
					title={ status.type === 'success' ? __( 'Success', 'fakerpress' ) : __( 'Error', 'fakerpress' ) }
					message={ status.message }
					onDismiss={ () => setStatus( null ) }
				/>
			) }

			<FormField
				label={ __( 'Erase faked data', 'fakerpress' ) }
				htmlFor="erase_phrase"
				description={ __( 'To erase all data generated type "Let it Go!", please back up your database before you proceed!', 'fakerpress' ) }
			>
				<Input
					id="erase_phrase"
					value={ phrase }
					onChange={ ( e ) => setPhrase( e.target.value ) }
					placeholder={ __( 'The cold never bothered me anyway!', 'fakerpress' ) }
				/>
			</FormField>

			<div className="fp:pt-4">
				<Dialog open={ dialogOpen } onOpenChange={ setDialogOpen }>
					<DialogTrigger asChild>
						<Button
							variant="destructive"
							disabled={ ! phraseMatches }
						>
							{ __( 'Delete!', 'fakerpress' ) }
						</Button>
					</DialogTrigger>
					<DialogContent>
						<DialogHeader>
							<DialogTitle>{ __( 'Confirm Deletion', 'fakerpress' ) }</DialogTitle>
							<DialogDescription>
								{ __( 'This will permanently delete all FakerPress-generated data. This action cannot be undone.', 'fakerpress' ) }
							</DialogDescription>
						</DialogHeader>
						<DialogFooter>
							<Button variant="outline" onClick={ () => setDialogOpen( false ) }>
								{ __( 'Cancel', 'fakerpress' ) }
							</Button>
							<Button
								variant="destructive"
								onClick={ handleDelete }
								disabled={ isSubmitting }
							>
								{ isSubmitting ? __( 'Deleting...', 'fakerpress' ) : __( 'Delete All Data', 'fakerpress' ) }
							</Button>
						</DialogFooter>
					</DialogContent>
				</Dialog>
			</div>
		</PageLayout>
	);
}
