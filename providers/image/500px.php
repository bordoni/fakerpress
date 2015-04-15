<?php
namespace Faker\Provider;
use \FakerPress\Plugin;

class Image500px extends Base {

	protected static $base_url = 'https://api.500px.com/v1/';

	/**
	 * @param \Faker\Generator $generator
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

	protected function request( $endpoint = 'photos', $args = array() ){
		$key = \FakerPress\Plugin::get( array( '500px', 'key' ), false );

		if ( ! $key ){
			return false;
		}

		$defaults = array(
			'consumer_key' => $key,
		);

		$args = wp_parse_args( $args, $defaults );

		// Determine the Transient Key
		array_multisort( $args );
		$hashed_id = substr( md5( json_encode( $args ) ), 0, 10 );
		$transient_id = Plugin::$slug . '-request-500px-' . $hashed_id;

		if ( false === ( $response = get_transient( $transient_id ) ) ) {
			$response = wp_remote_get( add_query_arg( $args, self::$base_url . $endpoint ) );
			$response = wp_remote_retrieve_body( $response );

			if ( ! $response ){
				return false;
			}

			$response = json_decode( $response );

			set_transient( $transient_id, $response, 6 * HOUR_IN_SECONDS );
		}

		return $response;
	}

	public function image_500px( $args = array() ) {
		$defaults = array(
			'feature' => 'editors',
			'sort' => 'created_at',
			'image_size' => 1080,
			'rpp' => 100,
			'only' => urlencode( 'Abstract,Food,Nature,Landscapes,City and Architecture,Animals,Macro' ),
		);
		$args = wp_parse_args( $args, $defaults );

		$response = $this->request( 'photos', $args );

		if ( ! $response ){
			return false;
		}

		$photo = $this->generator->randomElement( $response->photos );

		return $photo->image_url;
	}

}
