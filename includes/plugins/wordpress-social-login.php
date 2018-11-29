<?php
/**
 * Wordpress Social Login plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'snax_login_form_top',                  'snax_wsl_render_auth_widget_in_wp_login_form' );
add_action( 'wsl_render_auth_widget_start',         'snax_wsl_render_auth_widget_start' );
add_action( 'wsl_render_auth_widget_end',           'snax_wsl_render_auth_widget_end' );
add_filter( 'snax_disable_wp_login_option_active',  '__return_true' );
add_filter( 'snax_before_item_comments', 'snax_wsl_before_item_comments');
add_filter( 'snax_after_item_comments', 'snax_wsl_after_item_comments');


/**
 * Render WPSL widget inside the snax login form.
 */
function snax_wsl_render_auth_widget_in_wp_login_form() {
	 echo filter_var( wsl_render_auth_widget() );
}

/**
 * Render the opening tag of our custom wrapper for styling purposes
 */
function snax_wsl_render_auth_widget_start() {
	if ( 'none' === get_option( 'wsl_settings_social_icon_set' ) ) {
		echo '<div class="snax snax-wpsl-wrapper">';
			echo '<div class="snax-wpsl">';

	}
}

/**
 * Render the closing tag of our custom wrapper for styling purposes
 */
function snax_wsl_render_auth_widget_end() {
	if ( 'none' === get_option( 'wsl_settings_social_icon_set' ) ) {
			echo '</div>';
		echo '</div>';
	}
}


/**
 * Remove WSL widget from item comments form
 */
function snax_wsl_before_item_comments() {
	remove_action( 'comment_form_top'              , 'wsl_render_auth_widget_in_comment_form' );
	remove_action( 'comment_form_must_log_in_after', 'wsl_render_auth_widget_in_comment_form' );
}

/**
 * Reenable WSL widget after item comments form
 */
function snax_wsl_after_item_comments() {
	add_action( 'comment_form_top'              , 'wsl_render_auth_widget_in_comment_form' );
	add_action( 'comment_form_must_log_in_after', 'wsl_render_auth_widget_in_comment_form' );
}
