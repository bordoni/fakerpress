import { useCallback, type ReactNode } from 'react';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { ComboboxMulti, type ComboboxOption } from './combobox-multi';
import { DateRangeField } from './date-range-field';
import { RangeInput } from './range-input';
import { RuleCard, RuleConfigItem, RuleField, RuleSection } from './rule-card';
import type { MetaRule } from '../../lib/types';

/**
 * Fallback image providers for the attachment meta type, used when a page does
 * not pass its own provider list. Mirrors the values exposed by Post_View.
 */
const DEFAULT_PROVIDER_OPTIONS: ComboboxOption[] = [
	{ value: 'placeholder', label: 'Placehold.co' },
	{ value: 'lorempicsum', label: 'Lorem Picsum' },
];

/** Default weight for a new meta rule, as in the stable generator. */
export const DEFAULT_META_WEIGHT = 90;

/**
 * A blank rule, as the stable generator shows after "+" or after clearing the
 * last rule. Rules without a type or name are dropped when the form is submitted.
 */
export const EMPTY_META_RULE: MetaRule = {
	type: '',
	name: '',
	weight: DEFAULT_META_WEIGHT,
	config: {},
};

/** Whether a rule has nothing chosen yet (so clearing it would change nothing). */
export function isEmptyMetaRule( rule: MetaRule ): boolean {
	return ! rule.type && ! rule.name;
}

/** HTML tags offered for the html meta type: headers, lists, blocks and self-closing tags. */
const HTML_TAG_DEFAULT = [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'div', 'p', 'blockquote', 'img', 'hr', '!--more--' ];

/** Template tags for the person, geo and company types. Values are joined with `|`. */
const PERSON_TAGS: ComboboxOption[] = [
	{ label: 'Title', value: '{% title %}' },
	{ label: 'First Name', value: '{% first_name %}' },
	{ label: 'Last Name', value: '{% last_name %}' },
	{ label: 'Suffix', value: '{% suffix %}' },
];

const GEO_TAGS: ComboboxOption[] = [
	{ label: 'Country', value: '{% country %}' },
	{ label: 'Country Code (e.g.: US)', value: '{% country_code %}' },
	{ label: 'Country ABBR (e.g.: USA)', value: '{% country_abbr %}' },
	{ label: 'City Prefix', value: '{% city_prefix %}' },
	{ label: 'City Suffix', value: '{% city_suffix %}' },
	{ label: 'City', value: '{% city %}' },
	{ label: 'State', value: '{% state %}' },
	{ label: 'State Abbr', value: '{% state_abbr %}' },
	{ label: 'Address', value: '{% address %}' },
	{ label: 'Secondary Address', value: '{% secondary_address %}' },
	{ label: 'Building Number', value: '{% building_number %}' },
	{ label: 'Street Name', value: '{% street_name %}' },
	{ label: 'Street Address', value: '{% street_address %}' },
	{ label: 'Postal Code', value: '{% postalcode %}' },
	{ label: 'Latitude', value: '{% latitude %}' },
	{ label: 'Longitude', value: '{% longitude %}' },
];

const COMPANY_TAGS: ComboboxOption[] = [
	{ label: 'Catch Phrase', value: '{% catch_phrase %}' },
	{ label: 'BS', value: '{% bs %}' },
	{ label: 'Company', value: '{% company %}' },
	{ label: 'Suffix', value: '{% suffix %}' },
	{ label: 'Space', value: ' ' },
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
			return { elements: HTML_TAG_DEFAULT.join( ',' ), qty: [ 1, 6 ] };
		case 'lexify':
		case 'asciify':
		case 'regexify':
			return { template: '' };
		case 'person':
			return { template: '{% title %}|{% first_name %}|{% last_name %}|{% suffix %}', gender: 'female' };
		case 'geo':
			return { template: '{% latitude %}|,|{% longitude %}' };
		case 'company':
			return { template: '{% company %}| |{% suffix %}' };
		case 'date':
			return { interval: { name: '', min: '', max: '' }, format: 'Y-m-d H:i:s' };
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
	{ value: 'geo', label: 'Geo Information' },
	{ value: 'company', label: 'Company' },
	{ value: 'date', label: 'Date' },
	{ value: 'timezone', label: 'TimeZone' },
	{ value: 'email', label: 'Email' },
	{ value: 'domain', label: 'Domain' },
	{ value: 'ip', label: 'IP' },
	{ value: 'user_agent', label: 'Browser User Agent' },
];

/** Example templates (placeholder and inline label) for the pattern-based meta types. */
const TEMPLATE_EXAMPLES: Record< string, { placeholder: string; example: string; output: string } > = {
	lexify: { placeholder: 'E.g.: John ##??', example: 'John ##??', output: 'John 29ze' },
	asciify: { placeholder: 'E.g.: John ****', example: 'John ****', output: 'John r9"+' },
	regexify: {
		placeholder: 'E.g.: [A-Z0-9._%+-]+@[A-Z0-9.-]',
		example: '[A-Z0-9._%+-]+@[A-Z0-9.-]',
		output: 'sm0@y8k96a',
	},
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
	// Like the stable generator, there is always at least one rule on screen.
	const rules = value.length > 0 ? value : [ EMPTY_META_RULE ];

	const addRule = useCallback( () => {
		onChange( [ ...rules, { ...EMPTY_META_RULE } ] );
	}, [ rules, onChange ] );

	// Removing the only rule clears it instead.
	const removeRule = useCallback(
		( index: number ) => {
			onChange( rules.length === 1 ? [] : rules.filter( ( _, i ) => i !== index ) );
		},
		[ rules, onChange ]
	);

	const updateRule = useCallback(
		( index: number, updates: Partial< MetaRule > ) => {
			const newRules = [ ...rules ];
			newRules[ index ] = { ...newRules[ index ], ...updates };
			onChange( newRules );
		},
		[ rules, onChange ]
	);

	const updateRuleConfig = useCallback(
		( index: number, key: string, configValue: unknown ) => {
			const newRules = [ ...rules ];
			newRules[ index ] = {
				...newRules[ index ],
				config: { ...newRules[ index ].config, [ key ]: configValue },
			};
			onChange( newRules );
		},
		[ rules, onChange ]
	);

	return (
		<RuleSection title={ title } description={ description }>
			{ rules.map( ( rule, index ) => (
				<RuleCard
					key={ index }
					index={ index }
					onRemove={ () => removeRule( index ) }
					onAdd={ addRule }
					removeDisabled={ rules.length === 1 && isEmptyMetaRule( rule ) }
				>
					<RuleField label="Type">
						<Select
							value={ rule.type }
							onValueChange={ ( newType ) =>
								updateRule( index, {
									type: newType,
									weight: rule.weight ?? DEFAULT_META_WEIGHT,
									config: defaultConfigForType( newType, providerOptions ),
								} )
							}
						>
							<SelectTrigger className="fp:w-[200px]" aria-label="Type">
								<SelectValue placeholder="Select a Field type" />
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
							placeholder="Newborn Meta needs a Name, E.g.: _new_image"
							aria-label="Name"
							className="fp:w-full fp:max-w-[380px]"
						/>
					</RuleField>

					{ rule.type && (
						<RuleField label="Configuration">
							<MetaTypeConfig
								type={ rule.type }
								config={ rule.config }
								providerOptions={ providerOptions }
								onConfigChange={ ( key, val ) => updateRuleConfig( index, key, val ) }
								weightField={
									<RuleConfigItem label="Weight">
										<Input
											type="number"
											value={ rule.weight ?? '' }
											onChange={ ( e ) =>
												updateRule( index, {
													weight: e.target.value === '' ? undefined : Number( e.target.value ),
												} )
											}
											placeholder="E.g.: 55"
											aria-label="Weight"
											min={ 0 }
											max={ 100 }
											className="fp:w-[84px]"
										/>
									</RuleConfigItem>
								}
							/>
						</RuleField>
					) }
				</RuleCard>
			) ) }
		</RuleSection>
	);
}

/**
 * The "Configuration" controls for one meta type, in the stable generator's
 * order. Weight comes last for every type except attachment, where it sits
 * between the providers and the image sizes.
 */
function MetaTypeConfig( {
	type,
	config,
	providerOptions,
	onConfigChange,
	weightField,
}: {
	type: string;
	config: Record< string, unknown >;
	providerOptions: ComboboxOption[];
	onConfigChange: ( key: string, value: unknown ) => void;
	weightField: ReactNode;
} ) {
	const text = ( key: string, fallback = '' ) => ( config[ key ] as string | undefined ) ?? fallback;
	const list = ( key: string, separator: string ) =>
		text( key )
			.split( separator )
			.filter( ( v ) => v !== '' );

	switch ( type ) {
		case 'attachment':
			return (
				<>
					<RuleConfigItem label="Stored Data">
						<Select value={ text( 'store', 'id' ) } onValueChange={ ( val ) => onConfigChange( 'store', val ) }>
							<SelectTrigger className="fp:w-[200px]" aria-label="Stored Data">
								<SelectValue placeholder="Which value should be saved on the Meta" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="id">Attachment ID</SelectItem>
								<SelectItem value="url">Attachment URL</SelectItem>
							</SelectContent>
						</Select>
					</RuleConfigItem>
					<RuleConfigItem label="Which image services will the generator use?" labelBelow>
						<div className="fp:w-full fp:max-w-[420px]">
							<ComboboxMulti
								value={ list( 'providers', ',' ).map( ( p ) => p.trim() ) }
								onChange={ ( vals ) => onConfigChange( 'providers', vals.join( ',' ) ) }
								options={ providerOptions }
								placeholder="Select at least one Provider"
							/>
						</div>
					</RuleConfigItem>
					{ weightField }
					<RangeConfigItem
						label="Range of possible width sizes for the image"
						config={ config }
						configKey="width"
						minPlaceholder="e.g.: 350"
						maxPlaceholder="e.g.: 900"
						onConfigChange={ onConfigChange }
					/>
					<RangeConfigItem
						label="Range of possible height sizes for the image"
						config={ config }
						configKey="height"
						minPlaceholder="e.g.: 125"
						maxPlaceholder="e.g.: 650"
						onConfigChange={ onConfigChange }
					/>
				</>
			);

		case 'numbers':
			return (
				<>
					<RangeConfigItem
						label="Range of possible numbers"
						config={ config }
						configKey="number"
						onConfigChange={ onConfigChange }
					/>
					{ weightField }
				</>
			);

		case 'wp_query':
			return (
				<>
					<RuleConfigItem
						label={
							<>
								Uses{ ' ' }
								<a
									href="http://codex.wordpress.org/Class_Reference/WP_Query"
									target="_blank"
									rel="noreferrer"
									className="fp:underline"
								>
									WP_Query
								</a>
							</>
						}
					>
						<Input
							value={ text( 'query' ) }
							onChange={ ( e ) => onConfigChange( 'query', e.target.value ) }
							placeholder="category=2&posts_per_page=10"
							aria-label="WP_Query"
							className="fp:w-full fp:max-w-[490px]"
						/>
					</RuleConfigItem>
					{ weightField }
				</>
			);

		case 'elements':
			return (
				<>
					<RuleConfigItem>
						<div className="fp:w-full fp:max-w-[420px]">
							<ComboboxMulti
								value={ list( 'elements', ',' ) }
								onChange={ ( vals ) => onConfigChange( 'elements', vals.join( ',' ) ) }
								placeholder="Type all possible elements (Tab or Return)"
								allowCreate
							/>
						</div>
					</RuleConfigItem>
					<RangeConfigItem label="Quantity" config={ config } configKey="qty" onConfigChange={ onConfigChange } />
					<RuleConfigItem label="Separator">
						<Input
							value={ text( 'separator', ',' ) }
							onChange={ ( e ) => onConfigChange( 'separator', e.target.value ) }
							placeholder="E.g.: , "
							aria-label="Separator"
							className="fp:w-[84px]"
						/>
					</RuleConfigItem>
					{ weightField }
				</>
			);

		case 'words':
			return (
				<>
					<RangeConfigItem label="Quantity" config={ config } configKey="qty" onConfigChange={ onConfigChange } />
					{ weightField }
				</>
			);

		case 'text':
			return (
				<>
					<RuleConfigItem>
						<Select
							value={ text( 'text_type', 'paragraphs' ) }
							onValueChange={ ( val ) => onConfigChange( 'text_type', val ) }
						>
							<SelectTrigger className="fp:w-[200px]" aria-label="Text type">
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="sentences">Sentences</SelectItem>
								<SelectItem value="paragraphs">Paragraphs</SelectItem>
							</SelectContent>
						</Select>
					</RuleConfigItem>
					<RangeConfigItem label="Quantity" config={ config } configKey="qty" onConfigChange={ onConfigChange } />
					<RuleConfigItem
						label={
							<>
								Separator <b>&mdash;</b> New Line: &quot;\n&quot;
							</>
						}
					>
						<Input
							value={ text( 'separator', '\\n' ) }
							onChange={ ( e ) => onConfigChange( 'separator', e.target.value ) }
							placeholder="E.g.: , "
							aria-label="Separator"
							className="fp:w-[84px]"
						/>
					</RuleConfigItem>
					{ weightField }
				</>
			);

		case 'html':
			return (
				<>
					<RuleConfigItem label="HTML Tags, without < or >" labelBelow>
						<div className="fp:w-full fp:max-w-[420px]">
							<ComboboxMulti
								value={ list( 'elements', ',' ) }
								onChange={ ( vals ) => onConfigChange( 'elements', vals.join( ',' ) ) }
								options={ HTML_TAG_DEFAULT.map( ( tag ) => ( { value: tag, label: tag } ) ) }
								placeholder="HTML Tags"
								allowCreate
							/>
						</div>
					</RuleConfigItem>
					<RangeConfigItem label="Quantity" config={ config } configKey="qty" onConfigChange={ onConfigChange } />
					{ weightField }
				</>
			);

		case 'lexify':
		case 'asciify':
		case 'regexify': {
			const example = TEMPLATE_EXAMPLES[ type ];
			return (
				<>
					<RuleConfigItem
						label={
							<>
								{ example.example } <b>&raquo;</b> { example.output }
							</>
						}
					>
						<Input
							value={ text( 'template' ) }
							onChange={ ( e ) => onConfigChange( 'template', e.target.value ) }
							placeholder={ example.placeholder }
							aria-label="Template"
							className="fp:w-full fp:max-w-[350px]"
						/>
					</RuleConfigItem>
					{ weightField }
				</>
			);
		}

		case 'person':
			return (
				<>
					<TemplateConfigItem label="Name Template" options={ PERSON_TAGS } config={ config } onConfigChange={ onConfigChange } />
					<RuleConfigItem>
						<Select value={ text( 'gender', 'female' ) } onValueChange={ ( val ) => onConfigChange( 'gender', val ) }>
							<SelectTrigger className="fp:w-[200px]" aria-label="Gender">
								<SelectValue />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="male">Male</SelectItem>
								<SelectItem value="female">Female</SelectItem>
							</SelectContent>
						</Select>
					</RuleConfigItem>
					{ weightField }
				</>
			);

		case 'geo':
			return (
				<>
					<TemplateConfigItem label="Address Format" options={ GEO_TAGS } config={ config } onConfigChange={ onConfigChange } />
					{ weightField }
				</>
			);

		case 'company':
			return (
				<>
					<TemplateConfigItem label="Company Format" options={ COMPANY_TAGS } config={ config } onConfigChange={ onConfigChange } />
					{ weightField }
				</>
			);

		case 'date': {
			const interval = ( config.interval as { name?: string; min?: string; max?: string } | undefined ) ?? {};
			return (
				<>
					<RuleConfigItem>
						<DateRangeField
							value={ { preset: interval.name ?? '', start: interval.min ?? '', end: interval.max ?? '' } }
							onChange={ ( range ) =>
								onConfigChange( 'interval', { name: range.preset, min: range.start, max: range.end } )
							}
						/>
					</RuleConfigItem>
					<RuleConfigItem
						label={
							<>
								Date Format <b>&mdash;</b> See{ ' ' }
								<a
									href="http://php.net/manual/function.date.php"
									target="_blank"
									rel="noreferrer"
									className="fp:underline"
								>
									PHP Date
								</a>
							</>
						}
					>
						<Input
							value={ text( 'format', 'Y-m-d H:i:s' ) }
							onChange={ ( e ) => onConfigChange( 'format', e.target.value ) }
							aria-label="Date Format"
							className="fp:w-[200px]"
						/>
					</RuleConfigItem>
					{ weightField }
				</>
			);
		}

		default:
			// letter, timezone, email, domain, ip, user_agent: only weight applies.
			return <>{ weightField }</>;
	}
}

/**
 * A template built from tags (person, geo, company), stored as one string
 * joined with `|`, the separator the backend splits on. Free text is allowed,
 * so literal pieces like "," can sit between tags.
 */
function TemplateConfigItem( {
	label,
	options,
	config,
	onConfigChange,
}: {
	label: string;
	options: ComboboxOption[];
	config: Record< string, unknown >;
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	const value = ( ( config.template as string | undefined ) ?? '' ).split( '|' ).filter( ( v ) => v !== '' );
	return (
		<RuleConfigItem label={ label } labelBelow>
			<div className="fp:w-full fp:max-w-[420px]">
				<ComboboxMulti
					value={ value }
					onChange={ ( vals ) => onConfigChange( 'template', vals.join( '|' ) ) }
					options={ options }
					placeholder={ label }
					allowCreate
				/>
			</div>
		</RuleConfigItem>
	);
}

/**
 * A min/max range stored as a `[min, max]` array under a single config key,
 * matching the positional argument the backend meta handlers expect.
 */
function RangeConfigItem( {
	label,
	config,
	configKey,
	minPlaceholder,
	maxPlaceholder,
	onConfigChange,
}: {
	label: string;
	config: Record< string, unknown >;
	configKey: string;
	minPlaceholder?: string;
	maxPlaceholder?: string;
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	const range = Array.isArray( config[ configKey ] )
		? ( config[ configKey ] as ( number | undefined )[] )
		: [];
	return (
		<RuleConfigItem label={ label }>
			<RangeInput
				minValue={ range[ 0 ] }
				maxValue={ range[ 1 ] }
				onMinChange={ ( min ) => onConfigChange( configKey, [ min, range[ 1 ] ] ) }
				onMaxChange={ ( max ) => onConfigChange( configKey, [ range[ 0 ], max ] ) }
				minPlaceholder={ minPlaceholder }
				maxPlaceholder={ maxPlaceholder }
				min={ 0 }
				showMax
			/>
		</RuleConfigItem>
	);
}
