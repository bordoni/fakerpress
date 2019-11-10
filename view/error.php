<?php
namespace FakerPress;
$tweet_args = [
	'hashtags' => 'FakerPress',
	'related' => 'webord',
];
$locale = get_locale();
if ( ! empty( $locale ) ){
	$tweet_args['hashtags'] .= ',' . esc_attr( $locale );
}
$tweet_url = esc_url_raw( add_query_arg( $tweet_args, 'https://twitter.com/intent/tweet' ) );
?>
<div class='wrap about-wrap'>
	<h1><?php esc_attr_e( 'Oops, 404 Error!', 'fakerpress' ); ?></h1>
	<div class='about-text'>
		<?php echo wp_kses_post( wpautop( sprintf( __( "Yeah... Something went very wrong somewhere along your last actions.\n\nRight now we feel ashamed about this problem, <a href='%s' target='_blank'>poke us on twitter!</a>", 'fakerpress' ), $tweet_url ) ) ); ?>
	</div>
</div>
<script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>
