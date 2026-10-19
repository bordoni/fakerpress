// Downloads the translation files listed by the `i18n` entries in .puprc, the same files `pup i18n` fetches.
// pup sends every request at once, and translate.wordpress.org now answers that burst with 429 Too Many
// Requests, which fails the release. This fetches one file at a time and backs off on 429 and 5xx.
// Usage: bun .github/scripts/i18n.ts
import { readFileSync, writeFileSync, mkdirSync } from "node:fs";

type I18nConfig = {
	path: string;
	url: string;
	slug: string;
	textdomain: string;
	formats?: string[];
	file_format?: string;
	filter?: { minimum_percentage?: number };
};

type TranslationSet = {
	locale: string;
	slug: string;
	wp_locale?: string;
	current_count: number;
	percent_translated: number;
};

const puprc = JSON.parse( readFileSync( ".puprc", "utf8" ) );
const configs: I18nConfig[] = [ puprc.i18n ].flat().filter( Boolean );
const attempts = 6;
const pause = 500;

async function get( url: string ): Promise<Response> {
	for ( let attempt = 1; ; attempt++ ) {
		const res = await fetch( url );
		if ( res.ok || attempt === attempts || ( res.status !== 429 && res.status < 500 ) ) {
			return res;
		}
		const wait = Number( res.headers.get( "retry-after" ) ) * 1000 || 2000 * 2 ** attempt;
		console.log( `  ${ res.status } on ${ url }, retrying in ${ wait / 1000 }s` );
		await Bun.sleep( wait );
	}
}

for ( const config of configs ) {
	const projectUrl = `${ config.url }/api/projects/${ config.slug }`;
	const fileFormat = config.file_format ?? "%textdomain%-%wp_locale%.%format%";
	const minimum = config.filter?.minimum_percentage ?? 30;
	console.log( `Fetching language files for ${ config.textdomain } from ${ config.url }` );

	const project = await get( projectUrl );
	if ( ! project.ok ) {
		throw new Error( `Failed to fetch project data from ${ projectUrl }: ${ project.status }` );
	}
	const sets: TranslationSet[] = ( ( await project.json() ).translation_sets ?? [] ).filter(
		( set: TranslationSet ) => set.current_count > 0 && set.percent_translated >= minimum
	);
	if ( ! sets.length ) {
		throw new Error( `No translation sets at ${ minimum }% or more in ${ projectUrl }` );
	}

	mkdirSync( config.path, { recursive: true } );
	for ( const set of sets ) {
		for ( const format of config.formats ?? [ "mo" ] ) {
			const url = `${ projectUrl }/${ set.locale }/${ set.slug }/export-translations?format=${ format }`;
			const res = await get( url );
			const body = new Uint8Array( await res.arrayBuffer() );
			// pup treats anything under 200 bytes as a failed export.
			if ( ! res.ok || body.byteLength < 200 ) {
				throw new Error( `Failed to fetch ${ url }: ${ res.status }, ${ body.byteLength } bytes` );
			}
			const file = fileFormat
				.replace( "%domainPath%", config.path )
				.replace( "%textdomain%", config.textdomain )
				.replace( "%locale%", set.locale )
				.replace( "%wp_locale%", set.wp_locale ?? "" )
				.replace( "%format%", format );
			writeFileSync( `${ config.path }/${ file }`, body );
			console.log( `  ${ file } (${ set.percent_translated }%)` );
			await Bun.sleep( pause );
		}
	}
	console.log( `Saved ${ sets.length } translation sets to ${ config.path }/` );
}
