import { format, subDays, startOfWeek, startOfMonth, startOfYear } from 'date-fns';
import { ArrowRight } from 'lucide-react';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectSeparator, SelectTrigger, SelectValue } from '../ui/select';

const DATE_FORMAT = 'yyyy-MM-dd';
const CUSTOM_VALUE = 'custom';
const CUSTOM_LABEL = 'Custom interval';

interface DateRangeValue {
	preset: string;
	start: string;
	end: string;
}

interface DateRangeFieldProps {
	value: DateRangeValue;
	onChange: ( value: DateRangeValue ) => void;
}

const PRESETS: { value: string; label: string; getRange: () => [ Date, Date ] }[] = [
	{
		value: 'yesterday',
		label: 'Yesterday',
		getRange: () => [ subDays( new Date(), 1 ), subDays( new Date(), 1 ) ],
	},
	{
		value: 'today',
		label: 'Today',
		getRange: () => [ new Date(), new Date() ],
	},
	{
		value: 'this_week',
		label: 'This week',
		getRange: () => [ startOfWeek( new Date(), { weekStartsOn: 1 } ), new Date() ],
	},
	{
		value: 'this_month',
		label: 'This month',
		getRange: () => [ startOfMonth( new Date() ), new Date() ],
	},
	{
		value: 'last_30',
		label: 'Last 30 days',
		getRange: () => [ subDays( new Date(), 30 ), new Date() ],
	},
	{
		value: 'last_90',
		label: 'Last 90 days',
		getRange: () => [ subDays( new Date(), 90 ), new Date() ],
	},
	{
		value: 'this_year',
		label: 'This year',
		getRange: () => [ startOfYear( new Date() ), new Date() ],
	},
];

export function getPresetRange( presetValue: string ): [ string, string ] | null {
	const presetConfig = PRESETS.find( ( p ) => p.value === presetValue );
	if ( ! presetConfig ) return null;
	const [ start, end ] = presetConfig.getRange();
	return [ format( start, DATE_FORMAT ), format( end, DATE_FORMAT ) ];
}

export function DateRangeField( { value, onChange }: DateRangeFieldProps ) {
	const handlePresetChange = ( preset: string ) => {
		if ( preset === CUSTOM_VALUE ) {
			onChange( { ...value, preset: CUSTOM_VALUE } );
			return;
		}
		const presetConfig = PRESETS.find( ( p ) => p.value === preset );
		if ( presetConfig ) {
			const [ start, end ] = presetConfig.getRange();
			onChange( { preset, start: format( start, DATE_FORMAT ), end: format( end, DATE_FORMAT ) } );
		}
	};

	return (
		<div className="fp:flex fp:flex-wrap fp:items-center fp:gap-2">
			<Select value={ value.preset } onValueChange={ handlePresetChange }>
				<SelectTrigger className="fp:w-44">
					<SelectValue placeholder="Select an Interval" />
				</SelectTrigger>
				<SelectContent>
					<SelectItem value={ CUSTOM_VALUE }>{ CUSTOM_LABEL }</SelectItem>
					<SelectSeparator />
					{ PRESETS.map( ( p ) => (
						<SelectItem key={ p.value } value={ p.value }>
							{ p.label }
						</SelectItem>
					) ) }
				</SelectContent>
			</Select>

			<div className="fp:inline-flex fp:h-8 fp:items-center fp:rounded-md fp:border fp:border-input fp:bg-background fp:focus-within:ring-[3px] fp:focus-within:ring-ring/50 fp:transition-shadow fp:overflow-hidden">
				<Input
					type="date"
					value={ value.start }
					onChange={ ( e ) => onChange( { ...value, preset: CUSTOM_VALUE, start: e.target.value } ) }
					className="fp:w-36 fp:border-0 fp:rounded-none fp:shadow-none fp:focus-visible:ring-0"
				/>
				<span className="fp:flex fp:items-center fp:self-stretch fp:border-x fp:border-input fp:px-2 fp:text-muted-foreground fp:select-none">
					<ArrowRight className="fp:size-3.5" />
				</span>
				<Input
					type="date"
					value={ value.end }
					onChange={ ( e ) => onChange( { ...value, preset: CUSTOM_VALUE, end: e.target.value } ) }
					className="fp:w-36 fp:border-0 fp:rounded-none fp:shadow-none fp:focus-visible:ring-0"
				/>
			</div>
		</div>
	);
}
