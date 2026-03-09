import { ArrowRight } from 'lucide-react';
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
		<div className="fp:inline-flex fp:items-center fp:rounded-md fp:border fp:border-input fp:bg-background fp:focus-within:ring-[3px] fp:focus-within:ring-ring/50 fp:transition-shadow fp:overflow-hidden">
			<Input
				type="number"
				value={ minValue ?? '' }
				onChange={ ( e ) => onMinChange( e.target.value === '' ? undefined : Number( e.target.value ) ) }
				placeholder={ minPlaceholder }
				min={ min }
				max={ max }
				step={ step }
				className="fp:w-24 fp:border-0 fp:rounded-none fp:shadow-none fp:focus-visible:ring-0"
			/>
			{ minValue !== undefined && (
				<>
					<span className="fp:flex fp:items-center fp:self-stretch fp:border-x fp:border-input fp:px-2 fp:text-muted-foreground fp:select-none">
						<ArrowRight className="fp:size-3.5" />
					</span>
					<Input
						type="number"
						value={ maxValue ?? '' }
						onChange={ ( e ) => onMaxChange( e.target.value === '' ? undefined : Number( e.target.value ) ) }
						placeholder={ maxPlaceholder }
						min={ min }
						max={ max }
						step={ step }
						className="fp:w-24 fp:border-0 fp:rounded-none fp:shadow-none fp:focus-visible:ring-0"
					/>
				</>
			) }
		</div>
	);
}
