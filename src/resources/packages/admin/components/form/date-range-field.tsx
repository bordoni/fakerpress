import { useState, useCallback, useMemo } from 'react';
import { format, subDays, startOfWeek, startOfMonth, startOfYear, endOfDay } from 'date-fns';
import { Calendar as CalendarIcon } from 'lucide-react';
import { Button } from '../ui/button';
import { Calendar } from '../ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '../ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { cn } from '../../lib/utils';

const DATE_FORMAT = 'yyyy-MM-dd';

interface DateRangeFieldProps {
	startDate: string;
	endDate: string;
	onStartChange: ( value: string ) => void;
	onEndChange: ( value: string ) => void;
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

export function DateRangeField( {
	startDate,
	endDate,
	onStartChange,
	onEndChange,
}: DateRangeFieldProps ) {
	const [ preset, setPreset ] = useState( '' );

	const handlePresetChange = useCallback(
		( value: string ) => {
			setPreset( value );
			const presetConfig = PRESETS.find( ( p ) => p.value === value );
			if ( presetConfig ) {
				const [ start, end ] = presetConfig.getRange();
				onStartChange( format( start, DATE_FORMAT ) );
				onEndChange( format( endOfDay( end ), DATE_FORMAT ) );
			}
		},
		[ onStartChange, onEndChange ]
	);

	const startDateObj = useMemo(
		() => ( startDate ? new Date( startDate + 'T00:00:00' ) : undefined ),
		[ startDate ]
	);

	const endDateObj = useMemo(
		() => ( endDate ? new Date( endDate + 'T00:00:00' ) : undefined ),
		[ endDate ]
	);

	return (
		<div className="fp-flex fp-flex-wrap fp-items-center fp-gap-2">
			<Select value={ preset } onValueChange={ handlePresetChange }>
				<SelectTrigger className="fp-w-44">
					<SelectValue placeholder="Select an Interval" />
				</SelectTrigger>
				<SelectContent>
					{ PRESETS.map( ( p ) => (
						<SelectItem key={ p.value } value={ p.value }>
							{ p.label }
						</SelectItem>
					) ) }
				</SelectContent>
			</Select>

			<DatePickerButton
				date={ startDateObj }
				onSelect={ ( d ) => {
					onStartChange( d ? format( d, DATE_FORMAT ) : '' );
					setPreset( '' );
				} }
				placeholder="Start date"
			/>

			<span className="fp-text-muted-foreground fp-text-sm">&gt;</span>

			<DatePickerButton
				date={ endDateObj }
				onSelect={ ( d ) => {
					onEndChange( d ? format( d, DATE_FORMAT ) : '' );
					setPreset( '' );
				} }
				placeholder="End date"
			/>
		</div>
	);
}

function DatePickerButton( {
	date,
	onSelect,
	placeholder,
}: {
	date?: Date;
	onSelect: ( date: Date | undefined ) => void;
	placeholder: string;
} ) {
	return (
		<Popover>
			<PopoverTrigger asChild>
				<Button
					variant="outline"
					className={ cn(
						'fp-w-36 fp-justify-start fp-text-left fp-font-normal',
						! date && 'fp-text-muted-foreground'
					) }
				>
					<CalendarIcon className="fp-mr-2 fp-h-4 fp-w-4" />
					{ date ? format( date, DATE_FORMAT ) : placeholder }
				</Button>
			</PopoverTrigger>
			<PopoverContent className="fp-w-auto fp-p-0" align="start">
				<Calendar
					mode="single"
					selected={ date }
					onSelect={ onSelect }
					initialFocus
				/>
			</PopoverContent>
		</Popover>
	);
}
