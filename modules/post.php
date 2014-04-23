<?php
namespace FakerPress\Module;

include_once \Fakerpress\Plugin::path( 'providers/post.php' );

class Post extends Base {

	public function __construct( $arguments ) {
		$this->faker = \Faker\Factory::create( 'en_US' );
		$provider    = new \Faker\Provider\Post( $this->faker );

		$this->faker->addProvider( $provider );
	}

	public function save( $faker ) {

	}
}
