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
import { DateRangeField, getPresetRange } from '../components/form/date-range-field';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Checkbox } from '../components/ui/checkbox';
import { Label } from '../components/ui/label';
import { Input } from '../components/ui/input';
import type { MetaRule } from '../lib/types';

interface AttachmentsFormData {
	qty: { min: number | undefined; max: number | undefined };
	date: { preset: string; start: string; end: string };
	provider: string;
	width: { min: number | undefined; max: number | undefined };
	height: { min: number | undefined; max: number | undefined };
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

	const defaultPreset = 'yesterday';
	const defaultDateRange = getPresetRange( defaultPreset );

	const { control, handleSubmit } = useForm< AttachmentsFormData >( {
		defaultValues: {
			qty: { min: 3, max: 12 },
			date: { preset: defaultPreset, start: defaultDateRange?.[ 0 ] ?? '', end: defaultDateRange?.[ 1 ] ?? '' },
			provider: providerOptions[ 0 ]?.value || 'placeholder',
			width: { min: 200, max: 1200 },
			height: { min: undefined, max: undefined },
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
						<FormField
							label={ __( 'Quantity', 'fakerpress' ) }
							description={ __( 'How many attachments should be generated, use both fields to get a randomized number of attachments within the given range.', 'fakerpress' ) }
							tooltip={ __( 'Enter a minimum value to generate a fixed count, or add a maximum to randomise the count between the two numbers on each run.', 'fakerpress' ) }
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
					name="date"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Date', 'fakerpress' ) }
							description={ __( 'Choose the range for the attachment dates.', 'fakerpress' ) }
							tooltip={ __( "Pick a date preset (e.g. 'Last 7 days') or set custom Start and End dates. FakerPress will pick a random upload date within this range for each attachment.", 'fakerpress' ) }
						>
							<DateRangeField
								value={ field.value }
								onChange={ field.onChange }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="provider"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Image Provider', 'fakerpress' ) }
							description={ __( 'Choose which image service to use for generating attachments.', 'fakerpress' ) }
							tooltip={ __( "Selects the external service used to fetch placeholder images. 'Placeholder' works fully offline; the other providers make live HTTP requests.", 'fakerpress' ) }
						>
							<Select value={ field.value } onValueChange={ field.onChange }>
								<SelectTrigger className="fp:w-48">
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
						<FormField
							label={ __( 'Width', 'fakerpress' ) }
							description={ __( 'Image width range in pixels.', 'fakerpress' ) }
							tooltip={ __( 'Enter only a minimum to use a fixed width, or add a maximum to randomise width (in pixels) within that range for each generated image.', 'fakerpress' ) }
						>
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
						<FormField
							label={ __( 'Height', 'fakerpress' ) }
							description={ __( 'Image height range in pixels. Leave at 0 to use aspect ratio instead.', 'fakerpress' ) }
							tooltip={ __( 'Enter a pixel height range, or leave both fields empty to derive height automatically from the Aspect Ratio instead.', 'fakerpress' ) }
						>
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
						<FormField
							label={ __( 'Aspect Ratio', 'fakerpress' ) }
							description={ __( 'Width/Height ratio (e.g., 1.5 for 3:2, 1.77 for 16:9). Only used when height is 0.', 'fakerpress' ) }
							tooltip={ __( 'Active only when Height is left empty. Expressed as width ÷ height — e.g. 1.78 for 16:9, 1.33 for 4:3, or 1.0 for a square.', 'fakerpress' ) }
						>
							<Input
								type="number"
								value={ field.value }
								onChange={ ( e ) => field.onChange( Number( e.target.value ) ) }
								step={ 0.1 }
								min={ 0 }
								className="fp:w-24"
							/>
						</FormField>
					) }
				/>

				<Controller
					name="parents"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Parent Posts', 'fakerpress' ) }
							description={ __( 'Attach generated images to specific posts.', 'fakerpress' ) }
							tooltip={ __( 'Search for and select existing posts to attach the generated images to. Leave empty to create unattached (orphan) media library items.', 'fakerpress' ) }
						>
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

				<Controller
					name="alt_text"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Alt Text', 'fakerpress' ) }
							tooltip={ __( 'When enabled, FakerPress generates a random descriptive phrase as the image alt text, improving the realism of test content for accessibility testing.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Checkbox
									id="alt_text"
									checked={ field.value }
									onCheckedChange={ field.onChange }
								/>
								<Label htmlFor="alt_text">
									{ __( 'Generate alt text for accessibility', 'fakerpress' ) }
								</Label>
							</div>
						</FormField>
					) }
				/>

				<Controller
					name="caption"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Caption', 'fakerpress' ) }
							tooltip={ __( 'When enabled, FakerPress generates a short sentence as the image caption, which appears below images in galleries and in many themes.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Checkbox
									id="caption"
									checked={ field.value }
									onCheckedChange={ field.onChange }
								/>
								<Label htmlFor="caption">
									{ __( 'Generate image captions', 'fakerpress' ) }
								</Label>
							</div>
						</FormField>
					) }
				/>

				<Controller
					name="description"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Description', 'fakerpress' ) }
							tooltip={ __( 'When enabled, FakerPress generates a longer paragraph for the attachment\'s description field, visible in the media library detail pane.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Checkbox
									id="description"
									checked={ field.value }
									onCheckedChange={ field.onChange }
								/>
								<Label htmlFor="description">
									{ __( 'Generate image descriptions', 'fakerpress' ) }
								</Label>
							</div>
						</FormField>
					) }
				/>

				<Controller
					name="author"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Author', 'fakerpress' ) }
							description={ __( 'Choose users to be owners of generated attachments.', 'fakerpress' ) }
							tooltip={ __( 'Search for and select one or more users. FakerPress randomly assigns each attachment to one of the chosen users. Leave empty to assign to the currently logged-in user.', 'fakerpress' ) }
						>
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
						<MetaFieldRules
							value={ field.value }
							onChange={ field.onChange }
							description={ __( 'Use the fields below to configure a set of rules for your generated Attachments.', 'fakerpress' ) }
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
