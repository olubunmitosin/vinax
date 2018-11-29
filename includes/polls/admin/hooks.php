<?php
/**
 * Admin Hooks
 *
 * @package snax
 * @subpackage Hooks
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Forms.
add_action( 'edit_form_after_editor', 	        'snax_render_poll_form' );
add_action( 'save_post', 				        'snax_save_poll_form', 10, 3 );

// Ajax.
add_action( 'wp_ajax_snax_poll_sync_question', 	'snax_ajax_poll_sync_question' );
add_action( 'wp_ajax_snax_poll_sync_answer', 	'snax_ajax_poll_sync_answer' );
add_action( 'wp_ajax_snax_poll_sync_result', 	'snax_ajax_poll_sync_result' );

// Menu.
add_action( 'admin_menu', 			            'snax_register_new_poll_page' );
add_filter( 'admin_url', 			            'snax_redirect_to_new_poll_page', 10, 3 );

// Post list.
add_filter( 'manage_snax_poll_posts_columns' ,          'snax_register_polls_custom_columns' );
add_action( 'manage_snax_poll_posts_custom_column' ,    'snax_render_polls_custom_columns', 10, 2 );
