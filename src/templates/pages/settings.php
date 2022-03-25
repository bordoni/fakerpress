<?php
namespace FakerPress;
use FakerPress\Admin\View\Abstract_View;
use FakerPress\Fields;

/**
 * @var Abstract_View $this The instance of the Template that we are using to build this view.
 */

$fields[] = new Field(
	'text',
	[
		'id' => 'erase_phrase',
		'placeholder' => 'The cold never bothered me anyway!',
	],
	[
		'label' => __( 'Erase faked data', 'fakerpress' ),
		'description' => __( 'To erase all data generated type "<b>Let it Go!</b>", <i>please back up your database before you proceed!</i>', 'fakerpress' ),
		'actions' => [
			'delete' => __( 'Delete!', 'fakerpress' ),
		],
	]
);

?>
<div class='wrap'>
	<h2><?php echo esc_attr( $this->get_title() ); ?></h2>

	<form method='post'>
		<?php wp_nonce_field( Plugin::$slug . '.request.' . $this::get_slug() ); ?>
		<table class="form-table" style="display: table;">
			<tbody>
				<?php foreach ( $fields as $field ) : ?>
					<?php $field->output( true ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</form>
</div>
