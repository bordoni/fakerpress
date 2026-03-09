export const __ = ( str: string ) => str;
export const _n = ( single: string, plural: string, count: number ) =>
	count === 1 ? single : plural;
export const _x = ( str: string ) => str;
export const sprintf = ( format: string, ...args: unknown[] ) =>
	args.reduce< string >(
		( result, arg ) => result.replace( /%[sd]/, String( arg ) ),
		format
	);
