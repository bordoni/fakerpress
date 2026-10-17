import { transformPostsForm, transformUsersForm } from '../transform';
import type { MetaRule, TaxonomyRule } from '../types';

const usersForm = ( meta: MetaRule[] ) => ( {
	qty: { min: 1, max: 1 },
	roles: [],
	description_size: { min: 1, max: 5 },
	use_html: false,
	html_tags: [],
	meta,
} );

const postsForm = ( taxonomy_rules: TaxonomyRule[], meta: MetaRule[] = [] ) => ( {
	qty: { min: 1, max: 1 },
	date: { preset: 'today', start: '2026-10-17', end: '2026-10-17' },
	post_type: [ 'post' ],
	parents: [],
	comment_status: [ 'open' ],
	authors: [],
	use_html: false,
	content_size: { min: 1, max: 5 },
	html_tags: [],
	image_providers: [],
	excerpt_size: { min: 1, max: 3 },
	taxonomy_rules,
	meta,
} );

describe( 'transform', () => {
	it( 'drops blank meta rules left by the rules UI', () => {
		const result = transformUsersForm(
			usersForm( [
				{ type: '', name: '', weight: 90, config: {} },
				{ type: 'letter', name: '   ', weight: 90, config: {} },
				{ type: 'email', name: ' contact ', weight: 90, config: {} },
			] )
		);

		expect( result.meta ).toEqual( [ { type: 'email', name: 'contact', weight: 90 } ] );
	} );

	it( 'sends no meta when every rule is blank', () => {
		expect( transformUsersForm( usersForm( [ { type: '', name: '', weight: 90, config: {} } ] ) ).meta ).toBeUndefined();
	} );

	it( 'orders date config as the handler expects: interval, format, weight', () => {
		const result = transformUsersForm(
			usersForm( [
				{
					type: 'date',
					name: 'event_date',
					weight: 80,
					config: { format: 'Y-m-d', interval: { name: 'today', min: '2026-10-17', max: '2026-10-17' } },
				},
			] )
		);

		const [ rule ] = result.meta as Record< string, unknown >[];
		expect( Object.keys( rule ) ).toEqual( [ 'type', 'name', 'interval', 'format', 'weight' ] );
		expect( rule.interval ).toEqual( { name: 'today', min: '2026-10-17', max: '2026-10-17' } );
	} );

	it( 'keeps attachment weight between providers and sizes', () => {
		const result = transformUsersForm(
			usersForm( [
				{ type: 'attachment', name: '_thumbnail_id', weight: 75, config: { providers: 'placeholder', store: 'id' } },
			] )
		);

		const [ rule ] = result.meta as Record< string, unknown >[];
		expect( Object.keys( rule ) ).toEqual( [ 'type', 'name', 'store', 'providers', 'weight', 'width', 'height' ] );
	} );

	it( 'drops taxonomy rules without taxonomies', () => {
		const result = transformPostsForm(
			postsForm( [
				{ taxonomies: [], terms: [], rate: 85, qty: { min: 1, max: 4 } },
				{ taxonomies: [ 'category' ], terms: [ 3 ], rate: 50, qty: { min: 2, max: undefined } },
			] )
		);

		expect( result.taxonomy ).toEqual( [ { taxonomies: [ 'category' ], terms: [ 3 ], rate: 50, qty: [ 2, 2 ] } ] );
	} );

	it( 'sends no taxonomy when every rule is blank', () => {
		const result = transformPostsForm( postsForm( [ { taxonomies: [], terms: [], rate: 85, qty: { min: 1, max: 4 } } ] ) );

		expect( result.taxonomy ).toBeUndefined();
	} );
} );
