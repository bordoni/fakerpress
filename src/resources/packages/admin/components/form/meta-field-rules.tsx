import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '../ui/accordion';
import { Plus, Minus } from 'lucide-react';
import type { MetaRule } from '../../lib/types';

const META_TYPES: { value: string; label: string }[] = [
	{ value: 'number', label: 'Number' },
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
	{ value: 'date', label: 'Date' },
	{ value: 'timezone', label: 'TimeZone' },
	{ value: 'email', label: 'Email' },
	{ value: 'domain', label: 'Domain' },
	{ value: 'ip', label: 'IP' },
	{ value: 'user_agent', label: 'UserAgent' },
];

interface MetaFieldRulesProps {
	value: MetaRule[];
	onChange: ( rules: MetaRule[] ) => void;
}

export function MetaFieldRules( { value, onChange }: MetaFieldRulesProps ) {
	const addRule = useCallback( () => {
		onChange( [ ...value, { type: 'text', name: '', config: {} } ] );
	}, [ value, onChange ] );

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
		<div className="fp-space-y-2">
			{ value.length > 0 && (
				<Accordion type="multiple" className="fp-w-full">
					{ value.map( ( rule, index ) => (
						<AccordionItem key={ index } value={ `rule-${ index }` }>
							<AccordionTrigger className="fp-text-sm">
								<span className="fp-flex fp-items-center fp-gap-2">
									<span className="fp-inline-flex fp-items-center fp-justify-center fp-w-6 fp-h-6 fp-rounded-full fp-bg-muted fp-text-xs fp-font-medium">
										{ index + 1 }
									</span>
									{ rule.name || 'Untitled Rule' }
									<span className="fp-text-muted-foreground">({ rule.type })</span>
								</span>
							</AccordionTrigger>
							<AccordionContent>
								<div className="fp-space-y-3 fp-pt-2">
									<div className="fp-grid fp-grid-cols-2 fp-gap-3">
										<div className="fp-space-y-1">
											<label className="fp-text-xs fp-font-medium">Type</label>
											<Select
												value={ rule.type }
												onValueChange={ ( newType ) =>
													updateRule( index, { type: newType, config: {} } )
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
										</div>
										<div className="fp-space-y-1">
											<label className="fp-text-xs fp-font-medium">Name</label>
											<Input
												value={ rule.name }
												onChange={ ( e ) =>
													updateRule( index, { name: e.target.value } )
												}
												placeholder="meta_key_name"
											/>
										</div>
									</div>

									<MetaTypeConfig
										type={ rule.type }
										config={ rule.config }
										onConfigChange={ ( key, val ) =>
											updateRuleConfig( index, key, val )
										}
									/>

									<div className="fp-flex fp-justify-end">
										<Button
											variant="ghost"
											size="xs"
											onClick={ () => removeRule( index ) }
											className="fp-text-destructive hover:fp-text-destructive"
										>
											<Minus className="fp-h-3 fp-w-3" />
											Remove
										</Button>
									</div>
								</div>
							</AccordionContent>
						</AccordionItem>
					) ) }
				</Accordion>
			) }

			<Button variant="outline" size="sm" onClick={ addRule }>
				<Plus className="fp-h-3 fp-w-3" />
				Add Meta Rule
			</Button>
		</div>
	);
}

/**
 * Dynamic configuration panel per meta type.
 */
function MetaTypeConfig( {
	type,
	config,
	onConfigChange,
}: {
	type: string;
	config: Record< string, unknown >;
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	switch ( type ) {
		case 'number':
			return (
				<div className="fp-grid fp-grid-cols-2 fp-gap-3">
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Min</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 0 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Max</label>
						<Input
							type="number"
							value={ ( config.max as number ) ?? 100 }
							onChange={ ( e ) => onConfigChange( 'max', Number( e.target.value ) ) }
						/>
					</div>
				</div>
			);

		case 'text':
		case 'words':
		case 'letter':
			return (
				<div className="fp-grid fp-grid-cols-2 fp-gap-3">
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Min Length</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 1 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Max Length</label>
						<Input
							type="number"
							value={ ( config.max as number ) ?? 5 }
							onChange={ ( e ) => onConfigChange( 'max', Number( e.target.value ) ) }
						/>
					</div>
				</div>
			);

		case 'lexify':
		case 'asciify':
		case 'regexify':
			return (
				<div className="fp-space-y-1">
					<label className="fp-text-xs fp-font-medium">Pattern</label>
					<Input
						value={ ( config.pattern as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'pattern', e.target.value ) }
						placeholder={ type === 'regexify' ? '/[A-Z]{3}/' : '???###' }
					/>
				</div>
			);

		case 'html':
			return (
				<div className="fp-grid fp-grid-cols-2 fp-gap-3">
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Min Elements</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 1 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Max Elements</label>
						<Input
							type="number"
							value={ ( config.max as number ) ?? 5 }
							onChange={ ( e ) => onConfigChange( 'max', Number( e.target.value ) ) }
						/>
					</div>
				</div>
			);

		case 'date':
			return (
				<div className="fp-grid fp-grid-cols-2 fp-gap-3">
					<div className="fp-space-y-1">
						<label className="fp-text-xs fp-font-medium">Format</label>
						<Input
							value={ ( config.format as string ) ?? 'Y-m-d H:i:s' }
							onChange={ ( e ) => onConfigChange( 'format', e.target.value ) }
							placeholder="Y-m-d H:i:s"
						/>
					</div>
				</div>
			);

		case 'elements':
			return (
				<div className="fp-space-y-1">
					<label className="fp-text-xs fp-font-medium">Elements (comma-separated)</label>
					<Input
						value={ ( config.elements as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'elements', e.target.value ) }
						placeholder="foo, bar, baz"
					/>
				</div>
			);

		default:
			return null;
	}
}
