/**
 * Shared TypeScript types for the FakerPress admin interface.
 */

/**
 * Configuration data passed from PHP via wp_localize_script.
 */
export interface PageConfig {
	page: string;
	restRoot: string;
	restNonce: string;
	ajaxUrl: string;
	ajaxNonces: Record< string, string >;
	data: Record< string, unknown >;
}

/**
 * Result from a generate REST API call.
 */
export interface GenerateResult {
	generated: number;
	ids: number[];
	links: Record< number, string >;
	time: number;
}

/**
 * REST API response from a generate endpoint.
 */
export interface GenerateResponse {
	generated: number;
	ids: number[];
	links: Record< number, string >;
	time: number;
	is_capped: boolean;
	total: number;
}

/**
 * Search result from an AJAX endpoint.
 */
export interface SearchResult {
	id: number | string;
	value: number | string;
	name: string;
	[ key: string ]: unknown;
}

/**
 * AJAX search response shape.
 */
export interface AjaxSearchResponse {
	status: boolean;
	message: string;
	results: SearchResult[];
	more: boolean;
}

/**
 * Range value with min and max.
 */
export interface RangeValue {
	min: number | undefined;
	max: number | undefined;
}

/**
 * Date range value.
 */
export interface DateRange {
	preset: string;
	start: string;
	end: string;
}

/**
 * Meta field rule.
 */
export interface MetaRule {
	type: string;
	name: string;
	config: Record< string, unknown >;
}

/**
 * Taxonomy field rule.
 */
export interface TaxonomyRule {
	taxonomies: string[];
	terms: ( string | number )[];
	rate: number;
	qty: RangeValue;
}

/**
 * Taxonomy object from WordPress.
 */
export interface WPTaxonomy {
	name: string;
	label: string;
	labels: Record< string, string >;
	public: boolean;
	hierarchical: boolean;
}

/**
 * Post type object from WordPress.
 */
export interface WPPostType {
	name: string;
	label: string;
	labels: Record< string, string >;
	public: boolean;
	hierarchical: boolean;
}
