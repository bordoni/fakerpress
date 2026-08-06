<?php
/**
 * Scramble users template.
 *
 * @since 0.9.2
 * @package FakerPress
 */

namespace FakerPress;

use FakerPress\Admin\View\Abstract_View;

/**
 * @var Abstract_View $this The instance of the Template that we are using to build this view.
 */

$fields[] = new Field(
	'text',
	[
		'id'          => 'scramble_phrase',
		'placeholder' => 'Scramble the real data away!',
	],
	[
		'label'       => __( 'Scramble real users', 'fakerpress' ),
		'description' => __( 'Replaces the names and emails of existing users with realistic fake data so you can demo this site without exposing real people. To proceed type "<b>Scramble</b>". <i>This cannot be undone, back up your database first!</i>', 'fakerpress' ),
		'actions'     => [
			'scramble' => __( 'Scramble!', 'fakerpress' ),
		],
	]
);

?>
<div class='wrap'>
	<h2><?php echo esc_html( $this->get_title() ); ?></h2>

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
