<?php
namespace FakerPress\Module;
use FakerPress;

class Attachment extends Base {

	public $dependencies = array(
		'\Faker\Provider\Lorem',
		'\Faker\Provider\DateTime',
		'\Faker\Provider\HTML',
		'\Faker\Provider\PlaceHoldIt',
		'\Faker\Provider\LoremPixel',
		'\Faker\Provider\Image500px',
	);

	public $provider = '\Faker\Provider\WP_Attachment';

	public $page = false;

	public function init() {
		add_filter( "fakerpress.module.{$this->slug}.save", array( $this, 'do_save' ), 10, 4 );
	}

	public function do_save( $return_val, $params, $metas, $module ){
		if ( empty( $params['attachment_url'] ) ){
			return false;
		}

		$bits = file_get_contents( $params['attachment_url'] );
		$filename = $this->faker->uuid() . '.jpg';
		$upload = wp_upload_bits( $filename, null, $bits );

		$params['guid'] = $upload['url'];
		$params['post_mime_type'] = 'image/jpeg';

		// Insert the attachment.
		$attach_id = wp_insert_attachment( $params, $upload['file'], 0 );

		if ( ! is_numeric( $attach_id ) ){
			return false;
		}

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ){
			// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		// Generate the metadata for the attachment, and update the database record.
		$metas['_wp_attachment_metadata'] = wp_generate_attachment_metadata( $attach_id, $upload['file'] );

		foreach ( $metas as $key => $value ) {
			update_post_meta( $attach_id, $key, $value );
		}

		return $attach_id;
	}
}
