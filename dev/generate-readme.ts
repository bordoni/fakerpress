#!/usr/bin/env bun

import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { readmeToMarkdown } from './src/readme-to-markdown.js';
import { parseReadmeToData } from './src/readme-to-data.js';
import { phpJsonEncode } from './src/json-encode.js';

try {
	// Walk up from CWD to find readme.txt
	let readmeTxtPath: string | null = null;
	let dir = process.cwd();

	while (true) {
		for (const filename of ['readme.txt', 'README.txt']) {
			const candidate = join(dir, filename);
			if (existsSync(candidate)) {
				readmeTxtPath = resolve(candidate);
				break;
			}
		}

		if (readmeTxtPath) break;

		const parent = dirname(dir);
		if (parent === dir) break;
		dir = parent;
	}

	if (!readmeTxtPath) {
		throw new Error('Failed to find a readme.txt or README.txt above the current working directory.');
	}

	const readmeRoot = dirname(readmeTxtPath);
	const readmeMdPath = readmeTxtPath.replace(/txt$/, 'md');
	const source = readFileSync(readmeTxtPath, 'utf-8');

	// Generate readme.md
	const markdown = readmeToMarkdown(readmeTxtPath, source);
	writeFileSync(readmeMdPath, markdown);
	process.stderr.write('Successfully converted WordPress README to Markdown\n');
	process.stdout.write(readmeMdPath + '\n');

	// Generate src/data/readme.php
	const readmeData = parseReadmeToData(source);
	const jsonString = phpJsonEncode(readmeData);
	const phpContents = `<?php return json_decode( '${jsonString}' );`;
	const changelogPhpPath = join(readmeRoot, 'src/data/readme.php');
	writeFileSync(changelogPhpPath, phpContents);
	process.stderr.write('Successfully converted WordPress README to PHP data\n');
	process.stdout.write(changelogPhpPath + '\n');
} catch (e) {
	process.stderr.write((e as Error).message + '\n');
	process.exit(1);
}
