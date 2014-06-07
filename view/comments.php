<div class='wrap'>
	<h2><?php esc_attr_e( 'Create Fake Comments', 'fakerpress' ); ?></h2>

	<?php
		$faker = new \FakerPress\Module\Comment();
		$save = $faker->save();
		$faker->generate();
	?>
</div>