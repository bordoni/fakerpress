import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
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
	title?: string;
	description?: string;
}

export function MetaFieldRules( {
	value,
	onChange,
	title = 'Meta Field Rules',
	description,
}: MetaFieldRulesProps ) {
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
		<div className="fp:border fp:border-[#dfdfdf] fp:mt-4">
			<h2 className="fp:text-base fp:font-normal fp:px-3 fp:py-2 fp:bg-[#f0f0f0] fp:border-b fp:border-[#dfdfdf]">
				{ title }
			</h2>
			<div className="fp:p-3">
				{ description && (
					<p className="fp:text-sm fp:italic fp:text-[#555] fp:mb-3">{ description }</p>
				) }

				{ value.map( ( rule, index ) => (
					<div key={ index } className="fp:flex fp:border fp:border-[#ededed] fp:mb-2">
						<div className="fp:w-10 fp:flex fp:items-start fp:justify-center fp:pt-3 fp:border-r fp:border-[#ededed] fp:bg-[#f9f9f9] fp:text-sm fp:font-medium">
							{ index + 1 }
						</div>

						<div className="fp:flex-1 fp:p-3 fp:space-y-2">
							<div className="fp:grid fp:grid-cols-2 fp:gap-3">
								<div className="fp:space-y-1">
									<label className="fp:text-xs fp:font-medium">Type</label>
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
								<div className="fp:space-y-1">
									<label className="fp:text-xs fp:font-medium">Name</label>
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
						</div>

						<div className="fp:flex fp:flex-col fp:border-l fp:border-[#ededed]">
							<Button
								type="button"
								variant="outline"
								size="icon-sm"
								onClick={ () => removeRule( index ) }
								title="Remove rule"
							>
								−
							</Button>
							<Button
								type="button"
								variant="outline"
								size="icon-sm"
								onClick={ addRule }
								title="Add rule"
							>
								+
							</Button>
						</div>
					</div>
				) ) }

				<Button type="button" variant="outline" size="sm" onClick={ addRule }>
					+ Add Meta Rule
				</Button>
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
	onConfigChange,
}: {
	type: string;
	config: Record< string, unknown >;
	onConfigChange: ( key: string, value: unknown ) => void;
} ) {
	switch ( type ) {
		case 'number':
			return (
				<div className="fp:grid fp:grid-cols-2 fp:gap-3">
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Min</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 0 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Max</label>
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
				<div className="fp:grid fp:grid-cols-2 fp:gap-3">
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Min Length</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 1 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Max Length</label>
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
				<div className="fp:space-y-1">
					<label className="fp:text-xs fp:font-medium">Pattern</label>
					<Input
						value={ ( config.pattern as string ) ?? '' }
						onChange={ ( e ) => onConfigChange( 'pattern', e.target.value ) }
						placeholder={ type === 'regexify' ? '/[A-Z]{3}/' : '???###' }
					/>
				</div>
			);

		case 'html':
			return (
				<div className="fp:grid fp:grid-cols-2 fp:gap-3">
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Min Elements</label>
						<Input
							type="number"
							value={ ( config.min as number ) ?? 1 }
							onChange={ ( e ) => onConfigChange( 'min', Number( e.target.value ) ) }
						/>
					</div>
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Max Elements</label>
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
				<div className="fp:grid fp:grid-cols-2 fp:gap-3">
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Format</label>
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
				<div className="fp:space-y-2">
					<div className="fp:space-y-1">
						<label className="fp:text-xs fp:font-medium">Elements</label>
						<Input
							value={ ( config.elements as string ) ?? '' }
							onChange={ ( e ) => onConfigChange( 'elements', e.target.value ) }
							placeholder="Type all possible elements (Tab or Return)"
						/>
					</div>
					<div className="fp:grid fp:grid-cols-2 fp:gap-3">
						<div className="fp:space-y-1">
							<label className="fp:text-xs fp:font-medium">Quantity Min</label>
							<Input
								type="number"
								value={ ( config.qty_min as number ) ?? 1 }
								onChange={ ( e ) =>
									onConfigChange( 'qty_min', Number( e.target.value ) )
								}
								placeholder="e.g.: 3"
							/>
						</div>
						<div className="fp:space-y-1">
							<label className="fp:text-xs fp:font-medium">Quantity Max</label>
							<Input
								type="number"
								value={ ( config.qty_max as number ) ?? 3 }
								onChange={ ( e ) =>
									onConfigChange( 'qty_max', Number( e.target.value ) )
								}
								placeholder="e.g.: 12"
							/>
						</div>
					</div>
					<div className="fp:grid fp:grid-cols-2 fp:gap-3">
						<div className="fp:space-y-1">
							<label className="fp:text-xs fp:font-medium">Separator</label>
							<Input
								value={ ( config.separator as string ) ?? ',' }
								onChange={ ( e ) => onConfigChange( 'separator', e.target.value ) }
								className="fp:w-16"
							/>
						</div>
						<div className="fp:space-y-1">
							<label className="fp:text-xs fp:font-medium">Weight</label>
							<Input
								type="number"
								value={ ( config.weight as number ) ?? 90 }
								onChange={ ( e ) =>
									onConfigChange( 'weight', Number( e.target.value ) )
								}
								className="fp:w-16"
							/>
						</div>
					</div>
				</div>
			);

		default:
			return null;
	}
}
