<?php
/**
 * Snax Format Loader
 *
 * @package snax
 * @subpackage Formats
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$format_path = trailingslashit( dirname( __FILE__ ) );
require_once $format_path . 'functions.php';

if ( is_admin() ) {
	require_once $format_path . 'settings.php';
}