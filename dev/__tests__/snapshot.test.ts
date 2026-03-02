import { describe, expect, test } from 'bun:test';
import { readFileSync } from 'node:fs';
import { join } from 'node:path';
import { readmeToMarkdown } from '../src/readme-to-markdown.js';
import { parseReadmeToData } from '../src/readme-to-data.js';
import { phpJsonEncode } from '../src/json-encode.js';

const ROOT = join(import.meta.dir, '../..');
const readmeTxtPath = join(ROOT, 'readme.txt');
const source = readFileSync(readmeTxtPath, 'utf-8');

describe('snapshot: readme.md', () => {
	test('generates byte-identical readme.md', () => {
		const generated = readmeToMarkdown(readmeTxtPath, source);
		const committed = readFileSync(join(ROOT, 'readme.md'), 'utf-8');
		expect(generated).toBe(committed);
	});
});

describe('snapshot: src/data/readme.php', () => {
	test('generates byte-identical src/data/readme.php', () => {
		const readmeData = parseReadmeToData(source);
		const jsonString = phpJsonEncode(readmeData);
		const generated = `<?php return json_decode( '${jsonString}' );`;
		const committed = readFileSync(join(ROOT, 'src/data/readme.php'), 'utf-8');
		expect(generated).toBe(committed);
	});
});
