import { useCallback } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
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
							<div className="fp:space-y-1">
								<label className="fp:text-xs fp:font-medium">Taxonomies</label>
								<ComboboxMulti
									value={ rule.taxonomies }
									onChange={ ( taxonomies ) =>
										updateRule( index, { taxonomies } )
									}
									options={ taxonomyOptions }
									placeholder="Select taxonomies..."
								/>
							</div>

							<div className="fp:space-y-1">
								<label className="fp:text-xs fp:font-medium">Terms</label>
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

							<div className="fp:grid fp:grid-cols-2 fp:gap-3">
								<div className="fp:space-y-1">
									<label className="fp:text-xs fp:font-medium">Rate (%)</label>
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
										className="fp:w-20"
									/>
								</div>
								<div className="fp:space-y-1">
									<label className="fp:text-xs fp:font-medium">Quantity</label>
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
					+ Add Taxonomy Rule
				</Button>
			</div>
		</div>
	);
}
