import { Input } from '../ui/input';

interface RangeInputProps {
	minValue: number;
	maxValue: number;
	onMinChange: ( value: number ) => void;
	onMaxChange: ( value: number ) => void;
	minPlaceholder?: string;
	maxPlaceholder?: string;
	min?: number;
	max?: number;
	step?: number;
}

export function RangeInput( {
	minValue,
	maxValue,
	onMinChange,
	onMaxChange,
	minPlaceholder = 'Min',
	maxPlaceholder = 'Max',
	min,
	max,
	step = 1,
}: RangeInputProps ) {
	return (
		<div className="fp-flex fp-items-center fp-gap-2">
			<Input
				type="number"
				value={ minValue }
				onChange={ ( e ) => onMinChange( Number( e.target.value ) ) }
				placeholder={ minPlaceholder }
				min={ min }
				max={ max }
				step={ step }
				className="fp-w-24"
			/>
			<span className="fp-text-muted-foreground fp-text-sm">&gt;</span>
			<Input
				type="number"
				value={ maxValue }
				onChange={ ( e ) => onMaxChange( Number( e.target.value ) ) }
				placeholder={ maxPlaceholder }
				min={ min }
				max={ max }
				step={ step }
				className="fp-w-24"
			/>
		</div>
	);
}
