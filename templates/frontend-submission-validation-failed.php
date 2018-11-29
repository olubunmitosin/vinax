<?php
/**
 * Snax validation failed template part
 *
 * @package snax 1.11
 * @subpackage Plugin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-message snax-message-error">
	<p><?php esc_html_e( 'Please correct all errors before continuing', 'snax' ); ?></p>
</div>
