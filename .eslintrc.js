/**
 * ESLint configuration.
 *
 * Mirrors the default @wordpress/scripts config and adds the TypeScript import
 * resolver, so `import/no-unresolved` understands the `@fp/*` path alias that
 * tsconfig.json, webpack.config.js and Jest already map to src/resources/packages.
 */
module.exports = {
	root: true,
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	// Vendored third-party builds (qs, Select2 3.5); not ours to lint.
	ignorePatterns: [ 'src/resources/js/qs.js', 'src/resources/js/select2.js' ],
	settings: {
		'import/resolver': {
			typescript: {
				project: './tsconfig.json',
			},
			node: {
				extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
			},
		},
	},
	overrides: [
		{
			// Unit test files and their helpers only.
			files: [ '**/@(test|__tests__)/**/*.js', '**/?(*.)test.js' ],
			extends: [ 'plugin:@wordpress/eslint-plugin/test-unit' ],
		},
	],
};
