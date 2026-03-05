import type { PageConfig } from '../lib/types';

declare global {
	interface Window {
		fakerpressPageConfig?: PageConfig;
	}
}

/**
 * Read the page configuration injected via wp_localize_script.
 *
 * @return PageConfig The page configuration object.
 */
export function usePageConfig(): PageConfig {
	const config = window.fakerpressPageConfig;

	if ( ! config ) {
		throw new Error(
			'fakerpressPageConfig is not defined. Ensure wp_localize_script is called before this component renders.'
		);
	}

	return config;
}
