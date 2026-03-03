import { useMemo } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { useGenerateModule } from '../hooks/use-generate-module';
import { useAsyncSearch } from '../hooks/use-async-search';
import { transformAttachmentsForm } from '../lib/transform';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { RangeInput } from '../components/form/range-input';
import { ComboboxMulti, type ComboboxOption } from '../components/form/combobox-multi';
import { DateRangeField } from '../components/form/date-range-field';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Checkbox } from '../components/ui/checkbox';
import { Label } from '../components/ui/label';
import { Input } from '../components/ui/input';
import type { MetaRule } from '../lib/types';

interface AttachmentsFormData {
	qty: { min: number; max: number };
	date: { preset: string; start: string; end: string };
	provider: string;
	width: { min: number; max: number };
	height: { min: number; max: number };
	aspect_ratio: number;
	parents: number[];
	author: number[];
	alt_text: boolean;
	caption: boolean;
	description: boolean;
	meta: MetaRule[];
}

export default function AttachmentsPage() {
	const { data, ajaxNonces } = usePageConfig();
	const { generate, isGenerating, progress, results, error, reset } = useGenerateModule( 'attachments' );

	const providerOptions = useMemo( () => {
		return ( data.image_providers || [] ) as { value: string; label: string }[];
	}, [ data.image_providers ] );

	const postSearch = useAsyncSearch( 'fakerpress.select2-WP_Query', ajaxNonces.wp_query || '' );
	const authorSearch = useAsyncSearch( 'fakerpress.search_authors', ajaxNonces.search_authors || '' );

	const postSearchOptions: ComboboxOption[] = useMemo( () => {
		return postSearch.results.map( ( r ) => ( {
			value: String( r.id ),
			label: r.name || String( r.id ),
		} ) );
	}, [ postSearch.results ] );

	const authorSearchOptions: ComboboxOption[] = useMemo( () => {
		return authorSearch.results.map( ( r ) => ( {
			value: String( r.id ),
			label: r.name || String( r.id ),
		} ) );
	}, [ authorSearch.results ] );

	const { control, handleSubmit } = useForm< AttachmentsFormData >( {
		defaultValues: {
			qty: { min: 3, max: 12 },
			date: { preset: 'yesterday', start: '', end: '' },
			provider: providerOptions[ 0 ]?.value || 'placeholder',
			width: { min: 200, max: 1200 },
			height: { min: 0, max: 0 },
			aspect_ratio: 1.5,
			parents: [],
			author: [],
			alt_text: true,
			caption: true,
			description: true,
			meta: [],
		},
	} );

	const onSubmit = ( formData: AttachmentsFormData ) => {
		reset();
		generate( transformAttachmentsForm( formData ) );
	};

	return (
		<PageLayout title={ __( 'Generate Attachments', 'fakerpress' ) }>
			{ results && (
				<AdminNotice
					type="success"
					title={ __( 'Success', 'fakerpress' ) }
					message={ `Generated ${ results.generated } attachments in ${ results.time.toFixed( 2 ) }s.` }
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
						<FormField label={ __( 'Quantity', 'fakerpress' ) }>
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
					name="date"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Date', 'fakerpress' ) }>
							<DateRangeField
								startDate={ field.value.start }
								endDate={ field.value.end }
								onStartChange={ ( start ) => field.onChange( { ...field.value, start } ) }
								onEndChange={ ( end ) => field.onChange( { ...field.value, end } ) }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="provider"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Image Provider', 'fakerpress' ) }>
							<Select value={ field.value } onValueChange={ field.onChange }>
								<SelectTrigger className="fp-w-48">
									<SelectValue />
								</SelectTrigger>
								<SelectContent>
									{ providerOptions.map( ( p ) => (
										<SelectItem key={ p.value } value={ p.value }>
											{ p.label }
										</SelectItem>
									) ) }
								</SelectContent>
							</Select>
						</FormField>
					) }
				/>

				<Controller
					name="width"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Width', 'fakerpress' ) }>
							<RangeInput
								minValue={ field.value.min }
								maxValue={ field.value.max }
								onMinChange={ ( min ) => field.onChange( { ...field.value, min } ) }
								onMaxChange={ ( max ) => field.onChange( { ...field.value, max } ) }
								min={ 50 }
								max={ 3000 }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="height"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Height', 'fakerpress' ) } description={ __( 'Set to 0 to use aspect ratio instead.', 'fakerpress' ) }>
							<RangeInput
								minValue={ field.value.min }
								maxValue={ field.value.max }
								onMinChange={ ( min ) => field.onChange( { ...field.value, min } ) }
								onMaxChange={ ( max ) => field.onChange( { ...field.value, max } ) }
								min={ 0 }
								max={ 3000 }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="aspect_ratio"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Aspect Ratio', 'fakerpress' ) } description={ __( 'Width/Height ratio. Used when Height is 0.', 'fakerpress' ) }>
							<Input
								type="number"
								value={ field.value }
								onChange={ ( e ) => field.onChange( Number( e.target.value ) ) }
								step={ 0.1 }
								min={ 0 }
								className="fp-w-24"
							/>
						</FormField>
					) }
				/>

				<Controller
					name="parents"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Parent Posts', 'fakerpress' ) }>
							<ComboboxMulti
								value={ field.value.map( String ) }
								onChange={ ( vals ) => field.onChange( vals.map( Number ) ) }
								placeholder={ __( 'Search for posts...', 'fakerpress' ) }
								onSearch={ postSearch.search }
								searchResults={ postSearchOptions }
								isSearching={ postSearch.isSearching }
							/>
						</FormField>
					) }
				/>

				<FormField label={ __( 'Generate Fields', 'fakerpress' ) }>
					<div className="fp-space-y-2">
						<Controller
							name="alt_text"
							control={ control }
							render={ ( { field } ) => (
								<div className="fp-flex fp-items-center fp-gap-2">
									<Checkbox
										id="alt_text"
										checked={ field.value }
										onCheckedChange={ field.onChange }
									/>
									<Label htmlFor="alt_text">{ __( 'Alt Text', 'fakerpress' ) }</Label>
								</div>
							) }
						/>
						<Controller
							name="caption"
							control={ control }
							render={ ( { field } ) => (
								<div className="fp-flex fp-items-center fp-gap-2">
									<Checkbox
										id="caption"
										checked={ field.value }
										onCheckedChange={ field.onChange }
									/>
									<Label htmlFor="caption">{ __( 'Caption', 'fakerpress' ) }</Label>
								</div>
							) }
						/>
						<Controller
							name="description"
							control={ control }
							render={ ( { field } ) => (
								<div className="fp-flex fp-items-center fp-gap-2">
									<Checkbox
										id="description"
										checked={ field.value }
										onCheckedChange={ field.onChange }
									/>
									<Label htmlFor="description">{ __( 'Description', 'fakerpress' ) }</Label>
								</div>
							) }
						/>
					</div>
				</FormField>

				<Controller
					name="author"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Author', 'fakerpress' ) }>
							<ComboboxMulti
								value={ field.value.map( String ) }
								onChange={ ( vals ) => field.onChange( vals.map( Number ) ) }
								placeholder={ __( 'Search for authors...', 'fakerpress' ) }
								onSearch={ authorSearch.search }
								searchResults={ authorSearchOptions }
								isSearching={ authorSearch.isSearching }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="meta"
					control={ control }
					render={ ( { field } ) => (
						<FormField label={ __( 'Meta Field Rules', 'fakerpress' ) }>
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
