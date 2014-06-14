<div class='wrap'>
	<h2><?php esc_attr_e( 'Create Fake Users', 'fakerpress' ); ?></h2>

	<?php
		$faker = new \FakerPress\Module\User();
		$save = $faker->save();
		$faker->generate();
	?>
</div>