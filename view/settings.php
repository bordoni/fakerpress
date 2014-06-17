<div class='wrap'>
	<h2><?php echo esc_attr( \FakerPress\Admin::$view->title ); ?></h2>
	<div>
		<?php echo wp_kses_post( wpautop( __( "Yeah! The plugin is still simple enough that it doesn't have any settings to be changed.\n\nCheckout the menus of our plugin to check what you can generate with the plugin.", 'fakerpress' ) ) ); ?>
	</div>
</div>