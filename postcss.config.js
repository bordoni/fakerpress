module.exports = {
	plugins: [
		require( '@tailwindcss/postcss' ),
		/*
		 * Tailwind v4 + shadcn/ui generates complex :is() selectors (e.g. group-data-* variants)
		 * that postcss-is-pseudo-class cannot safely expand. Configuring onComplexSelector: 'ignore'
		 * silences the warning and leaves the already-correct :is() selectors in place.
		 * This plugin is a transitive dep of postcss-preset-env — no extra install needed.
		 */
		require( '@csstools/postcss-is-pseudo-class' )( {
			onComplexSelector: 'ignore',
		} ),
	],
};
