import { createHash } from 'node:crypto';

/**
 * Slimdown - A very basic regex-based Markdown parser.
 *
 * Port of dev/src/Utils/Slimdown.php
 *
 * Author: Johnny Broadway <johnny@johnnybroadway.com>
 * Website: https://gist.github.com/jbroadway/2836900
 * License: MIT
 */

type Rule = [RegExp, string | ((match: string, ...args: string[]) => string)];

export class Slimdown {
	private codeValues: Record<string, string> = {};

	private rules: Rule[] = [
		[/`(.*?)`/g, (match: string, p1: string) => this.code(p1)],
		[/(#+)(.*)/g, (match: string, p1: string, p2: string) => this.header(p1, p2)],
		[/\[([^\[]+)\]\(([^\)]+)\)/g, '<a href=\'$2\'>$1</a>'],
		[/(\*\*|__)(.*?)\1/g, '<strong>$2</strong>'],
		[/(\*|_)(.*?)\1/g, '<em>$2</em>'],
		[/\~\~(.*?)\~\~/g, '<del>$1</del>'],
		[/\:\"(.*?)\"\:/g, '<q>$1</q>'],
		[/\n\*(.*)/g, (match: string, p1: string) => this.ulList(p1)],
		[/\n[0-9]+\.(.*)/g, (match: string, p1: string) => this.olList(p1)],
		[/\n(&gt;|\>)(.*)/g, (match: string, p1: string, p2: string) => this.blockquote(p2)],
		[/\n-{5,}/g, '\n<hr />'],
		[/\n([^\n]+)\n/g, (match: string, p1: string) => this.para(p1)],
		[/<\/ul>\s?<ul>/g, ''],
		[/<\/ol>\s?<ol>/g, ''],
		[/<\/blockquote><blockquote>/g, '\n'],
		[/<\/code><code>/g, ''],
		[/<code>(.*?)<\/code>/g, (match: string, p1: string) => this.codeContents(p1)],
	];

	private code(item: string): string {
		const key = createHash('md5').update(item).digest('hex');
		this.codeValues[key] = item;
		return `<code>${key}</code>`;
	}

	private codeContents(key: string): string {
		return `<code>${this.codeValues[key]}</code>`;
	}

	private para(line: string): string {
		const trimmed = line.trim();
		if (/^<\/?(ul|ol|li|h|p|bl)/.test(trimmed)) {
			return `\n${line}\n`;
		}
		return `\n<p>${trimmed}</p>\n`;
	}

	private ulList(item: string): string {
		return `\n<ul>\n\t<li>${item.trim()}</li>\n</ul>`;
	}

	private olList(item: string): string {
		return `\n<ol>\n\t<li>${item.trim()}</li>\n</ol>`;
	}

	private blockquote(item: string): string {
		return `\n<blockquote>${item.trim()}</blockquote>`;
	}

	private header(chars: string, header: string): string {
		const level = chars.length;
		return `<h${level}>${header.trim()}</h${level}>`;
	}

	removeRule(pattern: string): boolean {
		const index = this.rules.findIndex(([regex]) => regex.source === new RegExp(pattern.slice(1, pattern.lastIndexOf('/')), '').source);
		if (index === -1) {
			return false;
		}
		this.rules.splice(index, 1);
		return true;
	}

	render(text: string): string {
		this.codeValues = {};
		text = `\n${text}\n`;
		for (const [regex, replacement] of this.rules) {
			// Reset lastIndex for global regexes
			regex.lastIndex = 0;
			if (typeof replacement === 'function') {
				text = text.replace(regex, replacement as (...args: string[]) => string);
			} else {
				text = text.replace(regex, replacement);
			}
		}
		return text.trim();
	}
}
