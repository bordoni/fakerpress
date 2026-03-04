import { Input } from '../ui/input';

interface RangeInputProps {
	minValue: number | undefined;
	maxValue: number | undefined;
	onMinChange: ( value: number | undefined ) => void;
	onMaxChange: ( value: number | undefined ) => void;
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
		<div className="fp:flex fp:items-center fp:gap-2">
			<Input
				type="number"
				value={ minValue ?? '' }
				onChange={ ( e ) => onMinChange( e.target.value === '' ? undefined : Number( e.target.value ) ) }
				placeholder={ minPlaceholder }
				min={ min }
				max={ max }
				step={ step }
				className="fp:w-24"
			/>
			{ minValue !== undefined && (
				<>
					<span className="fp:text-muted-foreground fp:text-sm">&gt;</span>
					<Input
						type="number"
						value={ maxValue ?? '' }
						onChange={ ( e ) => onMaxChange( e.target.value === '' ? undefined : Number( e.target.value ) ) }
						placeholder={ maxPlaceholder }
						min={ min }
						max={ max }
						step={ step }
						className="fp:w-24"
					/>
				</>
			) }
		</div>
	);
}
