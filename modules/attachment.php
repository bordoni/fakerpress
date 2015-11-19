<?php
namespace FakerPress\Module;
use FakerPress;

class Attachment extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
		'\Faker\Provider\PlaceHoldIt',
		'\Faker\Provider\UnsplashIt',
		'\Faker\Provider\LoremPixel',
		'\Faker\Provider\Image500px',
	);

	public $provider = '\Faker\Provider\WP_Attachment';

	public $page = false;

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 3 );
	}

	public function do_save( $return_val, $data, $module ) {
		if ( empty( $data['attachment_url'] ) ){
			return false;
		}

		$response = wp_remote_get( $data['attachment_url'], array( 'timeout' => 5 ) );

		if( is_wp_error( $response ) ){
			return false;
		}

		$bits = wp_remote_retrieve_body( $response );

		$filename = $this->faker->uuid() . '.jpg';
		$upload = wp_upload_bits( $filename, null, $bits );

		$data['guid'] = $upload['url'];
		$data['post_mime_type'] = 'image/jpeg';

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $data, $upload['file'], 0 );

		if ( ! is_numeric( $attach_id ) ){
			return false;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ){
			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		// Generate the metadata for the attachment, and update the database record.
		update_post_meta( $attach_id, '_wp_attachment_metadata', wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );

		// Flag the Object as FakerPress
		update_post_meta( $attach_id, self::$flag, 1 );

		return $attach_id;
	}
}
