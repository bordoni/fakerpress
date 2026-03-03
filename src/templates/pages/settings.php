<?php
namespace FakerPress;

use FakerPress\Admin\View\Abstract_View;

/**
 * @var Abstract_View $this The instance of the Template that we are using to build this view.
 */
?>
<div class='wrap'>
	<div id="fakerpress-react-root" data-page="settings"></div>
	<noscript>
		<p><?php esc_html_e( 'FakerPress requires JavaScript to be enabled.', 'fakerpress' ); ?></p>
	</noscript>
</div>
