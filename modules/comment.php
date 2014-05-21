<?php
namespace FakerPress\Module;

class Comment extends Base {

	public $faked_args = array(
		'comment_content' => null,
		'user_id'		  => null,
		'comment_author'  => null,
		'comment_author_email' => null,
		'comment_author_url' => null,
		'comment_approved' => null,
		'comment_post_ID'     => null,
	);

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
		return wp_insert_comment( $this->args );
	}
}
