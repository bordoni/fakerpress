import { describe, expect, test } from 'bun:test';
import { Slimdown } from '../src/slimdown.js';

describe('Slimdown', () => {
	test('renders bold text', () => {
		const sd = new Slimdown();
		expect(sd.render('**hello**')).toBe('<p><strong>hello</strong></p>');
	});

	test('renders emphasis', () => {
		const sd = new Slimdown();
		expect(sd.render('*hello*')).toBe('<p><em>hello</em></p>');
	});

	test('renders inline code', () => {
		const sd = new Slimdown();
		expect(sd.render('use `code` here')).toBe('<p>use <code>code</code> here</p>');
	});

	test('renders links with single-quoted href', () => {
		const sd = new Slimdown();
		expect(sd.render('[text](http://example.com)')).toBe("<p><a href='http://example.com'>text</a></p>");
	});

	test('renders unordered list', () => {
		const sd = new Slimdown();
		const result = sd.render('\n* item one\n* item two');
		expect(result).toContain('<ul>');
		expect(result).toContain('<li>item one</li>');
		expect(result).toContain('<li>item two</li>');
	});

	test('removeRule removes the header rule', () => {
		const sd = new Slimdown();
		sd.removeRule('/(#+)(.*)/');
		const result = sd.render('## Header');
		// Without the header rule, ## Header should not become <h2>
		expect(result).not.toContain('<h2>');
	});

	test('renders a changelog line like PHP does', () => {
		const sd = new Slimdown();
		sd.removeRule('/(#+)(.*)/');
		const result = sd.render(' Feature - Complete REST API implementation replacing legacy AJAX system');
		expect(result).toBe('<p>Feature - Complete REST API implementation replacing legacy AJAX system</p>');
	});

	test('renders inline code inside list items', () => {
		const sd = new Slimdown();
		sd.removeRule('/(#+)(.*)/');
		const result = sd.render(' Version - Update dependency `cakephp/chronos` to `3.1.0`');
		expect(result).toBe('<p>Version - Update dependency <code>cakephp/chronos</code> to <code>3.1.0</code></p>');
	});
});
