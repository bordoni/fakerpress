const {resolve} = require('path');
const {dirname, basename, extname} = require('path');
const {readdirSync, statSync, existsSync} = require('fs');

/**
 * The default configuration coming from the @wordpress/scripts package.
 * Customized following the "Advanced Usage" section of the documentation:
 * See: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#advanced-usage
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

const {
  createTECLegacyJs,
  createTECPostCss,
  createTECLegacyBlocksFrontendPostCss,
  createTECPackage,
  compileCustomEntryPoints,
  exposeEntry,
  doNotPrefixSVGIdsClasses,
  WindowAssignPropertiesPlugin,
} = require('@stellarwp/tyson');

/**
 * Compile a list of entry points to be compiled to the format used by WebPack to define multiple entry points.
 * This is akin to the compilation system used for multi-page applications.
 * See: https://webpack.js.org/concepts/entry-points/#multi-page-application
 */
const customEntryPoints = compileCustomEntryPoints({
  /**
   * All existing Javascript files will be compiled to ES6, most will not be changed at all,
   * minified and cleaned up.
   * This is mostly a pass-thru with the additional benefit that the compiled packages will be
   * exposed on the `window.fakerpress` object.
   * E.g. the `src/resources/js/admin-ignored-events.js` file will be compiled to
   * `/build/js/admin-ignored-events.js` and exposed on `window.fakerpress.adminIgnoredEvents`.
   */
  '/src/resources/js': createTECLegacyJs('fakerpress'),

  /**
   * Compile, recursively, the PostCSS file using PostCSS nesting rules.
   * By default, the `@wordpress/scripts` configuration would compile files using the CSS
   * nesting syntax (https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_nesting) where
   * the `&` symbol indicates the parent element.
   * The PostCSS syntax followed in TEC files will instead use the `&` symbol to mean "this element".
   * Handling this correctly requires adding a PostCSS processor specific to the PostCSS files that
   * will handle the nesting correctly.
   * Note the plugin will need to specify the following development dependencies: postcss-nested, postcss-preset-env,
   * postcss-mixins, postcss-import, postcss-custom-media.
   */
  '/src/resources/pcss': createTECPostCss('fakerpress'),

  /**
   * This deals with packages written following modern module-based approaches.
   * These packages are usually not Blocks and require `@wordpress/scripts` to be explicitly
   * instructed about them to compile correctly.
   * To avoid having to list each package, here the configuration schema is used to recursively
   * pick them up and namespace them.
   */
  '/src/resources/packages': createTECPackage('fakerpress'),
}, defaultConfig);

/**
 * Following are static entry points, to be included in the build non-recursively.
 * These are built following a modern module approach where the root `index.js` file
 * will include the whole module.
 */


/**
 * Prepends a loader for SVG files that will be applied after the default one. Loaders are applied
 * in a LIFO queue in WebPack.
 * By default, `@wordpress/scripts` uses `@svgr/webpack` to handle SVG files and, together with it,
 * the default SVGO (package `svgo/svgo-loader`) configuration that includes the `prefixIds` plugin.
 * To avoid `id` and `class` attribute conflicts, the `prefixIds` plugin would prefix all `id` and
 * `class` attributes in SVG tags with a generated prefix. This would break TEC classes (already
 * namespaced) so here we prepend a rule to handle SVG files in the `src/modules` directory by
 * disabling the `prefixIds` plugin.
 */
doNotPrefixSVGIdsClasses(defaultConfig);

/**
 * Strips Tailwind v4's :not(#\#) cascade-compatibility shims from all CSS assets.
 *
 * These shims are injected by @tailwindcss/node (via LightningCSS) as a cascade layer
 * backward-compatibility mechanism for browsers released before mid-2022. WordPress 6.4+
 * (required by FakerPress) targets modern browsers that natively support @layer, so the
 * shims are unnecessary bloat.
 *
 * This plugin runs before RtlCssPlugin (PROCESS_ASSETS_STAGE_OPTIMIZE) so that RTL variants
 * are also generated without the shims.
 */
class StripTailwindLayerHacksPlugin {
	apply( compiler ) {
		compiler.hooks.compilation.tap( 'StripTailwindLayerHacksPlugin', ( compilation ) => {
			compilation.hooks.processAssets.tap(
				{
					name: 'StripTailwindLayerHacksPlugin',
					stage: compilation.PROCESS_ASSETS_STAGE_DERIVED,
				},
				() => {
					for ( const [ filename, asset ] of Object.entries( compilation.assets ) ) {
						if ( ! filename.endsWith( '.css' ) ) {
							continue;
						}
						const src = asset.source();
						if ( ! src.includes( ':not(#' ) ) {
							continue;
						}
						const stripped = src.replace( /(:not\(#\\#\))+/g, '' );
						compilation.updateAsset(
							filename,
							new compiler.webpack.sources.RawSource( stripped )
						);
					}
				}
			);
		} );
	}
}

/**
 * Finally the customizations are merged with the default WebPack configuration.
 */
module.exports = {
  ...defaultConfig,
  ...{
    watchOptions: {
      ...defaultConfig.watchOptions,
      ignored: [
        '**/node_modules/**',
        '**/build/**',
        '**/vendor/**',
      ],
    },
    entry: (buildType) => {
      const defaultEntryPoints = defaultConfig.entry(buildType);
      const allEntries = { ...defaultEntryPoints, ...customEntryPoints };
      // Strip leading slashes from entry keys to fix webpack auto public path.
      // createTECPackage produces keys like "/admin" (from dirname("/admin/index.tsx")),
      // causing webpack to append "../" to the detected script directory.
      return Object.fromEntries(
        Object.entries(allEntries).map(([key, value]) => [key.replace(/^\//, ''), value])
      );
    },
    output: {
      ...defaultConfig.output,
      ...{
        enabledLibraryTypes: ['window'],
      },
    },
    resolve: {
      ...defaultConfig.resolve,
      alias: {
        ...(defaultConfig.resolve && defaultConfig.resolve.alias),
        '@fp': resolve(__dirname, 'src/resources/packages'),
      },
    },
    module: defaultConfig.module,
    plugins: [
      ...defaultConfig.plugins,
      new WindowAssignPropertiesPlugin(),
      new StripTailwindLayerHacksPlugin(),
    ],
  },
};
