<?php
/**
 * Snax Cron Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

if ( ! wp_next_scheduled( 'snax_clean_up_junk_uploads' ) ) {
	wp_schedule_event( time(), 'twicedaily', 'snax_clean_up_junk_uploads' );
}
