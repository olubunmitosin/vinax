<?php
/**
 * Common Hooks
 *
 * @package snax
 * @subpackage Hooks
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Init.
add_action( 'init',     'snax_register_polls_post_types' );
add_action( 'init' ,    'snax_add_polls_image_sizes' );

// Ajax.
add_action( 'wp_ajax_snax_save_poll_answer',        'snax_ajax_save_poll_answer' );
add_action( 'wp_ajax_nopriv_snax_save_poll_answer', 'snax_ajax_save_poll_answer' );
add_action( 'before_delete_post',                   'snax_remove_poll_children' );
