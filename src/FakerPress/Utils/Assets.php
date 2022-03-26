<?php
namespace FakerPress\Utils;

use function FakerPress\get;
use function FakerPress\is_truthy;

/**
 * Class used to register and enqueue assets across our plugins.
 *
 * @since  0.5.1
 */
class Assets {
	/**
	 * Stores all the Assets and it's configurations.
	 *
	 * @since  0.5.1
	 *
	 * @var array
	 */
	protected $assets = [];

	/**
	 * Stores the localized scripts for reference.
	 *
	 * @since  0.5.1
	 *
	 * @var array
	 */
	private $localized = [];

	/**
	 * Register the Methods in the correct places.
	 *
	 * @since  0.5.1
	 */
	public function __construct() {
		// Hook the actual registering of.
		add_action( 'init', [ $this, 'register_in_wp' ], 1, 0 );
	}

	/**
	 * Register the Assets on the correct hooks.
	 *
	 * @since  0.5.1
	 *
	 * @return void
	 */
	public function register_in_wp( $assets = null ) {
		if ( is_null( $assets ) ) {
			$assets = $this->assets;
		}

		if ( ! is_array( $assets ) ) {
			$assets = [ $assets ];
		}

		uasort( $assets, 'FakerPress\sort_by_priority' );

		foreach ( $assets as $asset ) {
			// Asset is already registered.
			if ( $asset->is_registered ) {
				continue;
			}

			if ( 'js' === $asset->type ) {
				// Script is already registered.
				if ( wp_script_is( $asset->slug, 'registered' ) ) {
					continue;
				}

				wp_register_script( $asset->slug, $asset->url, $asset->deps, $asset->version, $asset->in_footer );

				// Register that this asset is actually registered on the WP methods.
				$asset->is_registered = wp_script_is( $asset->slug, 'registered' );
			} else {
				// Style is already registered.
				if ( wp_style_is( $asset->slug, 'registered' ) ) {
					continue;
				}

				wp_register_style( $asset->slug, $asset->url, $asset->deps, $asset->version, $asset->media );

				// Register that this asset is actually registered on the WP methods.
				$asset->is_registered = wp_style_is( $asset->slug, 'registered' );
			}

			// If we don't have an action we don't even register the action to enqueue.
			if ( empty( $asset->action ) ) {
				continue;
			}

			// Now add an action to enqueue the registered assets.
			foreach ( (array) $asset->action as $action ) {
				// Enqueue the registered assets at the appropriate time.
				if ( did_action( $action ) > 0 ) {
					$this->enqueue();
				} else {
					add_action( $action, [ $this, 'enqueue' ], $asset->priority );
				}
			}
		}
	}

	/**
	 * Enqueues registered assets based on their groups.
	 *
	 * @since  0.5.1
	 *
	 * @uses  Assets::enqueue()
	 *
	 * @param string|array $groups Which groups will be enqueued.
	 */
	public function enqueue_group( $groups ) {
		$assets = $this->get();
		$enqueue = [];

		foreach ( $assets as $asset ) {
			if ( empty( $asset->groups ) ) {
				continue;
			}

			$intersect = array_intersect( (array) $groups, $asset->groups );

			if ( empty( $intersect ) ) {
				continue;
			}

			$enqueue[] = $asset->slug;
		}

		$this->enqueue( $enqueue );
	}

	/**
	 * Enqueues registered assets.
	 *
	 * This method is called on whichever action (if any) was declared during registration.
	 *
	 * It can also be called directly with a list of asset slugs to forcibly enqueue, which may be
	 * useful where an asset is required in a situation not anticipated when it was originally
	 * registered.
	 *
	 * @since  0.5.1
	 *
	 * @param string|array $forcibly_enqueue
	 */
	public function enqueue( $forcibly_enqueue = null ) {
		$forcibly_enqueue = array_filter( (array) $forcibly_enqueue );
		$assets = $this->get();

		foreach ( $assets as $asset ) {
			// Should this asset be enqueued regardless of the current filter/any conditional requirements?
			$must_enqueue = in_array( $asset->slug, $forcibly_enqueue );
			$in_filter    = in_array( current_filter(), (array) $asset->action );

			// Skip if we are not on the correct filter (unless we are forcibly enqueuing).
			if ( ! $in_filter && ! $must_enqueue ) {
				continue;
			}

			// If any single conditional returns true, then we need to enqueue the asset.
			if ( empty( $asset->action ) && ! $must_enqueue ) {
				continue;
			}

			// If this asset was late called
			if ( ! $asset->is_registered ) {
				$this->register_in_wp( $asset );
			}

			// Default to enqueuing the asset if there are no conditionals,
			// and default to not enqueuing it if there *are* conditionals.
			$enqueue = empty( $asset->conditionals );

			if ( ! $enqueue ) {
				// Reset Enqueue.
				$enqueue = [];

				// Which is the operator?
				$conditional_operator = get( $asset->conditionals, 'operator', 'OR' );

				// If we have a set of conditionals we loop on then and get if they are true.
				foreach ( $asset->conditionals as $key => $conditional ) {
					// Avoid doing anything to the operator
					if ( 'operator' === $key ) {
						continue;
					}

					$enqueue[] = call_user_func( $conditional );
				}

				// By default we use OR for backwards compatibility.
				if ( 'OR' === $conditional_operator ) {
					$enqueue = in_array( true, $enqueue );
				} else {
					$enqueue = ! in_array( false, $enqueue );
				}
			}

			/**
			 * Allows developers to hook-in and prevent an asset from been loaded.
			 *
			 * @since  0.5.1
			 *
			 * @param bool   $enqueue If we should enqueue or not a given asset.
			 * @param object $asset   Which asset we are dealing with.
			 */
			$enqueue = apply_filters( 'fakerpress_asset_enqueue', $enqueue, $asset );

			/**
			 * Allows developers to hook-in and prevent an asset from been loaded
			 *
			 * @since 0.5.1
			 *
			 * @param bool   $enqueue If we should enqueue or not a given asset.
			 * @param object $asset   Which asset we are dealing with.
			 */
			$enqueue = apply_filters( "fakerpress_asset_enqueue_{$asset->slug}", $enqueue, $asset );

			if ( ! $enqueue && ! $must_enqueue ) {
				continue;
			}

			if ( 'js' === $asset->type ) {
				wp_enqueue_script( $asset->slug );

				// Only localize on JS and if we have data.
				if ( ! empty( $asset->localize ) ) {
					// Makes sure we have an Array of Localize data.
					if ( is_object( $asset->localize ) ) {
						$localization = [ $asset->localize ];
					} else {
						$localization = (array) $asset->localize;
					}

					/**
					 * Check to ensure we haven't already localized it before.
					 *
					 * @since 0.5.1
					 */
					foreach ( $localization as $localize ) {
						if ( in_array( $localize->name, $this->localized ) ) {
							continue;
						}

						// If we have a Callable as the Localize data we execute it.
						if ( is_callable( $localize->data ) ) {
							$localize->data = call_user_func( $localize->data, $asset );
						}

						wp_localize_script( $asset->slug, $localize->name, $localize->data );
						$this->localized[] = $localize->name;
					}
				}
			} else {
				wp_enqueue_style( $asset->slug );
			}

			$asset->already_enqueued = true;
		}
	}

	/**
	 * Returns the path to a minified version of a js or css file, if it exists.
	 * If the file does not exist, returns false.
	 *
	 * @since  0.5.1
	 *
	 * @param string $url The absolute URL to the un-minified file.
	 *
	 * @return string|false The url to the minified version or false, if file not found.
	 */
	public static function maybe_get_min_file( $url ) {
		static $wpmu_plugin_url;
		static $wp_plugin_url;
		static $wp_content_url;
		static $plugins_url;
		static $base_dirs;

		$urls = [];
		if ( ! isset( $wpmu_plugin_url ) ) {
			$wpmu_plugin_url = set_url_scheme( WPMU_PLUGIN_URL );
		}

		if ( ! isset( $wp_plugin_url ) ) {
			$wp_plugin_url = set_url_scheme( WP_PLUGIN_URL );
		}

		if ( ! isset( $wp_content_url ) ) {
			$wp_content_url = set_url_scheme( WP_CONTENT_URL );
		}

		if ( ! isset( $plugins_url ) ) {
			$plugins_url = plugins_url();
		}

		if ( ! isset( $base_dirs ) ) {
			$base_dirs[ WPMU_PLUGIN_DIR ] = wp_normalize_path( WPMU_PLUGIN_DIR );
			$base_dirs[ WP_PLUGIN_DIR ]   = wp_normalize_path( WP_PLUGIN_DIR );
			$base_dirs[ WP_CONTENT_DIR ]  = wp_normalize_path( WP_CONTENT_DIR );
		}

		if ( 0 === strpos( $url, $wpmu_plugin_url ) ) {
			// URL inside WPMU plugin dir.
			$base_dir = $base_dirs[ WPMU_PLUGIN_DIR ];
			$base_url = $wpmu_plugin_url;
		} elseif ( 0 === strpos( $url, $wp_plugin_url ) ) {
			// URL inside WP plugin dir.
			$base_dir = $base_dirs[ WP_PLUGIN_DIR ];
			$base_url = $wp_plugin_url;
		} elseif ( 0 === strpos( $url, $wp_content_url ) ) {
			// URL inside WP content dir.
			$base_dir = $base_dirs[ WP_CONTENT_DIR ];
			$base_url = $wp_content_url;
		} elseif ( 0 === strpos( $url, $plugins_url ) ) {
			$base_dir = $base_dirs[ WP_PLUGIN_DIR ];
			$base_url = $plugins_url;
		} else {
			// Resource needs to be inside wp-content or a plugins dir.
			return false;
		}

		$script_debug = defined( 'SCRIPT_DEBUG' ) && is_truthy( SCRIPT_DEBUG );

		// Strip the plugin URL and make this relative.
		$relative_location = str_replace( $base_url, '', $url );

		if ( $script_debug ) {
			// Add the actual url after having the min file added.
			$urls[] = $relative_location;
		}

		// If needed add the Min Files.
		if ( substr( $relative_location, -3, 3 ) === '.js' ) {
			$urls[] = substr_replace( $relative_location, '.min', - 3, 0 );
		} elseif ( substr( $relative_location, -4, 4 ) === '.css' ) {
			$urls[] = substr_replace( $relative_location, '.min', - 4, 0 );
		}

		if ( ! $script_debug ) {
			// Add the actual url after having the min file added.
			$urls[] = $relative_location;
		}

		// Check for all Urls added to the array.
		foreach ( $urls as $partial_path ) {
			$file_path = wp_normalize_path( $base_dir . $partial_path );
			$file_url  = $base_url . $partial_path;

			if ( file_exists( $file_path ) ) {
				return $file_url;
			}
		}

		// If we don't have any real file return false.
		return false;
	}

	public function get_resource_url( $asset = null ) {
		$extension = pathinfo( $asset->file, PATHINFO_EXTENSION );
		$is_vendor = strpos( $asset->file, 'vendor/' ) !== false ? true : false;
		$resource_path = 'src/resources/';

		if ( ! $is_vendor ) {
			switch ( $extension ) {
				case 'css':
					$resource_path .= 'css/';
					break;
				case 'js':
					$resource_path .= 'js/';
					break;
				case 'scss':
					$resource_path .= 'scss/';
					break;
				case 'pcss':
					$resource_path .= 'pcss/';
					break;
			}
		}

		$resource = $resource_path . $asset->file;
		$file     = wp_normalize_path( $asset->origin_path . $resource );

		// Turn the Path into a URL
		$url = plugins_url( basename( $asset->file ), $file );

		/**
		 * Filters the resource URL
		 *
		 * @since  0.5.1
		 *
		 * @param $url
		 * @param $resource
		 * @param $asset
		 */
		$url = apply_filters( 'fakerpress_asset_resource_url', $url, $resource, $asset );

		return $url;
	}

	/**
	 * Register an Asset and attach a callback to the required action to display it correctly.
	 *
	 * @since  0.5.1
	 *
	 * @param object            $origin    The main object for the plugin you are enqueueing the asset for.
	 * @param string            $slug      Slug to save the asset - passes through `sanitize_title_with_dashes()`.
	 * @param string            $file      The asset file to load (CSS or JS), including non-minified file extension.
	 * @param array             $deps      The list of dependencies.
	 * @param string|array|null $action    The WordPress action(s) to enqueue on, such as `wp_enqueue_scripts`,
	 *                                     `admin_enqueue_scripts`, or `login_enqueue_scripts`.
	 * @param string|array      $arguments {
	 *     Optional. Array or string of parameters for this asset.
	 *
	 *     @type array|string|null  $action         The WordPress action(s) this asset will be enqueued on.
	 *     @type int                $priority       Priority in which this asset will be loaded on the WordPress action.
	 *     @type string             $file           The relative path to the File that will be enqueued, uses the $origin to get the full path.
	 *     @type string             $type           Asset Type, `js` or `css`.
	 *     @type array              $deps           An array of other asset as dependencies.
	 *     @type string             $version        Version number, used for cache expiring.
	 *     @type string             $media          Used only for CSS, when to load the file.
	 *     @type bool               $in_footer      A boolean determining if the javascript should be loaded on the footer.
	 *     @type array|object       $localize       {
	 *          Variables needed on the JavaScript side.
	 *
	 *          @type string       $name     Name of the JS variable.
	 *          @type string|array $data     Contents of the JS variable.
	 *     }
	 *     @type callable[]   $conditionals   An callable method or an array of them, that will determine if the asset is loaded or not.
	 * }
	 *
	 * @return object|false The registered object or false on error.
	 */
	public function register( $origin, $slug, $file, $deps = [], $action = null, $arguments = [] ) {
		// Prevent weird stuff here.
		$slug = sanitize_title_with_dashes( $slug );

		if ( $this->exists( $slug ) ) {
			return $this->get( $slug );
		}

		if ( is_string( $origin ) ) {
			// Origin needs to be a class with a `instance` method and a Version constant.
			if ( class_exists( $origin ) && method_exists( $origin, 'instance' ) && defined( $origin . '::VERSION' ) ) {
				$origin = call_user_func( [ $origin, 'instance' ] );
			}
		}

		if ( is_object( $origin ) ) {
			$origin_name = get_class( $origin );

			if ( ! defined( $origin_name . '::VERSION' ) ) {
				// If we have a Object and we don't have instance or version.
				return false;
			}
		} else {
			return false;
		}

		// Fetches the version on the Origin Version constant.
		$version = constant( $origin_name . '::VERSION' );

		// Default variables to prevent notices.
		$defaults = [
			'slug'          => null,
			'file'          => false,
			'url'           => false,
			'action'        => null,
			'priority'      => 10,
			'type'          => null,
			'deps'          => [],
			'groups'        => [],
			'version'       => $version,
			'media'         => 'all',
			'in_footer'     => true,
			'is_registered' => false,
			'origin_path'   => null,
			'origin_url'    => null,
			'origin_name'   => null,

			// Bigger Variables at the end.
			'localize'      => [],
			'conditionals'  => [],
		];

		// Merge Arguments.
		$asset = (object) wp_parse_args( $arguments, $defaults );

		// Enforce these one.
		$asset->slug        = $slug;
		$asset->file        = $file;
		$asset->deps        = $deps;
		$asset->action      = $action;
		$asset->origin_file = $origin::$_file;
		$asset->origin_path = trailingslashit( ! empty( $origin->plugin_path ) ? $origin->plugin_path : $origin->path() );
		$asset->origin_name = $origin_name;

		// Origin URL might throw notices so we double check.
		$asset->origin_url  = trailingslashit( ! empty( $origin->plugin_url ) ? $origin->plugin_url : $origin->url() );

		// If we don't have a type on the arguments we grab from the File path.
		if ( is_null( $asset->type ) ) {
			if ( substr( $asset->file, -3, 3 ) === '.js' ) {
				$asset->type = 'js';
			} elseif ( substr( $asset->file, -4, 4 ) === '.css' ) {
				$asset->type = 'css';
			}
		}

		// If asset type is wrong don't register.
		if ( ! in_array( $asset->type, [ 'js', 'css' ], true ) ) {
			return false;
		}

		/**
		 * Filter to change version number on assets.
		 *
		 * @since 0.5.1
		 *
		 * @param string $version
		 * @param object $asset
		 */
		$asset->version = apply_filters( 'fakerpress_asset_version', $asset->version, $asset );

		// Clean these
		$asset->priority  = absint( $asset->priority );
		$asset->in_footer = (bool) $asset->in_footer;
		$asset->media     = esc_attr( $asset->media );

		// Ensures that we have a priority over 1.
		if ( $asset->priority < 1 ) {
			$asset->priority = 1;
		}

		// Setup the actual URL.
		if ( filter_var( $asset->file, FILTER_VALIDATE_URL ) ) {
			$asset->url = $asset->file;
		} else {
			$asset->url = $this->maybe_get_min_file( $this->get_resource_url( $asset ) );
		}

		// Parse the Localize asset arguments.
		$asset = $this->parse_argument_localize( $asset );

		// Looks for a single conditional callable and places it in an Array.
		if ( ! empty( $asset->conditionals ) && is_callable( $asset->conditionals ) ) {
			$asset->conditionals = [ $asset->conditionals ];
		}

		// Groups is always an array of unique strings.
		if ( ! empty( $asset->groups ) ) {
			$asset->groups = (array) $asset->groups;
			$asset->groups = array_filter( $asset->groups, 'is_string' );
			$asset->groups = array_unique( $asset->groups );
		}

		/**
		 * Filter an Asset loading variables.
		 *
		 * @since  0.5.1
		 *
		 * @param object $asset
		 */
		$asset = apply_filters( 'fakerpress_asset_pre_register', $asset );

		// Set the Asset on the array of notices.
		$this->assets[ $slug ] = $asset;

		// Sorts by priority.
		uasort( $this->assets, 'FakerPress\sort_by_priority' );

		// Return the Slug because it might be modified.
		return $asset;
	}

	/**
	 * Parse the localize argument for a given asset object.
	 *
	 * @since  0.5.1
	 *
	 * @param  stdClass $asset Argument that set that asset.
	 *
	 * @return stdClass
	 */
	public function parse_argument_localize( \stdClass $asset ) {
		if ( empty( $asset->localize ) ) {
			return $asset;
		}

		if ( ! is_array( $asset->localize ) && ! is_object( $asset->localize ) ) {
			return $asset;
		}

		// Cast to array for safety.
		$asset->localize = (array) $asset->localize;

		// Allow passing of a single instance.
		if ( ! empty( $asset->localize['name'] ) ) {
			// Reset to empty when name was not empty data was not set.
			$asset->localize = ! isset( $asset->localize['data'] ) ? [] : [ (object) $asset->localize ];
		}

		// Cast all instances as object.
		$asset->localize = array_map( function( $values ) {
			return (object) $values;
		}, $asset->localize );

		return $asset;
	}

	/**
	 * Removes an Asset from been registered and enqueue.
	 *
	 * @since 0.5.1
	 *
	 * @param  string $slug Slug of the Asset.
	 *
	 * @return bool
	 */
	public function remove( $slug ) {
		if ( ! $this->exists( $slug ) ) {
			return false;
		}

		unset( $this->assets[ $slug ] );
		return true;
	}

	/**
	 * Get the Asset Object configuration.
	 *
	 * @since  0.5.1
	 *
	 * @param string $slug Slug of the Asset.
	 *
	 * @return array|object|null Array of asset objects, single asset object, or null if looking for a single asset but
	 *                           it was not in the array of objects.
	 */
	public function get( $slug = null ) {
		uasort( $this->assets, 'FakerPress\sort_by_priority' );

		if ( is_null( $slug ) ) {
			return $this->assets;
		}

		// Prevent weird stuff here.
		$slug = sanitize_title_with_dashes( $slug );

		if ( ! empty( $this->assets[ $slug ] ) ) {
			return $this->assets[ $slug ];
		}

		return null;
	}

	/**
	 * Checks if an Asset exists.
	 *
	 * @since  0.5.1
	 *
	 * @param  string $slug Slug of the Asset.
	 *
	 * @return bool
	 */
	public function exists( $slug ) {
		return is_object( $this->get( $slug ) ) ? true : false;
	}
}
