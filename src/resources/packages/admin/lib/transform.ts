/**
 * Data transform utilities.
 *
 * Converts clean form objects (from React Hook Form) into REST API params
 * matching what each FakerPress endpoint expects.
 *
 * The endpoints hand these params to the generator modules (Module\Post,
 * Module\User, …) as they are, so the keys and formats here are the ones the
 * modules read: comma-separated strings for lists, `interval_date` for dates,
 * and so on. The quantity goes out as `qty: { min, max }`, which the REST
 * batching layer resolves to a random number in the range.
 */

import type { RangeValue, DateRange, MetaRule, TaxonomyRule } from './types';

/**
 * Convert a quantity range to the `qty` object the batching layer reads.
 */
function qtyToParam( range: RangeValue ): { min: number; max: number } {
	const min = range.min ?? 1;
	return { min, max: Math.max( range.max ?? min, min ) };
}

/**
 * Convert a `{ min, max }` range to the `[ min, max ]` pair the modules read.
 */
function rangeToPair( range: RangeValue, fallback = 1 ): [ number, number ] {
	const min = range.min ?? fallback;
	return [ min, range.max ?? min ];
}

/**
 * Join a list into the comma-separated string the modules explode.
 */
function list( values: ( string | number )[] | undefined ): string {
	return ( values ?? [] ).map( String ).join( ',' );
}

/**
 * Convert a date range to the `interval_date` param the modules read.
 */
function dateToParams( date: DateRange ): { interval_date?: { min?: string; max?: string } } {
	if ( ! date.start && ! date.end ) {
		return {};
	}
	return {
		interval_date: {
			...( date.start ? { min: date.start } : {} ),
			...( date.end ? { max: date.end } : {} ),
		},
	};
}

/**
 * Canonical order of config keys per meta type.
 *
 * The backend consumes meta config positionally (`array_values()` is passed to
 * `meta_type_<type>()`), so the keys must be emitted in the exact parameter
 * order of the matching handler. React builds `config` in interaction order, so
 * we re-order here to guarantee a correct mapping regardless of the UI. Weight
 * is appended last by `metaToParam` (it is the final argument of every handler).
 */
const META_CONFIG_ORDER: Record< string, string[] > = {
	numbers: [ 'number' ],
	wp_query: [ 'query' ],
	attachment: [ 'store', 'providers', 'width', 'height' ],
	elements: [ 'elements', 'qty', 'separator' ],
	words: [ 'qty' ],
	text: [ 'text_type', 'qty', 'separator' ],
	html: [ 'elements', 'qty' ],
	lexify: [ 'template' ],
	asciify: [ 'template' ],
	regexify: [ 'template' ],
	person: [ 'template', 'gender' ],
	geo: [ 'template' ],
	company: [ 'template' ],
	date: [ 'interval', 'format' ],
};

/**
 * Re-order a rule's config object into the canonical handler argument order.
 */
function orderedConfig(
	type: string,
	config: Record< string, unknown >
): Record< string, unknown > {
	const order = META_CONFIG_ORDER[ type ];
	if ( ! order ) {
		return { ...config };
	}
	const result: Record< string, unknown > = {};
	order.forEach( ( key ) => {
		if ( config[ key ] !== undefined ) {
			result[ key ] = config[ key ];
		}
	} );
	// Preserve any keys not in the canonical list, appended after.
	Object.keys( config ).forEach( ( key ) => {
		if ( ! ( key in result ) ) {
			result[ key ] = config[ key ];
		}
	} );
	return result;
}

/**
 * Convert meta rules to API format.
 */
function metaToParam( meta: MetaRule[] ): Record< string, unknown >[] | undefined {
	// The rules UI always shows at least one rule, so blank ones (no type or no
	// name yet) are expected here; the backend would skip them anyway.
	const rules = ( meta ?? [] ).filter( ( rule ) => rule.type && rule.name.trim() );
	if ( rules.length === 0 ) {
		return undefined;
	}
	return rules.map( ( rule ) => {
		const config = orderedConfig( rule.type, rule.config );
		const param: Record< string, unknown > = { type: rule.type, name: rule.name.trim() };
		const weight =
			rule.weight !== undefined && rule.weight !== null ? rule.weight : undefined;

		// Attachment is the only handler where weight is NOT the final argument:
		// meta_type_attachment( store, providers, weight, width, height ). Width and
		// height are always emitted (empty when unset) to keep the positions stable.
		if ( rule.type === 'attachment' ) {
			param.store = config.store;
			param.providers = config.providers;
			param.weight = weight ?? 100;
			param.width = config.width ?? [];
			param.height = config.height ?? [];
			return param;
		}

		Object.assign( param, config );
		// Weight is the last positional argument for every other meta_type_* handler.
		if ( weight !== undefined ) {
			param.weight = weight;
		}
		return param;
	} );
}

/**
 * Transform Terms form data for the REST API.
 *
 * @param data Terms form values.
 */
export function transformTermsForm( data: {
	qty: RangeValue;
	size: RangeValue;
	taxonomies: string[];
	meta: MetaRule[];
} ): Record< string, unknown > {
	return {
		qty: qtyToParam( data.qty ),
		size: rangeToPair( data.size ),
		taxonomies: list( data.taxonomies ),
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Users form data for the REST API.
 *
 * @param data Users form values.
 */
export function transformUsersForm( data: {
	qty: RangeValue;
	roles: string[];
	description_size: RangeValue;
	use_html: boolean;
	html_tags: string[];
	meta: MetaRule[];
} ): Record< string, unknown > {
	return {
		qty: qtyToParam( data.qty ),
		roles: list( data.roles ),
		description_size: rangeToPair( data.description_size ),
		use_html: data.use_html,
		html_tags: list( data.html_tags ),
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Comments form data for the REST API.
 *
 * @param data Comments form values.
 */
export function transformCommentsForm( data: {
	type: string[];
	post_type: string[];
	qty: RangeValue;
	date: DateRange;
	content_size: RangeValue;
	use_html: boolean;
	html_tags: string[];
	meta: MetaRule[];
} ): Record< string, unknown > {
	return {
		type: list( data.type ),
		post_types: list( data.post_type ),
		qty: qtyToParam( data.qty ),
		...dateToParams( data.date ),
		content_size: rangeToPair( data.content_size ),
		use_html: data.use_html,
		html_tags: list( data.html_tags ),
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Attachments form data for the REST API.
 *
 * @param data Attachments form values.
 */
export function transformAttachmentsForm( data: {
	qty: RangeValue;
	date: DateRange;
	provider: string;
	width: RangeValue;
	height: RangeValue;
	aspect_ratio: number;
	parents: number[];
	author: number[];
	alt_text: boolean;
	caption: boolean;
	description: boolean;
	meta: MetaRule[];
} ): Record< string, unknown > {
	const [ widthMin, widthMax ] = rangeToPair( data.width, 200 );
	const hasHeight = data.height.min !== undefined || data.height.max !== undefined;
	const [ heightMin, heightMax ] = rangeToPair( data.height, 0 );
	return {
		qty: qtyToParam( data.qty ),
		...dateToParams( data.date ),
		provider: data.provider,
		width: { min: widthMin, max: widthMax },
		height: hasHeight ? { min: heightMin, max: heightMax } : undefined,
		aspect_ratio: data.aspect_ratio || undefined,
		post_parent: data.parents,
		author: list( data.author ),
		generate_alt_text: data.alt_text,
		generate_caption: data.caption,
		generate_description: data.description,
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Posts form data for the REST API.
 *
 * @param data Posts form values.
 */
export function transformPostsForm( data: {
	qty: RangeValue;
	date: DateRange;
	post_type: string[];
	parents: number[];
	comment_status: string[];
	authors: number[];
	use_html: boolean;
	content_size: RangeValue;
	html_tags: string[];
	image_providers: string[];
	excerpt_size: RangeValue;
	taxonomy_rules: TaxonomyRule[];
	meta: MetaRule[];
} ): Record< string, unknown > {
	// Rules without taxonomies are the blank placeholder the rules UI keeps on screen.
	const taxonomyRules = data.taxonomy_rules.filter(
		( rule ) => rule.taxonomies.length > 0
	);
	return {
		qty: qtyToParam( data.qty ),
		...dateToParams( data.date ),
		post_types: list( data.post_type ),
		post_parent: list( data.parents ),
		comment_status: list( data.comment_status ),
		author: list( data.authors ),
		use_html: data.use_html,
		content_size: rangeToPair( data.content_size ),
		html_tags: list( data.html_tags ),
		images_origin: list( data.image_providers ),
		excerpt_size: rangeToPair( data.excerpt_size ),
		// The Post module reads `taxonomy` (not `taxonomy_rules`) and tax_input()
		// expects a `qty` range plus `rate`; there is no taxonomy weight.
		taxonomy:
			taxonomyRules.length > 0
				? taxonomyRules.map( ( rule ) => ( {
						taxonomies: rule.taxonomies,
						terms: rule.terms,
						rate: rule.rate,
						qty: rangeToPair( rule.qty ),
				  } ) )
				: undefined,
		meta: metaToParam( data.meta ),
	};
}
