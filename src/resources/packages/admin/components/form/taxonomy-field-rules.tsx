import { useCallback } from 'react';
import { Input } from '../ui/input';
import { ComboboxMulti, type ComboboxOption } from './combobox-multi';
import { RangeInput } from './range-input';
import { RuleCard, RuleField, RuleSection } from './rule-card';
import type { TaxonomyRule } from '../../lib/types';

/**
 * A blank rule, as the stable generator shows after "+" or after clearing the
 * last rule. Rules without taxonomies are dropped when the form is submitted.
 */
export const EMPTY_TAXONOMY_RULE: TaxonomyRule = {
	taxonomies: [],
	terms: [],
	rate: 85,
	qty: { min: 1, max: 4 },
};

/** Whether a rule has nothing chosen yet (so clearing it would change nothing). */
export function isEmptyTaxonomyRule( rule: TaxonomyRule ): boolean {
	return rule.taxonomies.length === 0 && rule.terms.length === 0;
}

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
	description = 'Use the fields below to configure the rules for the Taxonomy and Terms selected',
}: TaxonomyFieldRulesProps ) {
	// Like the stable generator, there is always at least one rule on screen.
	const rules = value.length > 0 ? value : [ EMPTY_TAXONOMY_RULE ];

	const addRule = useCallback( () => {
		onChange( [ ...rules, { ...EMPTY_TAXONOMY_RULE } ] );
	}, [ rules, onChange ] );

	// Removing the only rule clears it instead.
	const removeRule = useCallback(
		( index: number ) => {
			onChange( rules.length === 1 ? [] : rules.filter( ( _, i ) => i !== index ) );
		},
		[ rules, onChange ]
	);

	const updateRule = useCallback(
		( index: number, updates: Partial< TaxonomyRule > ) => {
			const newRules = [ ...rules ];
			newRules[ index ] = { ...newRules[ index ], ...updates };
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
					removeDisabled={ rules.length === 1 && isEmptyTaxonomyRule( rule ) }
				>
					<RuleField label="Taxonomies">
						<div className="fp:max-w-[420px]">
							<ComboboxMulti
								value={ rule.taxonomies }
								onChange={ ( taxonomies ) => updateRule( index, { taxonomies } ) }
								options={ taxonomyOptions }
								placeholder="Select Which taxonomies"
							/>
						</div>
					</RuleField>

					<RuleField label="Terms">
						<div className="fp:max-w-[420px]">
							<ComboboxMulti
								value={ rule.terms.map( String ) }
								onChange={ ( terms ) => updateRule( index, { terms } ) }
								placeholder="Which terms can be used"
								onSearch={ ( query ) => onSearchTerms?.( query, rule.taxonomies ) }
								searchResults={ termSearchResults }
								isSearching={ isSearchingTerms }
							/>
						</div>
					</RuleField>

					<RuleField
						label="Rate"
						description="Percentage rate of posts that will have terms generated for the amount below"
					>
						<Input
							type="number"
							value={ rule.rate }
							onChange={ ( e ) =>
								updateRule( index, { rate: Number( e.target.value ) } )
							}
							placeholder="Rate"
							min={ 0 }
							max={ 100 }
							className="fp:w-[84px]"
						/>
					</RuleField>

					<RuleField
						label="Quantity"
						description={
							<>
								How many terms will be selected.
								<br />
								E.g.: From 1 to 4 or just fill the first field for an exact number
							</>
						}
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
							showMax
						/>
					</RuleField>
				</RuleCard>
			) ) }
		</RuleSection>
	);
}
