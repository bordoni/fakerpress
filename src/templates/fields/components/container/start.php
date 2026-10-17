<?php
$container_classes[] = 'field-container';
$container_classes[] = 'type-' . $field::get_slug() . '-container';

?>
<tr
	id="<?php echo esc_attr( $field->get_html_id( 'container' ) ); ?>"
	class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>"
>
