<?php
/**
 * Quiz tabs
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<p class="nav-tab-wrapper quizzard-tab-wrapper">
	<a href="" class="nav-tab nav-tab-active"><?php esc_html_e( 'Edit Personalities', 'snax' ); ?></a>
	<a href="" class="nav-tab"><?php echo esc_html_e( 'Edit Questions', 'snax' ); ?></a>
</p>