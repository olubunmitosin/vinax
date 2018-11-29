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

$snax_quizzes_path = trailingslashit( dirname( __FILE__ ) );

// Common.
require_once( $snax_quizzes_path . 'functions.php' );
require_once( $snax_quizzes_path . 'hooks.php' );
require_once( $snax_quizzes_path . 'ajax.php' );

// Front.
if ( ! is_admin() ) {
	require_once( $snax_quizzes_path . 'front/functions.php' );
	require_once( $snax_quizzes_path . 'front/hooks.php' );

	// Admin.
} else {
	require_once( $snax_quizzes_path . 'admin/functions.php' );
	require_once( $snax_quizzes_path . 'admin/ajax.php' );
	require_once( $snax_quizzes_path . 'admin/hooks.php' );
}
