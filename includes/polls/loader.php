<?php
/**
 * Load files
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_polls_path = trailingslashit( dirname( __FILE__ ) );

// Common.
require_once( $snax_polls_path . 'functions.php' );
require_once( $snax_polls_path . 'hooks.php' );
require_once( $snax_polls_path . 'ajax.php' );

// Front.
if ( ! is_admin() ) {
	require_once( $snax_polls_path . 'front/functions.php' );
	require_once( $snax_polls_path . 'front/hooks.php' );

	// Admin.
} else {
	require_once( $snax_polls_path . 'admin/functions.php' );
	require_once( $snax_polls_path . 'admin/ajax.php' );
	require_once( $snax_polls_path . 'admin/hooks.php' );
}
