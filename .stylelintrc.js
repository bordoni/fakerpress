/**
 * Stylelint config for `bun run lint:css` (`wp-scripts lint-style`).
 *
 * A JS file rather than `.stylelintrc.json` so every override can carry a comment
 * (stylelint parses JSON configs strictly, without comments).
 *
 * Base: the same `scss-stylistic` preset `wp-scripts` uses when a project has no config
 * of its own. The SCSS parser also handles the postcss-nested syntax in the legacy
 * `src/resources/pcss/*.pcss` files (`&` means "this element").
 *
 * Build output (`build/`) and `vendor/` stay ignored through wp-scripts' default
 * `.stylelintignore` (used while the project has none of its own).
 */
module.exports = {
	extends: '@wordpress/stylelint-config/scss-stylistic',
	rules: {
		// Same as the wp-scripts default config: class names come from WordPress core,
		// jQuery UI, Select2 and Tailwind, none of which follow one pattern.
		'selector-class-pattern': null,

		// Tailwind v4 at-rules used by `src/resources/packages/admin/styles/globals.css`
		// (@config, @source, @theme, @custom-variant), plus the rest of the v4 set.
		// `@tailwindcss/postcss` consumes them at build time.
		'scss/at-rule-no-unknown': [
			true,
			{
				ignoreAtRules: [
					'config',
					'source',
					'theme',
					'utility',
					'variant',
					'custom-variant',
					'apply',
					'reference',
					'plugin',
				],
			},
		],

		// Tailwind's @config and @source directives sit above its @import lines. They are
		// build-time directives that emit no CSS, so they don't invalidate the imports.
		'no-invalid-position-at-import-rule': [
			true,
			{
				ignoreAtRules: [ 'config', 'source' ],
			},
		],

		// Dashicons is WordPress's icon font. A generic fallback family would render
		// the glyph's code point as a letter instead of the icon.
		'font-family-no-missing-generic-family-keyword': [
			true,
			{
				ignoreFontFamilies: [ 'dashicons' ],
			},
		],
	},
	overrides: [
		{
			// Legacy jQuery UI, Select2 and datepicker styles rely on source order:
			// reordering rules to satisfy this rule would change the cascade.
			files: [ 'src/resources/pcss/**/*.pcss' ],
			rules: {
				'no-descending-specificity': null,
			},
		},
	],
};
