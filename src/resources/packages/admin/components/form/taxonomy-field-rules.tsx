import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { ComboboxMulti, type ComboboxOption } from './combobox-multi';
import { RangeInput } from './range-input';
import { RuleCard, RuleField } from './rule-card';
import type { TaxonomyRule } from '../../lib/types';

interface TaxonomyFieldRulesProps {
	value: TaxonomyRule[];
	onChange: ( rules: TaxonomyRule[] ) => void;
	taxonomyOptions: ComboboxOption[];
	onSearchTerms?: ( query: string, taxonomies: string[] ) => void;
	termSearchResults?: ComboboxOption[];
	isSearchingTerms?: boolean;
	title?: string;
	description?: string;
}

export function TaxonomyFieldRules( {
	value,
	onChange,
	taxonomyOptions,
	onSearchTerms,
	termSearchResults,
	isSearchingTerms = false,
	title = 'Taxonomy Field Rules',
	description,
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
						<RuleField label="Taxonomies">
							<ComboboxMulti
								value={ rule.taxonomies }
								onChange={ ( taxonomies ) => updateRule( index, { taxonomies } ) }
								options={ taxonomyOptions }
								placeholder="Select taxonomies..."
							/>
						</RuleField>

						<RuleField label="Terms">
							<ComboboxMulti
								value={ rule.terms.map( String ) }
								onChange={ ( terms ) => updateRule( index, { terms } ) }
								placeholder="Search for terms..."
								onSearch={ ( query ) => onSearchTerms?.( query, rule.taxonomies ) }
								searchResults={ termSearchResults }
								isSearching={ isSearchingTerms }
							/>
						</RuleField>

						<RuleField
							label="Rate (%)"
							description="Percentage rate of posts that will have terms generated."
						>
							<Input
								type="number"
								value={ rule.rate }
								onChange={ ( e ) =>
									updateRule( index, { rate: Number( e.target.value ) } )
								}
								min={ 0 }
								max={ 100 }
								className="fp:w-24"
							/>
						</RuleField>

						<RuleField
							label="Quantity"
							description="How many terms will be selected. From 1 to 4, or just the first field for an exact number."
						>
							<RangeInput
								minValue={ rule.qty.min }
								maxValue={ rule.qty.max }
								onMinChange={ ( min ) =>
									updateRule( index, { qty: { ...rule.qty, min } } )
								}
								onMaxChange={ ( max ) =>
									updateRule( index, { qty: { ...rule.qty, max } } )
								}
								min={ 0 }
							/>
						</RuleField>
					</RuleCard>
				) ) }

				<div className="fp:flex fp:justify-end">
					<Button type="button" variant="outline" size="sm" onClick={ addRule }>
						+ Add Taxonomy Rule
					</Button>
				</div>
			</div>
		</div>
	);
}
