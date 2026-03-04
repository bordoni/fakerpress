import { useMemo } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { useGenerateModule } from '../hooks/use-generate-module';
import { useAsyncSearch } from '../hooks/use-async-search';
import { transformPostsForm } from '../lib/transform';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { RangeInput } from '../components/form/range-input';
import { ComboboxMulti, type ComboboxOption } from '../components/form/combobox-multi';
import { DateRangeField } from '../components/form/date-range-field';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { TaxonomyFieldRules } from '../components/form/taxonomy-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import { Switch } from '../components/ui/switch';
import { Label } from '../components/ui/label';
import type { MetaRule, TaxonomyRule } from '../lib/types';

interface PostsFormData {
	qty: { min: number | undefined; max: number | undefined };
	date: { preset: string; start: string; end: string };
	post_type: string[];
	parents: number[];
	comment_status: string[];
	authors: number[];
	use_html: boolean;
	content_size: { min: number | undefined; max: number | undefined };
	html_tags: string[];
	image_providers: string[];
	excerpt_size: { min: number | undefined; max: number | undefined };
	taxonomy_rules: TaxonomyRule[];
	meta: MetaRule[];
}

export default function PostsPage() {
	const { data, ajaxNonces } = usePageConfig();
	const { generate, isGenerating, progress, results, error, reset } = useGenerateModule( 'posts' );

	const postTypeOptions: ComboboxOption[] = useMemo( () => {
		const postTypes = ( data.post_types || {} ) as Record< string, { name: string; label: string } >;
		return Object.values( postTypes ).map( ( pt ) => ( {
			value: pt.name,
			label: pt.label,
		} ) );
	}, [ data.post_types ] );

	const taxonomyOptions: ComboboxOption[] = useMemo( () => {
		const taxonomies = ( data.taxonomies || {} ) as Record< string, { name: string; label: string } >;
		return Object.values( taxonomies ).map( ( tax ) => ( {
			value: tax.name,
			label: tax.label,
		} ) );
	}, [ data.taxonomies ] );

	const commentStatusOptions: ComboboxOption[] = useMemo( () => {
		const statuses = ( data.comment_statuses || [] ) as string[];
		return statuses.map( ( s ) => ( { value: s, label: s } ) );
	}, [ data.comment_statuses ] );

	const htmlTagOptions: ComboboxOption[] = useMemo( () => {
		const tags = ( data.html_tags || [] ) as string[];
		return tags.map( ( tag ) => ( { value: tag, label: tag } ) );
	}, [ data.html_tags ] );

	const imageProviderOptions: ComboboxOption[] = useMemo( () => {
		return ( data.image_providers || [] ) as { value: string; label: string }[];
	}, [ data.image_providers ] );

	const defaultPostTypes = useMemo( () => {
		return postTypeOptions
			.filter( ( o ) => o.value === 'post' )
			.map( ( o ) => o.value );
	}, [ postTypeOptions ] );

	const defaultHtmlTags = useMemo( () => {
		return ( data.html_tags || [] ) as string[];
	}, [ data.html_tags ] );

	const parentSearch = useAsyncSearch( 'fakerpress.select2-WP_Query', ajaxNonces.wp_query || '' );
	const authorSearch = useAsyncSearch( 'fakerpress.search_authors', ajaxNonces.search_authors || '' );
	const termSearch = useAsyncSearch( 'fakerpress.search_terms', ajaxNonces.search_terms || '' );

	const parentSearchOptions: ComboboxOption[] = useMemo( () => {
		return parentSearch.results.map( ( r ) => ( {
			value: String( r.id ),
			label: r.name || String( r.id ),
		} ) );
	}, [ parentSearch.results ] );

	const authorSearchOptions: ComboboxOption[] = useMemo( () => {
		return authorSearch.results.map( ( r ) => ( {
			value: String( r.id ),
			label: r.name || String( r.id ),
		} ) );
	}, [ authorSearch.results ] );

	const termSearchOptions: ComboboxOption[] = useMemo( () => {
		return termSearch.results.map( ( r ) => ( {
			value: String( r.id ),
			label: r.name || String( r.id ),
		} ) );
	}, [ termSearch.results ] );

	const { control, handleSubmit, watch } = useForm< PostsFormData >( {
		defaultValues: {
			qty: { min: 3, max: 12 },
			date: { preset: 'yesterday', start: '', end: '' },
			post_type: defaultPostTypes,
			parents: [],
			comment_status: [ 'open' ],
			authors: [],
			use_html: true,
			content_size: { min: 1, max: 5 },
			html_tags: defaultHtmlTags,
			image_providers: [],
			excerpt_size: { min: 1, max: 3 },
			taxonomy_rules: [],
			meta: [],
		},
	} );

	const useHtml = watch( 'use_html' );

	const onSubmit = ( formData: PostsFormData ) => {
		reset();
		generate( transformPostsForm( formData ) );
	};

	return (
		<PageLayout title={ __( 'Generate Posts', 'fakerpress' ) }>
			{ results && (
				<AdminNotice
					type="success"
					title={ __( 'Success', 'fakerpress' ) }
					message={ `Generated ${ results.generated } posts in ${ results.time.toFixed( 2 ) }s.` }
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
							description={ __( 'How many posts to generate. Use both fields to randomize within the given range.', 'fakerpress' ) }
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
							description={ __( 'Choose the range for the post dates.', 'fakerpress' ) }
						>
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
					name="post_type"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Post Type', 'fakerpress' ) }
							description={ __( 'Select which post types to generate.', 'fakerpress' ) }
						>
							<ComboboxMulti
								value={ field.value }
								onChange={ field.onChange }
								options={ postTypeOptions }
								placeholder={ __( 'Select post types...', 'fakerpress' ) }
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
							description={ __( 'Optionally assign generated posts as children of these posts.', 'fakerpress' ) }
						>
							<ComboboxMulti
								value={ field.value.map( String ) }
								onChange={ ( vals ) => field.onChange( vals.map( Number ) ) }
								placeholder={ __( 'Search for posts...', 'fakerpress' ) }
								onSearch={ parentSearch.search }
								searchResults={ parentSearchOptions }
								isSearching={ parentSearch.isSearching }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="comment_status"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Comment Status', 'fakerpress' ) }
							description={ __( 'Whether comments are open or closed on generated posts.', 'fakerpress' ) }
						>
							<ComboboxMulti
								value={ field.value }
								onChange={ field.onChange }
								options={ commentStatusOptions }
								placeholder={ __( 'Select comment status...', 'fakerpress' ) }
							/>
						</FormField>
					) }
				/>

				<Controller
					name="authors"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Author', 'fakerpress' ) }
							description={ __( 'Assign generated posts to these authors. Leave empty to use random users.', 'fakerpress' ) }
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
					name="use_html"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Use HTML', 'fakerpress' ) }
							description={ __( 'When enabled, post content will include HTML markup.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Switch
									id="use_html"
									checked={ field.value }
									onCheckedChange={ field.onChange }
								/>
								<Label htmlFor="use_html" className="fp:text-sm">
									{ __( 'Enable HTML in post content', 'fakerpress' ) }
								</Label>
							</div>
						</FormField>
					) }
				/>

				<Controller
					name="content_size"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Content Size', 'fakerpress' ) }
							description={ __( 'Number of paragraphs in post content.', 'fakerpress' ) }
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

				{ useHtml && (
					<Controller
						name="html_tags"
						control={ control }
						render={ ( { field } ) => (
							<FormField
								label={ __( 'HTML Tags', 'fakerpress' ) }
								description={ __( 'HTML elements that can appear in generated post content.', 'fakerpress' ) }
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

				{ useHtml && (
					<Controller
						name="image_providers"
						control={ control }
						render={ ( { field } ) => (
							<FormField
								label={ __( 'Image Providers', 'fakerpress' ) }
								description={ __( 'Providers for images embedded in HTML content.', 'fakerpress' ) }
							>
								<ComboboxMulti
									value={ field.value }
									onChange={ field.onChange }
									options={ imageProviderOptions }
									placeholder={ __( 'Select image providers...', 'fakerpress' ) }
								/>
							</FormField>
						) }
					/>
				) }

				<Controller
					name="excerpt_size"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Excerpt Size', 'fakerpress' ) }
							description={ __( 'Number of sentences in the post excerpt.', 'fakerpress' ) }
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
					name="taxonomy_rules"
					control={ control }
					render={ ( { field } ) => (
						<TaxonomyFieldRules
							value={ field.value }
							onChange={ field.onChange }
							taxonomyOptions={ taxonomyOptions }
							onSearchTerms={ ( query ) => termSearch.search( query ) }
							termSearchResults={ termSearchOptions }
							isSearchingTerms={ termSearch.isSearching }
							description={ __( 'Assign terms from taxonomies to generated posts.', 'fakerpress' ) }
						/>
					) }
				/>

				<Controller
					name="meta"
					control={ control }
					render={ ( { field } ) => (
						<MetaFieldRules
							value={ field.value }
							onChange={ field.onChange }
							description={ __( 'Use the fields below to configure a set of rules for your generated Posts.', 'fakerpress' ) }
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
