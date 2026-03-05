import { useState, useCallback } from 'react';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '../ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '../ui/popover';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';
import { X, ChevronsUpDown } from 'lucide-react';
import { cn } from '../../lib/utils';

export interface ComboboxOption {
	value: string;
	label: string;
}

interface ComboboxMultiProps {
	/** Currently selected values. */
	value: string[];
	/** Called when selection changes. */
	onChange: ( value: string[] ) => void;
	/** Static options to display. */
	options?: ComboboxOption[];
	/** Placeholder text when empty. */
	placeholder?: string;
	/** Enable AJAX search mode. */
	onSearch?: ( query: string ) => void;
	/** Results from AJAX search. */
	searchResults?: ComboboxOption[];
	/** Whether a search is in progress. */
	isSearching?: boolean;
	/** Allow free-text tagging. */
	allowCreate?: boolean;
}

export function ComboboxMulti( {
	value,
	onChange,
	options = [],
	placeholder = 'Select...',
	onSearch,
	searchResults,
	isSearching = false,
	allowCreate = false,
}: ComboboxMultiProps ) {
	const [ open, setOpen ] = useState( false );
	const [ inputValue, setInputValue ] = useState( '' );

	const displayOptions = onSearch && searchResults ? searchResults : options;

	const handleSelect = useCallback(
		( selectedValue: string ) => {
			if ( value.includes( selectedValue ) ) {
				onChange( value.filter( ( v ) => v !== selectedValue ) );
			} else {
				onChange( [ ...value, selectedValue ] );
			}
		},
		[ value, onChange ]
	);

	const handleRemove = useCallback(
		( removedValue: string ) => {
			onChange( value.filter( ( v ) => v !== removedValue ) );
		},
		[ value, onChange ]
	);

	const handleInputChange = useCallback(
		( search: string ) => {
			setInputValue( search );
			onSearch?.( search );
		},
		[ onSearch ]
	);

	const handleKeyDown = useCallback(
		( e: React.KeyboardEvent< HTMLInputElement > ) => {
			if ( allowCreate && e.key === 'Enter' && inputValue.trim() ) {
				e.preventDefault();
				const trimmed = inputValue.trim();
				if ( ! value.includes( trimmed ) ) {
					onChange( [ ...value, trimmed ] );
				}
				setInputValue( '' );
			}
		},
		[ allowCreate, inputValue, value, onChange ]
	);

	const getLabel = ( val: string ): string => {
		const option = options.find( ( o ) => o.value === val );
		if ( option ) {
			return option.label;
		}
		if ( searchResults ) {
			const searchOption = searchResults.find( ( o ) => o.value === val );
			if ( searchOption ) {
				return searchOption.label;
			}
		}
		return val;
	};

	return (
		<Popover open={ open } onOpenChange={ setOpen }>
			<PopoverTrigger asChild>
				<Button
					variant="outline"
					role="combobox"
					aria-expanded={ open }
					className="fp:w-full fp:justify-between fp:h-auto fp:min-h-9 fp:py-1.5"
				>
					<div className="fp:flex fp:flex-wrap fp:gap-1 fp:flex-1">
						{ value.length === 0 && (
							<span className="fp:text-muted-foreground fp:font-normal">{ placeholder }</span>
						) }
						{ value.map( ( val ) => (
							<Badge
								key={ val }
								variant="secondary"
								className="fp:gap-1"
							>
								{ getLabel( val ) }
								<span
									role="button"
									tabIndex={ 0 }
									aria-label={ `Remove ${ getLabel( val ) }` }
									className="fp:ml-0.5 fp:rounded-full fp:outline-none fp:hover:bg-secondary-foreground/20 fp:cursor-pointer"
									onClick={ ( e ) => {
										e.stopPropagation();
										handleRemove( val );
									} }
									onKeyDown={ ( e ) => {
										if ( e.key === 'Enter' || e.key === ' ' ) {
											e.preventDefault();
											e.stopPropagation();
											handleRemove( val );
										}
									} }
								>
									<X className="fp:h-3 fp:w-3" />
								</span>
							</Badge>
						) ) }
					</div>
					<ChevronsUpDown className="fp:h-4 fp:w-4 fp:shrink-0 fp:opacity-50" />
				</Button>
			</PopoverTrigger>
			<PopoverContent className="fp:w-[var(--radix-popover-trigger-width)] fp:p-0" align="start">
				<Command shouldFilter={ ! onSearch }>
					<CommandInput
						placeholder={ `Search${ allowCreate ? ' or type to add' : '' }...` }
						value={ inputValue }
						onValueChange={ handleInputChange }
						onKeyDown={ handleKeyDown }
					/>
					<CommandList>
						<CommandEmpty>
							{ isSearching
								? 'Searching...'
								: allowCreate && inputValue.trim()
									? `Press Enter to add "${ inputValue.trim() }"`
									: 'No results found.' }
						</CommandEmpty>
						<CommandGroup>
							{ displayOptions.map( ( option ) => (
								<CommandItem
									key={ option.value }
									value={ option.value }
									onSelect={ () => handleSelect( option.value ) }
									className={ cn(
										value.includes( option.value ) && 'fp:bg-accent'
									) }
								>
									{ option.label }
								</CommandItem>
							) ) }
						</CommandGroup>
					</CommandList>
				</Command>
			</PopoverContent>
		</Popover>
	);
}
