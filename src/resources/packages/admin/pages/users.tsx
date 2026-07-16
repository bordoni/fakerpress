import { useMemo } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { useGenerateModule } from '../hooks/use-generate-module';
import { transformUsersForm } from '../lib/transform';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { RangeInput } from '../components/form/range-input';
import { ComboboxMulti, type ComboboxOption } from '../components/form/combobox-multi';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import { Switch } from '../components/ui/switch';
import { Label } from '../components/ui/label';
import type { MetaRule } from '../lib/types';

interface UsersFormData {
	qty: { min: number | undefined; max: number | undefined };
	roles: string[];
	description_size: { min: number | undefined; max: number | undefined };
	use_html: boolean;
	html_tags: string[];
	meta: MetaRule[];
}

export default function UsersPage() {
	const { data } = usePageConfig();
	const { generate, isGenerating, progress, results, error, reset } = useGenerateModule( 'users' );

	const roleOptions: ComboboxOption[] = useMemo( () => {
		const roles = ( data.roles || [] ) as { value: string; label: string }[];
		return roles.map( ( r ) => ( { value: r.value, label: r.label } ) );
	}, [ data.roles ] );

	const htmlTagOptions: ComboboxOption[] = useMemo( () => {
		const tags = ( data.html_tags || [] ) as string[];
		return tags.map( ( tag ) => ( { value: tag, label: tag } ) );
	}, [ data.html_tags ] );

	const { control, handleSubmit, watch } = useForm< UsersFormData >( {
		defaultValues: {
			qty: { min: 3, max: 12 },
			roles: [],
			description_size: { min: 1, max: 5 },
			use_html: true,
			html_tags: ( data.html_tags || [] ) as string[],
			meta: [],
		},
	} );

	const useHtml = watch( 'use_html' );

	const onSubmit = ( formData: UsersFormData ) => {
		reset();
		generate( transformUsersForm( formData ) );
	};

	return (
		<PageLayout title={ __( 'Generate Users', 'fakerpress' ) }>
			{ results && (
				<AdminNotice
					type="success"
					title={ __( 'Success', 'fakerpress' ) }
					message={ `Generated ${ results.generated } users in ${ results.time.toFixed( 2 ) }s.` }
				/>
			) }
			{ error && (
				<AdminNotice type="error" title={ __( 'Error', 'fakerpress' ) } message={ error } />
			) }

			<form onSubmit={ handleSubmit( onSubmit ) }>
				<Controller
					name="qty"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Quantity', 'fakerpress' ) }
							description={ __( 'How many users to generate. Use both fields to randomize within the given range.', 'fakerpress' ) }
						>
							<RangeInput
								minValue={ field.value.min }
								maxValue={ field.value.max }
								onMinChange={ ( min ) => field.onChange( { ...field.value, min } ) }
								onMaxChange={ ( max ) => field.onChange( { ...field.value, max } ) }
								min={ 1 }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="roles"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Roles', 'fakerpress' ) }
							description={ __( 'Select which roles to assign to generated users. Leave empty for random roles.', 'fakerpress' ) }
						>
							<ComboboxMulti
								value={ field.value }
								onChange={ field.onChange }
								options={ roleOptions }
								placeholder={ __( 'Select roles...', 'fakerpress' ) }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="description_size"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Description Size', 'fakerpress' ) }
							description={ __( 'Number of sentences in the user biographical description.', 'fakerpress' ) }
						>
							<RangeInput
								minValue={ field.value.min }
								maxValue={ field.value.max }
								onMinChange={ ( min ) => field.onChange( { ...field.value, min } ) }
								onMaxChange={ ( max ) => field.onChange( { ...field.value, max } ) }
								min={ 0 }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="use_html"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Use HTML', 'fakerpress' ) }
							description={ __( 'When enabled, user descriptions will include HTML markup.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Switch
									id="use_html"
									checked={ field.value }
									onCheckedChange={ field.onChange }
								/>
								<Label htmlFor="use_html" className="fp:text-sm">
									{ __( 'Enable HTML in descriptions', 'fakerpress' ) }
								</Label>
							</div>
						</FormField>
					) }
				/>

				{ useHtml && (
					<Controller
						name="html_tags"
						control={ control }
						render={ ( { field } ) => (
							<FormField
								label={ __( 'HTML Tags', 'fakerpress' ) }
								description={ __( 'HTML elements that can appear in generated user descriptions.', 'fakerpress' ) }
							>
								<ComboboxMulti
									value={ field.value }
									onChange={ field.onChange }
									options={ htmlTagOptions }
									placeholder={ __( 'Select HTML tags...', 'fakerpress' ) }
									allowCreate
								/>
							</FormField>
						) }
					/>
				) }

				<Controller
					name="meta"
					control={ control }
					render={ ( { field } ) => (
						<MetaFieldRules
							value={ field.value }
							onChange={ field.onChange }
							description={ __( 'Use the fields below to configure a set of rules for your generated Users.', 'fakerpress' ) }
						/>
					) }
				/>

				<GenerateButton
					onClick={ handleSubmit( onSubmit ) }
					isGenerating={ isGenerating }
					progress={ progress }
					results={ results }
					error={ error }
				/>
			</form>
		</PageLayout>
	);
}
