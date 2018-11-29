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
add_action( 'init', 	'snax_register_post_types' );
add_action( 'init' , 	'snax_add_image_sizes' );

// Ajax.
add_action( 'wp_ajax_snax_load_quiz_result', 		'snax_ajax_load_quiz_result' );
add_action( 'wp_ajax_nopriv_snax_load_quiz_result',	'snax_ajax_load_quiz_result' );

add_action( 'before_delete_post',       'snax_remove_quiz_children' );