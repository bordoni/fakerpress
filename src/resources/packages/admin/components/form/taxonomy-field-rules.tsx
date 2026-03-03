import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '../ui/accordion';
import { Plus, Minus } from 'lucide-react';
import { ComboboxMulti, type ComboboxOption } from './combobox-multi';
import { RangeInput } from './range-input';
import type { TaxonomyRule } from '../../lib/types';

interface TaxonomyFieldRulesProps {
	value: TaxonomyRule[];
	onChange: ( rules: TaxonomyRule[] ) => void;
	taxonomyOptions: ComboboxOption[];
	onSearchTerms?: ( query: string, taxonomies: string[] ) => void;
	termSearchResults?: ComboboxOption[];
	isSearchingTerms?: boolean;
}

export function TaxonomyFieldRules( {
	value,
	onChange,
	taxonomyOptions,
	onSearchTerms,
	termSearchResults,
	isSearchingTerms = false,
}: TaxonomyFieldRulesProps ) {
	const addRule = useCallback( () => {
		onChange( [
			...value,
			{ taxonomies: [], terms: [], rate: 50, qty: { min: 1, max: 3 } },
		] );
	}, [ value, onChange ] );

	const removeRule = useCallback(
		( index: number ) => {
			onChange( value.filter( ( _, i ) => i !== index ) );
		},
		[ value, onChange ]
	);

	const updateRule = useCallback(
		( index: number, updates: Partial< TaxonomyRule > ) => {
			const newRules = [ ...value ];
			newRules[ index ] = { ...newRules[ index ], ...updates };
			onChange( newRules );
		},
		[ value, onChange ]
	);

	return (
		<div className="fp-space-y-2">
			{ value.length > 0 && (
				<Accordion type="multiple" className="fp-w-full">
					{ value.map( ( rule, index ) => (
						<AccordionItem key={ index } value={ `tax-rule-${ index }` }>
							<AccordionTrigger className="fp-text-sm">
								<span className="fp-flex fp-items-center fp-gap-2">
									<span className="fp-inline-flex fp-items-center fp-justify-center fp-w-6 fp-h-6 fp-rounded-full fp-bg-muted fp-text-xs fp-font-medium">
										{ index + 1 }
									</span>
									Taxonomy Rule
									{ rule.taxonomies.length > 0 && (
										<span className="fp-text-muted-foreground">
											({ rule.taxonomies.join( ', ' ) })
										</span>
									) }
								</span>
							</AccordionTrigger>
							<AccordionContent>
								<div className="fp-space-y-3 fp-pt-2">
									<div className="fp-space-y-1">
										<label className="fp-text-xs fp-font-medium">Taxonomies</label>
										<ComboboxMulti
											value={ rule.taxonomies }
											onChange={ ( taxonomies ) =>
												updateRule( index, { taxonomies } )
											}
											options={ taxonomyOptions }
											placeholder="Select taxonomies..."
										/>
									</div>

									<div className="fp-space-y-1">
										<label className="fp-text-xs fp-font-medium">Terms</label>
										<ComboboxMulti
											value={ rule.terms.map( String ) }
											onChange={ ( terms ) =>
												updateRule( index, { terms } )
											}
											placeholder="Search for terms..."
											onSearch={ ( query ) =>
												onSearchTerms?.( query, rule.taxonomies )
											}
											searchResults={ termSearchResults }
											isSearching={ isSearchingTerms }
										/>
									</div>

									<div className="fp-grid fp-grid-cols-2 fp-gap-3">
										<div className="fp-space-y-1">
											<label className="fp-text-xs fp-font-medium">Rate (%)</label>
											<Input
												type="number"
												value={ rule.rate }
												onChange={ ( e ) =>
													updateRule( index, {
														rate: Number( e.target.value ),
													} )
												}
												min={ 0 }
												max={ 100 }
											/>
										</div>
										<div className="fp-space-y-1">
											<label className="fp-text-xs fp-font-medium">Quantity</label>
											<RangeInput
												minValue={ rule.qty.min }
												maxValue={ rule.qty.max }
												onMinChange={ ( min ) =>
													updateRule( index, {
														qty: { ...rule.qty, min },
													} )
												}
												onMaxChange={ ( max ) =>
													updateRule( index, {
														qty: { ...rule.qty, max },
													} )
												}
												min={ 0 }
											/>
										</div>
									</div>

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
				Add Taxonomy Rule
			</Button>
		</div>
	);
}
