<?php
namespace Faker\Provider;
use \FakerPress\Plugin;

/**
 * Faker provider class for 500px images
 *
 * @since  0.2.2
 */
class Image500px extends Base {

	/**
	 * The base URL for the 500px API
	 *
	 * @since  0.2.2
	 *
	 * @var   string
	 */
	protected static $base_url = 'https://api.500px.com/v1/';

	/**
	 * Constructor for the 500px provider
	 *
	 * @since  0.2.2
	 *
	 * @param \Faker\Generator $generator An instance of the Faker Generator class
	 */
	public function __construct( \Faker\Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Makes a request to the 500px api
	 *
	 * @since 0.2.2
	 *
	 * @see  https://github.com/500px/api-documentation For more information on Endpoints
	 *
	 * @param  string $endpoint Which endpoint we are going to request
	 * @param  array  $args     Arguments use to request
	 * @return object|bool      The Body of the response or false if fail
	 */
	protected function request( $endpoint = 'photos', $args = array() ){
		$key = \FakerPress\Plugin::get( array( '500px', 'key' ), false );

		if ( ! $key ) {
			return false;
		}

		$defaults = array(
			'consumer_key' => $key,
		);

		$args = wp_parse_args( $args, $defaults );

		// Determine the Transient Key
		array_multisort( $args );
		$hashed_id = substr( md5( json_encode( $args ) ), 0, 10 );

		// Creates as transient key for the 500px request
		$transient_id = Plugin::$slug . '-request-500px-' . $endpoint . '-' . $hashed_id;

		/**
		 * Filter how many seconds we keep the 500px requests in a transient
		 * Set to false or 0 to avoid caching
		 *
		 * @since  0.4.9
		 *
		 * @param  int     $expiration  How long we should hold to the a result of images from 500px
		 * @param  string  $endpoint    Which endpoint we are fetching
		 * @param  array   $args        Arguments for the Request to 500px
		 * @param  self    $provider    Instance of the Current class
		 */
		$expiration = apply_filters( 'fakerpress.provider.image.500px.request_expiration', 6 * HOUR_IN_SECONDS, $endpoint, $args, $this );

		if ( ! $expiration || false === ( $response = get_transient( $transient_id ) ) ) {
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

	/**
	 * Fetches a random photo from 500px based on a given search param
	 *
	 * @since  0.2.2
	 *
	 * @param  array  $args The request params for 500px search of photos
	 *
	 * @return string       The Image url from 500px (external URL)
	 */
	public function image_500px( $args = array() ) {
		$categories = array(
			'Abstract',
			'Aerial',
			'Animals',
			'City and Architecture',
			'Food',
			'Landscapes',
			'Macro',
			'Nature',
			'Urban Exploration',
			'Wedding',
		);

		$defaults = array(
			'feature' => 'editors',
			'sort' => 'created_at',
			'image_size' => 1080,
			'rpp' => 100,
			'exclude_nude' => 1,
			'only' => urlencode( implode( ',', $categories ) ),
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * Allow third party developers to modify params for 500px requests
		 *
		 * @since  0.4.9
		 *
		 * @param  array $args      Arguments for the Request to 500px
		 * @param  self  $provider  Instance of the Current class
		 */
		$args = apply_filters( 'fakerpress.provider.image.500px.args', $args, $this );

		$response = $this->request( 'photos', $args );

		if ( ! $response ){
			return false;
		}

		$photo = $this->generator->randomElement( $response->photos );

		return $photo->image_url;
	}

}
