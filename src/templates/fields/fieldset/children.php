<?php
/**
 * Field template for the children of a fieldset, printed as a nested table.
 *
 * Expects `$field` (Fieldset_Field) in the template context.
 *
 * @package FakerPress
 */

?>
<table class="fakerpress-fieldset-table" style="display: table;">
	<tbody>
	<?php foreach ( $field->get_children() as $children ) : ?>
		<?php $children->get_html(); ?>
	<?php endforeach; ?>
	</tbody>
</table>
