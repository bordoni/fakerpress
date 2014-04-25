<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $faked_args = array();

	public $args = array();

	public function __construct( $args = array() ) {
		$this->faker = \Faker\Factory::create();

		$provider = new \Faker\Provider\Comment( $this );
		$this->faker->addProvider( $provider );

		$this->args = apply_filters( 'fakerpress.module.comment.args', wp_parse_args( array_merge( $this->args, $this->faked_args ) , $args ) );
	}

	public function save() {

		foreach ( $this->faked_args as $key => $value ) {
			if ( is_null( $value ) ) {
				$this->faker->$key();
			}
		}

		// Here you should use the `$this->args['param_name']`
		return $this->args;
	}
}
