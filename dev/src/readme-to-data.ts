import { Slimdown } from './slimdown.js';
import type { ChangelogVersion, ReadmeData, ReadmeDataSection } from './types.js';

/**
 * Port of dev/src/Readme.php
 *
 * Parses a WordPress readme.txt into structured data sections.
 */

export function parseReadmeToData(fileContents: string): ReadmeData {
	// Normalize line endings
	fileContents = fileContents.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
	fileContents = fileContents.trim();

	// Strip BOM
	if (fileContents.startsWith('\uFEFF')) {
		fileContents = fileContents.slice(1);
	}
	if (fileContents.startsWith('\xEF\xBB\xBF')) {
		fileContents = fileContents.slice(3);
	}

	// Markdown transformations — convert markdown headers to WordPress format
	fileContents = fileContents.replace(/^###([^#]+)#*?\s*?\n/gim, '=$1=\n');
	fileContents = fileContents.replace(/^##([^#]+)#*?\s*?\n/gim, '==$1==\n');
	fileContents = fileContents.replace(/^#([^#]+)#*?\s*?\n/gim, '===$1===\n');

	// Find all section markers
	const sectionRegex = /^([=]*) ([^=]*) ([=]*)/gim;
	const allMatches: { equals: string; name: string; index: number }[] = [];
	let m;
	while ((m = sectionRegex.exec(fileContents)) !== null) {
		allMatches.push({ equals: m[1], name: m[2], index: m.index });
	}

	if (!allMatches.length) {
		return {};
	}

	// Filter to only == sections (not === or =)
	const validSections = allMatches.filter((s) => s.equals === '==');

	let content = fileContents;

	const sections: ReadmeData = {};
	sections['headers'] = {
		name: 'headers',
		content: null,
	};

	let lastName: string | undefined;

	for (const section of validSections) {
		const name = section.name;
		sections[name.toLowerCase()] = {
			name,
			content: '',
		};

		const parts = content.split(`== ${name} ==`);

		if (!sections['headers'].content) {
			sections['headers'].content = parts[0];
		} else if (lastName !== undefined) {
			sections[lastName.toLowerCase()].content = parts[0];
		}

		if (parts[1] !== undefined && parts[1] !== '') {
			content = parts[1];
		}

		lastName = name;
	}

	// Assign remaining content to the last section
	if (lastName !== undefined) {
		sections[lastName.toLowerCase()].content = content;
	}

	// Parse changelog if it exists
	if (sections['changelog']) {
		sections['changelog'].versions = parseChangelogSection(sections['changelog'].content || '');
	}

	return sections;
}

function parseChangelogSection(content: string): Record<string, ChangelogVersion> {
	const versions: Record<string, ChangelogVersion> = {};

	// Find all version headers
	const versionRegex = /^(?:[=]*) ([^=]*) (?:[=]*)/gim;
	const versionTitles: string[] = [];
	let m;
	while ((m = versionRegex.exec(content)) !== null) {
		versionTitles.push(m[1]);
	}

	let lastVersion: string | undefined;

	for (const versionTitle of versionTitles) {
		let separator: string | false = false;
		if (versionTitle.includes('&mdash;')) {
			separator = '&mdash;';
		} else if (versionTitle.includes('-')) {
			separator = '-';
		}

		if (!separator) {
			continue;
		}

		const titleParts = versionTitle.split(separator);
		const versionNumber = titleParts[0].trim();
		const versionContentParts = content.split(`= ${versionTitle} =`);

		const version: ChangelogVersion = {
			number: versionNumber,
			date: titleParts[1].trim(),
			content: null,
		};

		if (lastVersion !== undefined) {
			versions[lastVersion].content = versionContentParts[0].trim();
		}

		if (versionContentParts[1] !== undefined && versionContentParts[1] !== '') {
			content = versionContentParts[1];
		}

		lastVersion = version.number;
		versions[version.number] = version;
	}

	// Assign remaining content to the last version
	if (lastVersion !== undefined) {
		versions[lastVersion].content = content;
	}

	// Render HTML for first 10 versions using Slimdown (with header rule removed)
	const slimdown = new Slimdown();
	slimdown.removeRule('/(#+)(.*)/');

	let count = 0;
	for (const version of Object.values(versions)) {
		if (count === 10) {
			break;
		}

		const lines = (version.content || '').split('\n');
		const html: string[] = ['<ul>'];

		for (const line of lines) {
			const change = line.trim();
			if (!change) {
				continue;
			}

			// str_replace_first('*', '', change) — JS replace matches first occurrence naturally
			const piece = '<li>' + slimdown.render(change.replace('*', '')) + '</li>';
			html.push(piece);
		}

		html.push('</ul>');
		version.html = html.join('\n');
		count++;
	}

	// Filter to only versions that have html
	const filtered: Record<string, ChangelogVersion> = {};
	for (const [key, version] of Object.entries(versions)) {
		if (version.html) {
			filtered[key] = version;
		}
	}

	return filtered;
}
