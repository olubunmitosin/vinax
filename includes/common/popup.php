<?php
/**
 * Snax Popup Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Render popup container
 */
function snax_render_popup_content() {
	?>
	<div id="snax-popup-content" class="snax white-popup mfp-hide">
		<?php do_action( 'snax_popup_content' ); ?>
	</div>
	<?php
}

