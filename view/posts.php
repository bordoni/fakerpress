<div class='wrap'>
	<h2><?php esc_attr_e( 'Create Fake Posts', 'fakerpress' ); ?></h2>

	<?php
		$faker = new \FakerPress\Module\Post();
		$faker->generate(
			array(
				'post_type' => array( 'page', 'post' ),
			)
		);
	?>
</div>