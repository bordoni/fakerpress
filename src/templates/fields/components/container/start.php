<?php
$container_classes[] = 'field-container';
$container_classes[] = 'type-' . $field::get_slug() . '-container';

?>
<tr
	id="<?php echo $field->get_html_id( 'container' ); ?>"
	class="<?php echo implode( ' ', $container_classes ); ?>"
>
