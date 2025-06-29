<?php
namespace FakerPress\Module;
use FakerPress\Provider\Image\Placeholder;
use FakerPress\Provider\Image\LoremPicsum;
use WP_Error;
use FakerPress;

class Attachment extends Abstract_Module {

	/**
	 * Holds the key for the meta value of the original URL from where
	 * a given attachment was downloaded from.
	 *
	 * @since  0.5.0
	 *
	 * @var string
	 */
	public static $meta_key_original_url = '_fakerpress_original_url';

	protected $dependencies = [
		FakerPress\ThirdParty\Faker\Provider\Lorem::class,
		FakerPress\ThirdParty\Faker\Provider\DateTime::class,
		FakerPress\Provider\HTML::class,
		FakerPress\Provider\Image\Placeholder::class,
		FakerPress\Provider\Image\LoremPicsum::class,
	];

	protected $provider_class = FakerPress\Provider\WP_Attachment::class;

	public static function get_slug(): string {
		return 'attachments';
	}

	public function hook():void {
	}

	/**
	 * To use the Attachment Module the current user must have at least the `upload_files` permission.
	 *
	 * @since 0.6.0
	 *
	 * @return string
	 */
	public static function get_permission_required(): string {
		return 'upload_files';
	}

	/**
	 * @inheritDoc
	 */
	public static function fetch( array $args = [] ): array {
		// TODO: Implement fetch() method.
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public static function delete( $item ) {
		// TODO: Implement delete() method.
	}

	/**
	 * Handle the downloads of Attachments given a URL and Post Parent ID, which will default to 0.
	 * Currently only support images.
	 *
	 * @since  0.5.0
	 *
	 * @param  string  $url            Which URL we are using to download.
	 * @param  integer $post_parent_id Which post this will be attached to.
	 *
	 * @return int|WP_Error            Attachment ID or WP_Error.
	 */
	protected function handle_download( $url, $post_parent_id = 0 ) {
		// Include WordPress core functions, this was not present on the REST API.
		if ( ! function_exists( 'download_url' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
		}

		/**
		 * Allows filtering of the attachment download_url timeout, which is here just to
		 * prevent fakerpress timing out.
		 *
		 * @since  0.5.0
		 *
		 * @param int    $timeout         Download timeout.
		 * @param string $url             Which url we are downloading it for.
		 * @param int    $post_parent_id  Which post this will be attached to.
		 */
		$timeout = apply_filters( 'fakerpress.module.attachment.download_url_timeout', 10, $url, $post_parent_id );

		// Download temp file
		$temporary_file = download_url( $url, $timeout );

		// Check for download errors if there are error unlink the temp file name
		if ( is_wp_error( $temporary_file ) ) {
			return $temporary_file;
		}

		// First try to get mime type for standard images
		$mime_type = wp_get_image_mime( $temporary_file );
		
		// If that fails, check if it's an SVG or other file type
		if ( ! $mime_type ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime_type = finfo_file( $finfo, $temporary_file );
			finfo_close( $finfo );
			
			// If it's SVG, we need to handle it differently
			if ( $mime_type === 'image/svg+xml' || $mime_type === 'text/xml' ) {
				// Delete the SVG file as we don't want to process it
				unlink( $temporary_file );
				return new WP_Error( 'svg-not-supported', __( 'SVG images are not supported. Please use a different image provider or format.', 'fakerpress' ) );
			}
		}
		
		if ( ! $mime_type ) {
			return new WP_Error( 'empty-mimetype', __( 'Empty MimeType', 'fakerpress' ) );
		}

		$allowed_mime_types = get_allowed_mime_types();

		$extension = array_search( $mime_type, $allowed_mime_types );
		if ( $extension ) {
			$extension = explode( '|', $extension );
		}

		if ( ! $extension ) {
			return new WP_Error( 'invalid-image-mimetype', sprintf( __( 'Invalid image MimeType (%s)', 'fakerpress' ), $mime_type ) );
		}

		// Build file name with Extension.
		$filename = implode( '.', [ $this->get_faker()->uuid(), reset( $extension ) ] );

		$file = [
			'name' => $filename,
			'tmp_name' => $temporary_file,
		];

		// uploads as an attachment to WP
		$attachment_id = media_handle_sideload( $file, $post_parent_id );

		// download_url requires deleting the file
		if ( file_exists( $temporary_file ) ) {
			 unlink( $temporary_file );
		}

		/**
		* We don't want to pass something to $id
		* if there were upload errors.
		* So this checks for errors
		*/
		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $temporary_file ) ) {
				 unlink( $temporary_file );
			}
			return $attachment_id;
		}

		// Return Attachment ID
		return $attachment_id;
	}

	/**
	 * @inheritDoc
	 */
	public function filter_save_response( $response, array $data, Abstract_Module $module ) {
		if ( empty( $data['attachment_url'] ) ) {
			return false;
		}

		$attachment_id = $this->handle_download( $data['attachment_url'] );

		// Flag the Object as FakerPress
		update_post_meta( $attachment_id, static::get_flag(), 1 );

		// Add the Original URL to the meta of the attachment
		update_post_meta( $attachment_id, static::$meta_key_original_url, $data['attachment_url'] );

		return $attachment_id;
	}

	/**
	 * Gets an Array with all the providers
	 *
	 * @return array  With ID, Text and Type
	 */
	public static function get_providers(): array {
		$providers = [
			[
				'id'   => Placeholder::ID,
				'text' => esc_attr__( 'Placehold.co', 'fakerpress' ),
				'type' => 'image',
			],
			[
				'id'   => LoremPicsum::ID,
				'text' => esc_attr__( 'Lorem Picsum', 'fakerpress' ),
				'type' => 'image',
			],
		];

		return $providers;

	}

	/**
	 * @since 0.6.4
	 *
	 * @throws \Exception
	 *
	 * @param $qty
	 * @param $request
	 *
	 * @return array|string
	 */
	public function parse_request( $qty, $request = [] ) {
		// The quantity is already calculated by the REST endpoint or passed directly
		// We should use the $qty parameter, not recalculate from request

		// Process date range
		if ( isset( $request['interval_date'] ) ) {
			$date_range = [
				'min' => $request['interval_date']['min'] ?? '-30 days',
				'max' => $request['interval_date']['max'] ?? 'now',
			];
		} else {
			$date_range = [ 'min' => '-30 days', 'max' => 'now' ];
		}

		// Process author - it comes as comma-separated string from select2
		$author_ids = [ 1 ];
		if ( ! empty( $request['author'] ) ) {
			if ( is_string( $request['author'] ) ) {
				$author_ids = array_map( 'absint', explode( ',', $request['author'] ) );
			} elseif ( is_array( $request['author'] ) ) {
				$author_ids = array_map( 'absint', $request['author'] );
			}
		}

		// Process width range
		$width = [ 'min' => 200, 'max' => 1200 ];
		if ( isset( $request['width'] ) && is_array( $request['width'] ) ) {
			$width = [
				'min' => absint( $request['width']['min'] ),
				'max' => absint( $request['width']['max'] ),
			];
		}

		// Process height range
		$height = null;
		if ( isset( $request['height'] ) && is_array( $request['height'] ) ) {
			$min_height = absint( $request['height']['min'] );
			$max_height = absint( $request['height']['max'] );
			
			// Only set height if at least one value is non-zero
			if ( $min_height > 0 || $max_height > 0 ) {
				$height = [
					'min' => $min_height,
					'max' => $max_height,
				];
			}
		}

		$defaults = [
			'provider'             => $request['provider'] ?? 'placeholder',
			'width'                => $width,
			'height'               => $height,
			'aspect_ratio'         => floatval( $request['aspect_ratio'] ?? 1.5 ),
			'file_types'           => [ 'jpg' ],
			'post_parent'          => $request['post_parent'] ?? 0,
			'author_ids'           => $author_ids,
			'generate_alt_text'    => ! empty( $request['generate_alt_text'] ),
			'generate_caption'     => ! empty( $request['generate_caption'] ),
			'generate_description' => ! empty( $request['generate_description'] ),
			'date_range'           => $date_range,
			'meta'                 => $request['meta'] ?? [],
		];

		// Merge with defaults.
		$request = wp_parse_args( $request, $defaults );

		// Initialize results array.
		$results = [];

		// Use the quantity passed in - it's already calculated by the REST endpoint
		$quantity = absint( $qty );
		
		// Ensure we have at least 1
		if ( $quantity < 1 ) {
			$quantity = 1;
		}
		
		// Debug logging
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'FakerPress Attachment: Generating ' . $quantity . ' attachments' );
		}

		// Generate attachments.
		for ( $i = 0; $i < $quantity; $i++ ) {
			$attachment_data = $this->generate_attachment_data( $request );
			
			if ( ! empty( $attachment_data['attachment_url'] ) ) {
				// Handle the download and creation of the attachment
				$attachment_id = $this->handle_download( $attachment_data['attachment_url'], $attachment_data['post_parent'] ?? 0 );
				
				if ( is_wp_error( $attachment_id ) ) {
					// Log the error for debugging
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( 'FakerPress Attachment Error: ' . $attachment_id->get_error_message() . ' for URL: ' . $attachment_data['attachment_url'] );
					}
					continue;
				}
				
				if ( $attachment_id ) {
					// Flag the Object as FakerPress
					update_post_meta( $attachment_id, static::get_flag(), 1 );
					
					// Add the Original URL to the meta of the attachment
					update_post_meta( $attachment_id, static::$meta_key_original_url, $attachment_data['attachment_url'] );
					
					// Update additional fields.
					$this->update_attachment_fields( $attachment_id, $attachment_data, $request );
					
					$results[] = $attachment_id;
				}
			}
		}

		return $results;
	}

	/**
	 * Generate attachment data based on request parameters.
	 *
	 * @since TBD
	 *
	 * @param array $request Request parameters.
	 *
	 * @return array
	 */
	protected function generate_attachment_data( $request ) {
		$faker = $this->get_faker();
		
		// Determine dimensions.
		$width = $this->get_random_dimension( $request['width'], 800 );
		
		if ( $request['height'] !== null ) {
			$height = $this->get_random_dimension( $request['height'], 600 );
		} else {
			// Use aspect ratio.
			$height = round( $width / $request['aspect_ratio'] );
		}

		// Select provider.
		$provider = $request['provider'];
		$attachment_url = '';
		$image_author = null;

		switch ( $provider ) {
			case 'lorempicsum':
				// Use a specific image ID so we can fetch metadata
				$image_id = $faker->numberBetween( 0, 1084 ); // Lorem Picsum has ~1084 images
				$attachment_url = sprintf( 'https://picsum.photos/id/%d/%d/%d', $image_id, $width, $height );
				
				// Fetch image metadata to get author info
				$metadata_url = sprintf( 'https://picsum.photos/id/%d/info', $image_id );
				$response = wp_remote_get( $metadata_url, [ 'timeout' => 5 ] );
				
				if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
					$body = wp_remote_retrieve_body( $response );
					$metadata = json_decode( $body, true );
					
					if ( ! empty( $metadata['author'] ) ) {
						$image_author = $metadata['author'];
					}
				}
				break;
			case 'placeholder':
			default:
				// Add .png to get PNG format instead of SVG
				$attachment_url = sprintf( 'https://placehold.co/%dx%d.png', $width, $height );
				break;
		}

		// Generate text content.
		$data = [
			'attachment_url' => $attachment_url,
			'post_title'     => $faker->sentence( $faker->numberBetween( 3, 8 ) ),
			'post_author'    => $faker->randomElement( (array) $request['author_ids'] ),
			'post_date'      => $faker->dateTimeBetween( $request['date_range']['min'], $request['date_range']['max'] )->format( 'Y-m-d H:i:s' ),
			'image_author'   => $image_author, // Store author for later use
		];

		// Add description if requested.
		if ( $request['generate_description'] ) {
			$data['post_content'] = $faker->paragraph( $faker->numberBetween( 3, 5 ) );
		}

		// Add caption if requested.
		if ( $request['generate_caption'] ) {
			$data['post_excerpt'] = $faker->sentence( $faker->numberBetween( 10, 20 ) );
		}

		// Add parent post if specified.
		if ( ! empty( $request['post_parent'] ) ) {
			if ( is_array( $request['post_parent'] ) ) {
				$data['post_parent'] = $faker->randomElement( $request['post_parent'] );
			} else {
				$data['post_parent'] = absint( $request['post_parent'] );
			}
		} else {
			$data['post_parent'] = 0;
		}

		return $data;
	}

	/**
	 * Update attachment fields after creation.
	 *
	 * @since TBD
	 *
	 * @param int   $attachment_id The attachment ID.
	 * @param array $attachment_data The attachment data.
	 * @param array $request The request parameters.
	 *
	 * @return void
	 */
	protected function update_attachment_fields( $attachment_id, $attachment_data, $request ) {
		$faker = $this->get_faker();

		// Update post fields if needed.
		$update_data = [
			'ID'           => $attachment_id,
			'post_title'   => $attachment_data['post_title'],
			'post_content' => $attachment_data['post_content'] ?? '',
			'post_excerpt' => $attachment_data['post_excerpt'] ?? '',
			'post_author'  => $attachment_data['post_author'],
			'post_date'    => $attachment_data['post_date'],
			'post_date_gmt' => get_gmt_from_date( $attachment_data['post_date'] ),
		];

		if ( ! empty( $attachment_data['post_parent'] ) ) {
			$update_data['post_parent'] = $attachment_data['post_parent'];
		}

		wp_update_post( $update_data );

		// Add alt text if requested.
		if ( $request['generate_alt_text'] ) {
			// If we have an image author from Lorem Picsum, include attribution
			if ( ! empty( $attachment_data['image_author'] ) ) {
				$alt_text = sprintf( 
					__( '%s - Photo by %s on Unsplash', 'fakerpress' ),
					$faker->sentence( $faker->numberBetween( 3, 8 ) ),
					$attachment_data['image_author']
				);
			} else {
				$alt_text = $faker->sentence( $faker->numberBetween( 3, 8 ) );
			}
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		}

		// Add custom meta if provided.
		if ( ! empty( $request['meta'] ) && is_array( $request['meta'] ) ) {
			foreach ( $request['meta'] as $meta_key => $meta_value ) {
				update_post_meta( $attachment_id, $meta_key, $meta_value );
			}
		}
	}

	/**
	 * Get random dimension value from input.
	 *
	 * @since TBD
	 *
	 * @param mixed $dimension Dimension value or range.
	 * @param int   $default Default value.
	 *
	 * @return int
	 */
	protected function get_random_dimension( $dimension, $default = 800 ) {
		if ( is_numeric( $dimension ) ) {
			return absint( $dimension );
		}

		if ( is_array( $dimension ) || is_object( $dimension ) ) {
			$dimension = (array) $dimension;
			$min = isset( $dimension['min'] ) ? absint( $dimension['min'] ) : $default;
			$max = isset( $dimension['max'] ) ? absint( $dimension['max'] ) : $min;
			
			return $this->get_faker()->numberBetween( $min, $max );
		}

		return $default;
	}
}
