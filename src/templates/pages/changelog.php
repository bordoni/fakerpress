<?php
// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}
?>
<div class='wrap about-wrap'>
	<h1><?php esc_attr_e( 'What has Changed in FakerPress', 'fakerpress' ); ?></h1>
	<div class='about-text'>
		<?php echo wp_kses_post( wpautop( __( 'Sorry but this page still doesn\'t have the information available.', 'fakerpress' ) ) ); ?>
	</div>
</div>