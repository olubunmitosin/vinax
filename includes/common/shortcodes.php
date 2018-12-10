<?php
/**
 * Snax Shortcodes
 *
 * @package snax
 * @subpackage Shortcodes
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}



function home_show_submission_form()
{
    echo 'This will be where the form appears';
}

add_shortcode('snax_home_submission','home_show_submission_form');