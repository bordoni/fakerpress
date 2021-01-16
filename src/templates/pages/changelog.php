<?php
// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}
$version = fp_get_request_var( 'version', null );

$readme = new FakerPress\Readme;
$readme = $readme->parse_readme( FakerPress\Plugin::path( 'readme.txt' ), $version );

?>
<style>
    .about-text ul {
        margin-left: 20px;
    }
    .about-text ul li {
        list-style: disc;
        padding-left: 5px;
    }
</style>
<div class='wrap about-wrap'>
	<h1><?php esc_attr_e( 'What has Changed in FakerPress', 'fakerpress' ); ?></h1>
	<div class='about-text'>
		<?php foreach ( $readme['changelog']['versions'] as $number => $version ) : ?>
            <h3><?php echo esc_html( $version['number'] ); ?> &mdash; <?php echo esc_html( $version['date'] ); ?></h3>
            <?php echo $version['html']; ?>
        <?php endforeach; ?>
	</div>
</div>