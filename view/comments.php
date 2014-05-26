<div class='wrap'>
	<h2><?php esc_attr_e( 'Create Fake Comments', 'fakerpress' ); ?></h2>

	<?php
		$faker = new \FakerPress\Module\Comment();
		$faker->generate(
			array(
				'post_status' => array( 'publish' ),
				'post_date' => array( '-7 years', 'now' )
			)
		);
		$faker->save();
	?>
</div>