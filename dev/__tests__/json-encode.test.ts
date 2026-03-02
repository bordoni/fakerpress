import { describe, expect, test } from 'bun:test';
import { phpJsonEncode } from '../src/json-encode.js';

describe('phpJsonEncode', () => {
	test('escapes < and > to hex sequences', () => {
		expect(phpJsonEncode({ a: '<b>' })).toBe('{"a":"\\u003Cb\\u003E"}');
	});

	test('escapes & to hex sequence', () => {
		expect(phpJsonEncode({ a: 'x&y' })).toBe('{"a":"x\\u0026y"}');
	});

	test('escapes single quotes', () => {
		expect(phpJsonEncode({ a: "it's" })).toBe('{"a":"it\\u0027s"}');
	});

	test('escapes forward slashes', () => {
		expect(phpJsonEncode({ a: 'a/b' })).toBe('{"a":"a\\/b"}');
	});

	test('handles nested objects', () => {
		const result = phpJsonEncode({ a: { b: "x'<y>" } });
		expect(result).toBe('{"a":{"b":"x\\u0027\\u003Cy\\u003E"}}');
	});

	test('handles arrays', () => {
		const result = phpJsonEncode(['a', "b'c"]);
		expect(result).toBe('["a","b\\u0027c"]');
	});

	test('preserves unicode characters', () => {
		const result = phpJsonEncode({ a: '\u00e9' });
		expect(result).toBe('{"a":"é"}');
	});

	test('handles null values', () => {
		expect(phpJsonEncode({ a: null })).toBe('{"a":null}');
	});

	test('handles empty string', () => {
		expect(phpJsonEncode({ a: '' })).toBe('{"a":""}');
	});
});
