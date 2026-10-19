export const __ = ( str: string ) => str;
export const _n = ( single: string, plural: string, count: number ) =>
	count === 1 ? single : plural;
export const _x = ( str: string ) => str;
// Handles positional placeholders (%1$d, %2$s) as well as plain %d / %s.
export const sprintf = ( format: string, ...args: unknown[] ) => {
	let next = 0;
	return format.replace( /%(?:(\d+)\$)?[sd]/g, ( _match, position ) =>
		String( args[ position ? Number( position ) - 1 : next++ ] )
	);
};
