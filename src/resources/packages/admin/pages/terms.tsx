import { useMemo } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { useGenerateModule } from '../hooks/use-generate-module';
import { transformTermsForm } from '../lib/transform';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { RangeInput } from '../components/form/range-input';
import { ComboboxMulti, type ComboboxOption } from '../components/form/combobox-multi';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import type { MetaRule } from '../lib/types';

interface TermsFormData {
	qty: { min: number; max: number };
	size: { min: number; max: number };
	taxonomies: string[];
	meta: MetaRule[];
}

export default function TermsPage() {
	const { data } = usePageConfig();
	const { generate, isGenerating, progress, results, error, reset } = useGenerateModule( 'terms' );

	const taxonomyOptions: ComboboxOption[] = useMemo( () => {
		const taxonomies = ( data.taxonomies || {} ) as Record< string, { name: string; label: string } >;
		return Object.values( taxonomies ).map( ( tax ) => ( {
			value: tax.name,
			label: tax.label,
		} ) );
	}, [ data.taxonomies ] );

	const defaultTaxonomies = useMemo( () => {
		return taxonomyOptions
			.filter( ( o ) => [ 'category', 'post_tag' ].includes( o.value ) )
			.map( ( o ) => o.value );
	}, [ taxonomyOptions ] );

	const { control, handleSubmit } = useForm< TermsFormData >( {
		defaultValues: {
			qty: { min: 3, max: 12 },
			size: { min: 2, max: 5 },
			taxonomies: defaultTaxonomies,
			meta: [],
		},
	} );

	const onSubmit = ( formData: TermsFormData ) => {
		reset();
		generate( transformTermsForm( formData ) );
	};

	return (
		<PageLayout title={ __( 'Generate Terms', 'fakerpress' ) }>
			{ results && (
				<AdminNotice
					type="success"
					title={ __( 'Success', 'fakerpress' ) }
					message={ `Generated ${ results.generated } terms in ${ results.time.toFixed( 2 ) }s.` }
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
						<FormField label={ __( 'Quantity', 'fakerpress' ) } description={ __( 'How many terms to generate.', 'fakerpress' ) }>
							<RangeInput
								minValue={ field.value.min }
								maxValue={ field.value.max }
								onMinChange={ ( min ) => field.onChange( { ...field.value, min } ) }
								onMaxChange={ ( max ) => field.onChange( { ...field.value, max } ) }
								min={ 1 }
								minPlaceholder="e.g.: 3"
								maxPlaceholder="e.g.: 12"
							/>
						</FormField>
					) }
				/>

				<Controller
					name="size"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Name Size', 'fakerpress' ) } description={ __( 'Number of words in the term name.', 'fakerpress' ) }>
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
					name="taxonomies"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Taxonomies', 'fakerpress' ) } description={ __( 'Select which taxonomies to create terms for.', 'fakerpress' ) }>
							<ComboboxMulti
								value={ field.value }
								onChange={ field.onChange }
								options={ taxonomyOptions }
								placeholder={ __( 'Select taxonomies...', 'fakerpress' ) }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="meta"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Meta Field Rules', 'fakerpress' ) } description={ __( 'Add custom meta fields to generated terms.', 'fakerpress' ) }>
							<MetaFieldRules value={ field.value } onChange={ field.onChange } />
						</FormField>
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
