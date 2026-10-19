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
 * One labelled fact about a generated item, e.g. { label: 'Status', value: 'Published' }.
 */
export interface GeneratedItemSummary {
	label: string;
	value: string;
}

/**
 * A generated item as the generate endpoints describe it.
 */
export interface GeneratedItem {
	id: number;
	title: string;
	type: string;
	status: string | null;
	date: string | null;
	summary: GeneratedItemSummary[];
	edit_url: string | null;
	view_url: string | null;
	thumbnail: string | null;
	details: Record< string, unknown >;
}

/**
 * One request/response round trip of a generate run.
 */
export interface GenerateBatch {
	generated: number;
	time: number;
	request: Record< string, unknown >;
	response: unknown;
}

/**
 * Result of a whole generate run, across every batch.
 */
export interface GenerateResult {
	generated: number;
	ids: number[];
	links: ( string | number )[];
	time: number;
	items: GeneratedItem[];
	listUrl: string | null;
	batches: GenerateBatch[];
	finishedAt: string;
}

/**
 * A failed generate run.
 */
export interface GenerateError {
	message: string;
	code: string | null;
	status: number | null;
	request: Record< string, unknown > | null;
	response: unknown;
	batches: GenerateBatch[];
	finishedAt: string;
}

/**
 * REST API response from a generate endpoint.
 */
export interface GenerateResponse {
	generated: number;
	ids: number[];
	links: ( string | number )[] | Record< number, string | number >;
	time: number;
	is_capped: boolean;
	total: number;
	offset?: number;
	items?: GeneratedItem[];
	list_url?: string | null;
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
	weight?: number;
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
	default_meta?: MetaRule[];
}
