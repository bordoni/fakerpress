import { useState, useCallback } from 'react';
import { format, subDays, startOfWeek, startOfMonth, startOfYear } from 'date-fns';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';

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
				onEndChange( format( end, DATE_FORMAT ) );
			}
		},
		[ onStartChange, onEndChange ]
	);

	return (
		<div className="fp:flex fp:flex-wrap fp:items-center fp:gap-2">
			<Select value={ preset } onValueChange={ handlePresetChange }>
				<SelectTrigger className="fp:w-44">
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

			<Input
				type="date"
				value={ startDate }
				onChange={ ( e ) => {
					onStartChange( e.target.value );
					setPreset( '' );
				} }
				className="fp:w-36"
			/>

			{ startDate && (
				<>
					<span className="fp:text-muted-foreground fp:text-sm">&gt;</span>
					<Input
						type="date"
						value={ endDate }
						onChange={ ( e ) => {
							onEndChange( e.target.value );
							setPreset( '' );
						} }
						className="fp:w-36"
					/>
				</>
			) }
		</div>
	);
}
