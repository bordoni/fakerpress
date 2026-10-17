<?php
/**
 * Field template for a raw field: a full-width table row holding the field's raw HTML.
 *
 * Expects `$field` (Raw_Field) in the template context.
 *
 * @package FakerPress
 */

?>
<?php $this->render( 'components/container/start' ); ?>
<td class="fakerpress-fields-row-full-width" colspan="100%">
	<?php echo $field->get_raw_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, StellarWP.XSS.EscapeOutput.OutputNotEscaped -- A raw field exists to print markup built by plugin code (buttons, inputs); escaping would break it. ?>
</td>
<?php $this->render( 'components/container/end' ); ?>
