<div class='wrap'>
    <h2><?php esc_attr_e( 'Development View', 'fakerpress' ); ?></h2>
	<?php
		$faker   = new \FakerPress\Module\Comment();
		$post_id = $faker->save();

		echo wp_kses_post( '<a href="' . admin_url() . 'comment.php?action=editcomment&c=' . absint( $post_id ) . '">Comment Generated (ID:' .absint( $post_id ) . ')' )
	?>
</div>