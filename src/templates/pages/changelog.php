<?php
// Fetch view from Template Vars
$view = $this->get( 'view' );

if ( ! $view ) {
	return;
}
$only_version = fp_get_request_var( 'version', null );

$readme = include FakerPress\Plugin::path( 'src/data/readme.php' );

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
		<?php foreach ( $readme->changelog->versions as $number => $version ) : ?>
			<?php
				if ( ! is_null( $only_version ) && $number !== $only_version ) {
					continue;
				}
			?>
            <h3><?php echo esc_html( $version->number ); ?> &mdash; <?php echo esc_html( $version->date ); ?></h3>
            <?php echo $version->html; ?>
        <?php endforeach; ?>
	</div>
</div>
