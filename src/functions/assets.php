<?php
namespace FakerPress;

/**
 * Shortcut for FakerPress\Utils\Assets::register(), include a single asset
 *
 * @since 0.5.1
 *
 * @param string            $slug      Slug to save the asset - passes through `sanitize_title_with_dashes()`.
 * @param string            $file      The asset file to load (CSS or JS), including non-minified file extension.
 * @param array             $deps      The list of dependencies.
 * @param string|array|null $action    The WordPress action(s) to enqueue on, such as `wp_enqueue_scripts`,
 *                                     `admin_enqueue_scripts`, or `login_enqueue_scripts`.
 * @param array             $arguments See `FakerPress\Assets::register()` for more info.
 *
 * @return object|false     The asset that got registered or false on error.
 */
function register_asset( $slug, $file, $deps = [], $action = null, $arguments = [] ) {
	$origin = make( Plugin::class );
	$assets = make( Utils\Assets::class );

	return $assets->register( $origin, $slug, $file, $deps, $action, $arguments );
}

/**
 * Shortcut for FakerPress\Assets::enqueue() to include assets.
 *
 * @since 0.5.1
 *
 * @param string|array $slug Slug to enqueue
 */
function enqueue_asset( $slug ) {
	$assets = make( Utils\Assets::class );

	$assets->enqueue( $slug );
}

/**
 * Shortcut for FakerPress\Assets::enqueue_group() include assets by groups.
 *
 * @since 0.5.1
 *
 * @param string|array  $group  Which group(s) should be enqueued.
 */
function enqueue_asset_group( $group ) {
	$assets = make( Utils\Assets::class );
	$assets->enqueue_group( $group );
}
