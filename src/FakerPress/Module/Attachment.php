<?php
namespace FakerPress\Module;
use FakerPress\Provider\Image\Placeholder;
use FakerPress\Provider\Image\LoremPicsum;
use WP_Error;
use FakerPress;
use Faker;

class Attachment extends Abstract_Module {

	/**
	 * Holds the key for the meta value of the original URL from where
	 * a given attachment was downloaded from.
	 *
	 * @since  0.5.0
	 *
	 * @var string
	 */
	public static $meta_key_original_url = '_fakerpress_orginal_url';

	protected $dependencies = [
		Faker\Provider\Lorem::class,
		Faker\Provider\DateTime::class,
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

		$mime_type = wp_get_image_mime( $temporary_file );
		if ( ! $mime_type ) {
			return new WP_Error( 'invalid-image-mimetype', __( 'Invalid image MimeType', 'fakerpress' ) );
		}

		$allowed_mime_types = get_allowed_mime_types();

		$extension = array_search( $mime_type, $allowed_mime_types );
		if ( $extension ) {
			$extension = explode( '|', $extension );
		}

		if ( ! $extension ) {
			return new WP_Error( 'invalid-image-mimetype', __( 'Invalid image MimeType', 'fakerpress' ) );
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
		@unlink( $temporary_file );

		/**
		* We don't want to pass something to $id
		* if there were upload errors.
		* So this checks for errors
		*/
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $temporary_file );
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
	 * Gets an Array with all the providers based on a given Type
	 *
	 * @param  string $type Which type of provider you are looking for
	 *
	 * @return array  With ID, Text and Type
	 */
	public static function get_providers( $type = 'image' ) {
		$providers = [
			[
				'id'   => Placeholder::ID,
				'text' => esc_attr__( 'Placeholder.com', 'fakerpress' ),
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
	 * @since TBD
	 *
	 * @throws \Exception
	 *
	 * @param $request
	 * @param $qty
	 *
	 * @return array|string
	 */
	public function parse_request( $qty, $request = [] ) {

	}
}
