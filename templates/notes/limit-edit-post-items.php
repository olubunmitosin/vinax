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

<div class="snax-note snax-note-warning snax-limit-edit-post-items snax-note-off">
	<div class="snax-note-icon"></div>

	<h2 class="snax-note-title"><?php esc_html_e( 'You\'ve reached the limit for adding new items to this post', 'snax' ); ?></h2>

	<p>
		<?php esc_html_e( 'Remove existing items to add new ones.', 'snax' ); ?>
	</p>
</div>
