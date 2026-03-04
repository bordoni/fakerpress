import { useMemo } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { __ } from '@wordpress/i18n';
import { usePageConfig } from '../hooks/use-page-config';
import { useGenerateModule } from '../hooks/use-generate-module';
import { transformCommentsForm } from '../lib/transform';
import { PageLayout } from '../components/layout/page-layout';
import { AdminNotice } from '../components/layout/admin-notice';
import { FormField } from '../components/form/form-field';
import { RangeInput } from '../components/form/range-input';
import { ComboboxMulti, type ComboboxOption } from '../components/form/combobox-multi';
import { DateRangeField } from '../components/form/date-range-field';
import { MetaFieldRules } from '../components/form/meta-field-rules';
import { GenerateButton } from '../components/form/generate-button';
import { Switch } from '../components/ui/switch';
import { Label } from '../components/ui/label';
import type { MetaRule } from '../lib/types';

interface CommentsFormData {
	type: string[];
	post_type: string[];
	qty: { min: number | undefined; max: number | undefined };
	date: { preset: string; start: string; end: string };
	content_size: { min: number | undefined; max: number | undefined };
	use_html: boolean;
	html_tags: string[];
	meta: MetaRule[];
}

export default function CommentsPage() {
	const { data } = usePageConfig();
	const { generate, isGenerating, progress, results, error, reset } = useGenerateModule( 'comments' );

	const commentTypeOptions: ComboboxOption[] = useMemo( () => {
		const types = ( data.comment_types || [ 'default' ] ) as string[];
		return types.map( ( t ) => ( { value: t, label: t } ) );
	}, [ data.comment_types ] );

	const postTypeOptions: ComboboxOption[] = useMemo( () => {
		const postTypes = ( data.post_types || {} ) as Record< string, { name: string; label: string } >;
		return Object.values( postTypes ).map( ( pt ) => ( { value: pt.name, label: pt.label } ) );
	}, [ data.post_types ] );

	const htmlTagOptions: ComboboxOption[] = useMemo( () => {
		const tags = ( data.html_tags || [] ) as string[];
		return tags.map( ( tag ) => ( { value: tag, label: tag } ) );
	}, [ data.html_tags ] );

	const { control, handleSubmit, watch } = useForm< CommentsFormData >( {
		defaultValues: {
			type: [ 'default' ],
			post_type: [ 'post' ],
			qty: { min: 3, max: 12 },
			date: { preset: '', start: '', end: '' },
			content_size: { min: 1, max: 5 },
			use_html: true,
			html_tags: ( data.html_tags || [] ) as string[],
			meta: [],
		},
	} );

	const useHtml = watch( 'use_html' );

	const onSubmit = ( formData: CommentsFormData ) => {
		reset();
		generate( transformCommentsForm( formData ) );
	};

	return (
		<PageLayout title={ __( 'Generate Comments', 'fakerpress' ) }>
			{ results && (
				<AdminNotice
					type="success"
					title={ __( 'Success', 'fakerpress' ) }
					message={ `Generated ${ results.generated } comments in ${ results.time.toFixed( 2 ) }s.` }
				/>
			) }
			{ error && (
				<AdminNotice type="error" title={ __( 'Error', 'fakerpress' ) } message={ error } />
			) }

			<form onSubmit={ handleSubmit( onSubmit ) }>
				<Controller
					name="type"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Type', 'fakerpress' ) }
							description={ __( 'Select the type of comments to generate.', 'fakerpress' ) }
						>
							<ComboboxMulti
								value={ field.value }
								onChange={ field.onChange }
								options={ commentTypeOptions }
								placeholder={ __( 'Select types...', 'fakerpress' ) }
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
							description={ __( 'Generate comments on posts of these types.', 'fakerpress' ) }
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
					name="qty"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Quantity', 'fakerpress' ) }
							description={ __( 'How many comments to generate. Use both fields to randomize within the given range.', 'fakerpress' ) }
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
							description={ __( 'Choose the range for the comment dates.', 'fakerpress' ) }
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
					name="content_size"
					control={ control }
					render={ ( { field } ) => (
						<FormField
							label={ __( 'Content Size', 'fakerpress' ) }
							description={ __( 'Number of sentences in the comment content.', 'fakerpress' ) }
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
							description={ __( 'When enabled, comment content will include HTML markup.', 'fakerpress' ) }
						>
							<div className="fp:flex fp:items-center fp:gap-2">
								<Switch id="use_html" checked={ field.value } onCheckedChange={ field.onChange } />
								<Label htmlFor="use_html" className="fp:text-sm">
									{ __( 'Enable HTML in content', 'fakerpress' ) }
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
								description={ __( 'HTML elements that can appear in generated comment content.', 'fakerpress' ) }
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
							description={ __( 'Use the fields below to configure a set of rules for your generated Comments.', 'fakerpress' ) }
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
