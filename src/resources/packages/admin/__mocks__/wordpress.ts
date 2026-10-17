/**
 * WordPress global mocks for Jest tests.
 *
 * Auto-imported via Jest setupFiles.
 * Sets window.fakerpressPageConfig so React components
 * can be tested without a WordPress runtime.
 *
 * Note: @wordpress/api-fetch and @wordpress/i18n are mocked
 * via moduleNameMapper in package.json, not here.
 */

// Polyfill ResizeObserver for Radix UI components.
class ResizeObserverMock {
	observe() {}
	unobserve() {}
	disconnect() {}
}

window.ResizeObserver = window.ResizeObserver || ResizeObserverMock;

// Mock window.fakerpressPageConfig with test defaults.
Object.defineProperty( window, 'fakerpressPageConfig', {
	writable: true,
	value: {
		page: 'test',
		restRoot: 'http://localhost/wp-json/',
		restNonce: 'test-nonce-123',
		ajaxUrl: 'http://localhost/wp-admin/admin-ajax.php',
		ajaxNonces: {},
		data: {},
	},
} );
