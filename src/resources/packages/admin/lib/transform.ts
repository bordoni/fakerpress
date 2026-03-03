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
	return range.min === range.max
		? range.min
		: `${ range.min }-${ range.max }`;
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
 * Convert meta rules to API format.
 */
function metaToParam( meta: MetaRule[] ): Record< string, unknown >[] | undefined {
	if ( ! meta || meta.length === 0 ) {
		return undefined;
	}
	return meta.map( ( rule ) => ( {
		type: rule.type,
		name: rule.name,
		...rule.config,
	} ) );
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
		name_size: [ data.size.min, data.size.max ],
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
		description_size: [ data.description_size.min, data.description_size.max ],
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
		content_size: [ data.content_size.min, data.content_size.max ],
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
		width: [ data.width.min, data.width.max ],
		height: [ data.height.min, data.height.max ],
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
		content_size: [ data.content_size.min, data.content_size.max ],
		html_tags: data.html_tags,
		image_providers: data.image_providers,
		excerpt_size: [ data.excerpt_size.min, data.excerpt_size.max ],
		taxonomy_rules: data.taxonomy_rules.length > 0
			? data.taxonomy_rules.map( ( rule ) => ( {
				taxonomies: rule.taxonomies,
				terms: rule.terms,
				rate: rule.rate,
				quantity: rangeToParam( rule.qty ),
			} ) )
			: undefined,
		meta: metaToParam( data.meta ),
	};
}
