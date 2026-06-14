import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { ComboboxMulti, type ComboboxOption } from './combobox-multi';
import { RangeInput } from './range-input';
import { RuleCard, RuleField } from './rule-card';
import type { MetaRule } from '../../lib/types';

/**
 * Fallback image providers for the attachment meta type, used when a page does
 * not pass its own provider list. Mirrors the values exposed by Post_View.
 */
const DEFAULT_PROVIDER_OPTIONS: ComboboxOption[] = [
	{ value: 'placeholder', label: 'Placehold.co' },
	{ value: 'lorempicsum', label: 'Lorem Picsum' },
];

/**
 * Build the initial config for a meta type so a freshly selected type is not
 * left with an empty (and therefore non-generating) config.
 */
function defaultConfigForType(
	type: string,
	providerOptions: ComboboxOption[]
): Record< string, unknown > {
	switch ( type ) {
		case 'numbers':
			return { number: [ 0, 9 ] };
		case 'wp_query':
			return { query: '' };
		case 'attachment':
			return {
				store: 'id',
				providers: providerOptions.map( ( o ) => o.value ).join( ',' ),
			};
		case 'elements':
			return { elements: '', qty: [ 1, 3 ], separator: ',' };
		case 'words':
			return { qty: [ 3, 8 ] };
		case 'text':
			return { text_type: 'paragraphs', qty: [ 1, 3 ], separator: '\\n' };
		case 'html':
			return { elements: HTML_TAG_DEFAULT, qty: [ 1, 6 ] };
		case 'lexify':
			return { template: '' };
		case 'asciify':
			return { template: '' };
		case 'regexify':
			return { template: '' };
		case 'person':
			return { template: '{% first_name %}|{% last_name %}', gender: 'female' };
		case 'geo':
			return { template: '{% city %}|{% state_abbr %}' };
		case 'company':
			return { template: '{% company %}' };
		default:
			// letter, timezone, email, domain, ip, user_agent need no config.
			return {};
	}
}

const META_TYPES: { value: string; label: string }[] = [
	{ value: 'numbers', label: 'Number' },
	{ value: 'wp_query', label: 'WP_Query' },
	{ value: 'attachment', label: 'Attachment' },
	{ value: 'elements', label: 'Elements' },
	{ value: 'letter', label: 'Letter' },
	{ value: 'words', label: 'Words' },
	{ value: 'text', label: 'Text' },
	{ value: 'html', label: 'HTML' },
	{ value: 'lexify', label: 'Lexify' },
	{ value: 'asciify', label: 'Asciify' },
	{ value: 'regexify', label: 'Regexify' },
	{ value: 'person', label: 'Person' },
	{ value: 'geo', label: 'Geo' },
	{ value: 'company', label: 'Company' },
	{ value: 'timezone', label: 'TimeZone' },
	{ value: 'email', label: 'Email' },
	{ value: 'domain', label: 'Domain' },
	{ value: 'ip', label: 'IP' },
	{ value: 'user_agent', label: 'UserAgent' },
];

/** Default HTML tags offered for the html meta type. */
const HTML_TAG_DEFAULT = 'h2,h3,p,ul,ol,blockquote,strong,em,a';

/** Example templates shown as placeholders for the pattern-based meta types. */
const TEMPLATE_PLACEHOLDERS: Record< string, string > = {
	lexify: 'John ##??',
	asciify: 'John ****',
	regexify: '[A-Z0-9._%+-]+@[A-Z0-9.-]',
};

interface MetaFieldRulesProps {
	value: MetaRule[];
	onChange: ( rules: MetaRule[] ) => void;
	providerOptions?: ComboboxOption[];
	title?: string;
	description?: string;
}

export function MetaFieldRules( {
	value,
	onChange,
	providerOptions = DEFAULT_PROVIDER_OPTIONS,
	title = 'Meta Field Rules',
	description,
}: MetaFieldRulesProps ) {
	const addRule = useCallback( () => {
		onChange( [
			...value,
			{
				type: 'text',
				name: '',
				weight: 100,
				config: defaultConfigForType( 'text', providerOptions ),
			},
		] );
	}, [ value, onChange, providerOptions ] );

	const removeRule = useCallback(
		( index: number ) => {
			onChange( value.filter( ( _, i ) => i !== index ) );
		},
		[ value, onChange ]
	);

	const updateRule = useCallback(
		( index: number, updates: Partial< MetaRule > ) => {
			const newRules = [ ...value ];
			newRules[ index ] = { ...newRules[ index ], ...updates };
			onChange( newRules );
		},
		[ value, onChange ]
	);

	const updateRuleConfig = useCallback(
		( index: number, key: string, configValue: unknown ) => {
			const newRules = [ ...value ];
			newRules[ index ] = {
				...newRules[ index ],
				config: { ...newRules[ index ].config, [ key ]: configValue },
			};
			onChange( newRules );
		},
		[ value, onChange ]
	);

	return (
		<div className="fp:mt-4">
			<h2 className="fp:text-base fp:font-normal fp:pb-2 fp:mb-3 fp:border-b fp:border-[#dfdfdf]">
				{ title }
			</h2>
			<div>
				{ description && (
					<p className="fp:text-sm fp:italic fp:text-[#555] fp:mb-3">{ description }</p>
				) }

				{ value.map( ( rule, index ) => (
					<RuleCard
						key={ index }
						index={ index }
						onRemove={ () => removeRule( index ) }
						onAdd={ addRule }
					>
						<RuleField label="Type">
							<Select
								value={ rule.type }
								onValueChange={ ( newType ) =>
									updateRule( index, {
										type: newType,
										config: defaultConfigForType( newType, providerOptions ),
									} )
								}
							>
								<SelectTrigger>
									<SelectValue />
								</SelectTrigger>
								<SelectContent>
									{ META_TYPES.map( ( mt ) => (
										<SelectItem key={ mt.value } value={ mt.value }>
											{ mt.label }
										</SelectItem>
									) ) }
								</SelectContent>
							</Select>
						</RuleField>

						<RuleField label="Name">
							<Input
								value={ rule.name }
								onChange={ ( e ) => updateRule( index, { name: e.target.value } ) }
								placeholder="meta_key_name"
							/>
						</RuleField>

						<MetaTypeConfig
							type={ rule.type }
							config={ rule.config }
							providerOptions={ providerOptions }
							onConfigChange={ ( key, val ) => updateRuleConfig( index, key, val ) }
						/>

						<RuleField label="Weight">
							<Input
								type="number"
								value={ rule.weight ?? 100 }
								onChange={ ( e ) =>
									updateRule( index, { weight: Number( e.target.value ) } )
								}
								min={ 0 }
								max={ 100 }
								className="fp:w-24"
							/>
						</RuleField>
					</RuleCard>
				) ) }

				<div className="fp:flex fp:justify-end">
					<Button type="button" variant="outline" size="sm" onClick={ addRule }>
						+ Add Meta Rule
					</Button>
				</div>
			</div>
		</div>
	);
}

/**
 * Dynamic configuration panel per meta type.
 */
function MetaTypeConfig( {
	type,
	config,
	providerOptions,
	onConfigChange,
}: {
	type: string;
	config: Record< string, unknown >;
	providerOptions: ComboboxOption[];
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	switch ( type ) {
		case 'attachment': {
			const providers = ( ( config.providers as string ) ?? '' )
				.split( ',' )
				.map( ( p ) => p.trim() )
				.filter( Boolean );
			return (
				<>
					<RuleField label="Stored Data">
						<Select
							value={ ( config.store as string ) ?? 'id' }
							onValueChange={ ( val ) => onConfigChange( 'store', val ) }
						>
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="id">Attachment ID</SelectItem>
								<SelectItem value="url">Attachment URL</SelectItem>
							</SelectContent>
						</Select>
					</RuleField>
					<RuleField
						label="Providers"
						description="Which image services will the generator use?"
					>
						<ComboboxMulti
							value={ providers }
							onChange={ ( vals ) => onConfigChange( 'providers', vals.join( ',' ) ) }
							options={ providerOptions }
							placeholder="Select image providers..."
						/>
					</RuleField>
					<RangeConfigField
						label="Width (px)"
						config={ config }
						configKey="width"
						onConfigChange={ onConfigChange }
					/>
					<RangeConfigField
						label="Height (px)"
						config={ config }
						configKey="height"
						onConfigChange={ onConfigChange }
					/>
				</>
			);
		}
		case 'numbers':
			return (
				<RangeConfigField
					label="Range of numbers"
					config={ config }
					configKey="number"
					onConfigChange={ onConfigChange }
				/>
			);

		case 'wp_query':
			return (
				<RuleField label="WP_Query">
					<Input
						value={ ( config.query as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'query', e.target.value ) }
						placeholder="category=2&posts_per_page=10"
					/>
				</RuleField>
			);

		case 'words':
			return (
				<RangeConfigField
					label="Quantity"
					config={ config }
					configKey="qty"
					onConfigChange={ onConfigChange }
				/>
			);

		case 'text':
			return (
				<>
					<RuleField label="Format">
						<Select
							value={ ( config.text_type as string ) ?? 'paragraphs' }
							onValueChange={ ( val ) => onConfigChange( 'text_type', val ) }
						>
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="sentences">Sentences</SelectItem>
								<SelectItem value="paragraphs">Paragraphs</SelectItem>
							</SelectContent>
						</Select>
					</RuleField>
					<RangeConfigField
						label="Quantity"
						config={ config }
						configKey="qty"
						onConfigChange={ onConfigChange }
					/>
					<RuleField label="Separator">
						<Input
							value={ ( config.separator as string ) ?? '\\n' }
							onChange={ ( e ) => onConfigChange( 'separator', e.target.value ) }
							className="fp:w-24"
						/>
					</RuleField>
				</>
			);

		case 'html':
			return (
				<>
					<RuleField label="HTML tags" description="Comma separated, without < or >.">
						<Input
							value={ ( config.elements as string ) ?? '' }
							onChange={ ( e ) => onConfigChange( 'elements', e.target.value ) }
							placeholder="h2,h3,p,ul,ol"
						/>
					</RuleField>
					<RangeConfigField
						label="Quantity"
						config={ config }
						configKey="qty"
						onConfigChange={ onConfigChange }
					/>
				</>
			);

		case 'lexify':
		case 'asciify':
		case 'regexify':
			return (
				<RuleField label="Template">
					<Input
						value={ ( config.template as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'template', e.target.value ) }
						placeholder={ TEMPLATE_PLACEHOLDERS[ type ] }
					/>
				</RuleField>
			);

		case 'elements':
			return (
				<>
					<RuleField label="Elements" description="Comma separated.">
						<Input
							value={ ( config.elements as string ) ?? '' }
							onChange={ ( e ) => onConfigChange( 'elements', e.target.value ) }
							placeholder="Type all possible elements"
						/>
					</RuleField>
					<RangeConfigField
						label="Quantity"
						config={ config }
						configKey="qty"
						onConfigChange={ onConfigChange }
					/>
					<RuleField label="Separator">
						<Input
							value={ ( config.separator as string ) ?? ',' }
							onChange={ ( e ) => onConfigChange( 'separator', e.target.value ) }
							className="fp:w-16"
						/>
					</RuleField>
				</>
			);

		case 'person':
			return (
				<>
					<RuleField label="Name Template">
						<Input
							value={ ( config.template as string ) ?? '' }
							onChange={ ( e ) => onConfigChange( 'template', e.target.value ) }
							placeholder="{% first_name %}|{% last_name %}"
						/>
					</RuleField>
					<RuleField label="Gender">
						<Select
							value={ ( config.gender as string ) ?? 'female' }
							onValueChange={ ( val ) => onConfigChange( 'gender', val ) }
						>
							<SelectTrigger>
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="female">Female</SelectItem>
								<SelectItem value="male">Male</SelectItem>
							</SelectContent>
						</Select>
					</RuleField>
				</>
			);

		case 'geo':
			return (
				<RuleField label="Geo Template">
					<Input
						value={ ( config.template as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'template', e.target.value ) }
						placeholder="{% city %}|{% state_abbr %}"
					/>
				</RuleField>
			);

		case 'company':
			return (
				<RuleField label="Company Template">
					<Input
						value={ ( config.template as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'template', e.target.value ) }
						placeholder="{% company %}"
					/>
				</RuleField>
			);

		default:
			// letter, timezone, email, domain, ip, user_agent: only weight applies.
			return null;
	}
}

/**
 * A min/max range stored as a `[min, max]` array under a single config key,
 * matching the positional argument the backend meta handlers expect.
 */
function RangeConfigField( {
	label,
	description,
	config,
	configKey,
	onConfigChange,
}: {
	label: string;
	description?: string;
	config: Record< string, unknown >;
	configKey: string;
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	const range = Array.isArray( config[ configKey ] )
		? ( config[ configKey ] as ( number | undefined )[] )
		: [];
	return (
		<RuleField label={ label } description={ description }>
			<RangeInput
				minValue={ range[ 0 ] }
				maxValue={ range[ 1 ] }
				onMinChange={ ( min ) => onConfigChange( configKey, [ min, range[ 1 ] ] ) }
				onMaxChange={ ( max ) => onConfigChange( configKey, [ range[ 0 ], max ] ) }
				min={ 0 }
			/>
		</RuleField>
	);
}
