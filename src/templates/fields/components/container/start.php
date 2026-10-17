<?php
/**
 * Field template that opens a field's table row with its id and container classes.
 *
 * Expects `$field` (a Field_Interface) and, optionally, `$container_classes` in the template context.
 *
 * @package FakerPress
 */

$container_classes[] = 'field-container';
$container_classes[] = 'type-' . $field::get_slug() . '-container';

?>
<tr
	id="<?php echo esc_attr( $field->get_html_id( 'container' ) ); ?>"
	class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>"
>
