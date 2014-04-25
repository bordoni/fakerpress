<div class='wrap'>
    <h2><?php esc_attr_e( 'Development View', 'fakerpress' ); ?></h2>
	<?php
		$faker   = new \FakerPress\Module\Post();
		$post_id = $faker->save();

		echo wp_kses_post( '<a href="' . get_edit_post_link( $post_id ) . '">Edit Generate Post (ID: ' . absint( $post_id ) .') </a>' )
	?>
</div>