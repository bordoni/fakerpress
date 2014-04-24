<?php
namespace FakerPress\Module;

include_once \Fakerpress\Plugin::path( 'providers/post.php' );

class Post extends Base {

	public $faked_args = array(
		'post_title' => null,
		'post_content' => null,
	);

	public $args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
	);

	public function __construct( $args = array() ) {
		$this->faker = \Faker\Factory::create( 'en_US' );
		$provider    = new \Faker\Provider\Post( $this );

		$this->faker->addProvider( $provider );

		$this->args = apply_filters( 'fakerpress.module.post.args', wp_parse_args( array_merge( $this->args, $this->faked_args ) , $args ) );
	}

	public function save() {

		foreach ( $this->faked_args as $key => $value ) {
			if ( is_null( $value ) ) {
				$this->faker->$key();
			}
		}

		return wp_insert_post( $this->args );
	}
}
