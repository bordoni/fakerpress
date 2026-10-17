<?php
/**
 * Field template for a fieldset: a full-width table row that renders each child field.
 *
 * Expects `$field` (Fieldset_Field) in the template context.
 *
 * @package FakerPress
 */

?>
<?php $this->render( 'components/container/start' ); ?>
<td class="fakerpress-fields-row-full-width" colspan="100%">
	<?php $this->render( 'fieldset/children' ); ?>
</td>
<?php $this->render( 'components/container/end' ); ?>
