<?php
/**
 * Snax Mirror Functions
 *
 * By mirroring existing WordPress hooks we allow dependant plugins to hook into the Snax specific ones,
 * thus guaranteeing proper code execution only when Snax is active.
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Runs when Snax setup globals and loaded all dependencies
 */
function snax_loaded() {
	do_action( 'snax_loaded' );
}

/**
 * Initialize any code after everything has been loaded
 */
function snax_init() {
	do_action( 'snax_init' );
}
