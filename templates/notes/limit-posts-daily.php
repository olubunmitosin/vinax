<?php
/**
 * Note - daily post limit
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-note snax-note-warning">
	<div class="snax-note-icon"></div>

	<h2 class="snax-note-title"><?php esc_html_e( 'You\'ve reached the daily limit for adding new content', 'snax' ); ?></h2>

	<p>
		<?php esc_html_e( 'But there\'s nothing to worry about, you can add more tomorrow.', 'snax' ); ?>
	</p>
</div>
