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

// Assets.
//add_action( 'admin_enqueue_scripts', 'snax_admin_enqueue_styles' );
//add_action( 'admin_enqueue_scripts', 'snax_admin_enqueue_scripts' );

// Forms.
add_action( 'edit_form_after_editor', 	'snax_render_quiz_form' );
add_action( 'save_post', 				'snax_save_quiz_form', 10, 3 );

// Ajax.
add_action( 'wp_ajax_snax_sync_question', 	'snax_ajax_sync_question' );
add_action( 'wp_ajax_snax_sync_answer', 	'snax_ajax_sync_answer' );
add_action( 'wp_ajax_snax_sync_result', 	'snax_ajax_sync_result' );

// Menu.
add_action( 'admin_menu', 			'snax_register_new_quiz_page' );
add_filter( 'admin_url', 			'snax_redirect_to_new_quiz_page', 10, 3 );
