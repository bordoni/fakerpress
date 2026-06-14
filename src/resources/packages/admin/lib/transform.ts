/**
 * Data transform utilities.
 *
 * Converts clean form objects (from React Hook Form) into REST API params
 * matching what each FakerPress endpoint expects.
 */

import type { RangeValue, DateRange, MetaRule, TaxonomyRule } from './types';

/**
 * Convert a range to the API format.
 */
function rangeToParam( range: RangeValue ): string | number {
	const min = range.min ?? 1;
	const max = range.max ?? min;
	return min === max ? min : `${ min }-${ max }`;
}

/**
 * Convert a date range to API params.
 */
function dateToParams( date: DateRange ): Record< string, string > {
	const params: Record< string, string > = {};
	if ( date.start ) {
		params.date_min = date.start;
	}
	if ( date.end ) {
		params.date_max = date.end;
	}
	return params;
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
	attachment: [ 'store', 'providers' ],
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
	if ( ! meta || meta.length === 0 ) {
		return undefined;
	}
	return meta.map( ( rule ) => {
		const param: Record< string, unknown > = {
			type: rule.type,
			name: rule.name,
			...orderedConfig( rule.type, rule.config ),
		};
		// Weight is the last positional argument for every meta_type_* handler, so
		// it must be appended after the type-specific config keys.
		if ( rule.weight !== undefined && rule.weight !== null ) {
			param.weight = rule.weight;
		}
		return param;
	} );
}

/**
 * Transform Terms form data for the REST API.
 */
export function transformTermsForm( data: {
	qty: RangeValue;
	size: RangeValue;
	taxonomies: string[];
	meta: MetaRule[];
} ): Record< string, unknown > {
	return {
		quantity: rangeToParam( data.qty ),
		name_size: [ data.size.min ?? 1, data.size.max ?? data.size.min ?? 1 ],
		taxonomies: data.taxonomies,
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Users form data for the REST API.
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
		quantity: rangeToParam( data.qty ),
		roles: data.roles,
		description_size: [ data.description_size.min ?? 1, data.description_size.max ?? data.description_size.min ?? 1 ],
		use_html: data.use_html,
		html_tags: data.html_tags,
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Comments form data for the REST API.
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
		type: data.type,
		post_type: data.post_type,
		quantity: rangeToParam( data.qty ),
		...dateToParams( data.date ),
		content_size: [ data.content_size.min ?? 1, data.content_size.max ?? data.content_size.min ?? 1 ],
		use_html: data.use_html,
		html_tags: data.html_tags,
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Attachments form data for the REST API.
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
	return {
		quantity: rangeToParam( data.qty ),
		...dateToParams( data.date ),
		provider: data.provider,
		width: [ data.width.min ?? 200, data.width.max ?? data.width.min ?? 200 ],
		height: ( data.height.min !== undefined || data.height.max !== undefined )
			? [ data.height.min ?? 0, data.height.max ?? data.height.min ?? 0 ]
			: undefined,
		aspect_ratio: data.aspect_ratio || undefined,
		parents: data.parents,
		author: data.author,
		alt_text: data.alt_text,
		caption: data.caption,
		description: data.description,
		meta: metaToParam( data.meta ),
	};
}

/**
 * Transform Posts form data for the REST API.
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
	return {
		quantity: rangeToParam( data.qty ),
		...dateToParams( data.date ),
		post_type: data.post_type,
		parents: data.parents,
		comment_status: data.comment_status,
		author: data.authors,
		use_html: data.use_html,
		content_size: [ data.content_size.min ?? 1, data.content_size.max ?? data.content_size.min ?? 1 ],
		html_tags: data.html_tags,
		image_providers: data.image_providers,
		excerpt_size: [ data.excerpt_size.min ?? 1, data.excerpt_size.max ?? data.excerpt_size.min ?? 1 ],
		// The Post module reads `taxonomy` (not `taxonomy_rules`) and tax_input()
		// expects a `qty` range plus `rate`; there is no taxonomy weight.
		taxonomy: data.taxonomy_rules.length > 0
			? data.taxonomy_rules.map( ( rule ) => ( {
				taxonomies: rule.taxonomies,
				terms: rule.terms,
				rate: rule.rate,
				qty: [ rule.qty.min ?? 1, rule.qty.max ?? rule.qty.min ?? 1 ],
			} ) )
			: undefined,
		meta: metaToParam( data.meta ),
	};
}
