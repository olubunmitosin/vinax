<?php
/**
 * Snax post save success template part
 *
 * @package snax 1.11
 * @subpackage Plugin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-message snax-message-success">
	<p><?php esc_html_e( 'Post draft updated.', 'snax' ); ?></p>
</div>
