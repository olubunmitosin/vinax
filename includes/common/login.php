<?php
/**
 * Snax Login/Register Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Render login form
 */
function snax_render_login_form() {
	if ( is_user_logged_in() ) {
		return;
	}

	snax_get_template_part( 'form-login' );
	snax_get_template_part( 'form-forgot-pass' );
	snax_get_template_part( 'form-reset-pass' );
	snax_get_template_part( 'form-gdpr' );
}

/**
 * Check where to show WP login form
 *
 * @return bool
 */
function snax_show_wp_login_form() {
	$show = ! snax_disable_wp_login();

	return apply_filters( 'snax_wp_login_form', $show );
}

/**
 * Render login form errors
 *
 * @param string $content       Login form HTML.
 *
 * @return string
 */
function snax_render_login_form_errors( $content ) {
	$content .= '<div class="snax-validation-error snax-login-error-message"></div>';

	return $content;
}

/**
 * Render login reCaptcha
 *
 * @param string $content       Login form HTML.
 *
 * @return string
 */
function snax_render_login_recaptcha( $content ) {
	$content .= '<div id="snax-login-recaptcha"></div>';

	return $content;
}

/**
 * Return invalid reCaptcha message
 *
 * @return string
 */
function snax_get_recaptcha_invalid_message() {
	return __( '<strong>ERROR</strong>: The reCAPTCHA you entered is incorrect.', 'snax' );
}

/**
 * Verify Google reCaptcha
 *
 * @param string $token         reCaptcha reposnse token.
 *
 * @return bool
 */
function snax_verify_recaptcha( $token ) {
	$api_url = snax_get_recaptcha_verify_api_url();

	$recaptcha_response = wp_remote_retrieve_body( wp_remote_post( $api_url, array(
		'body' => array(
			'secret'    => snax_get_recaptcha_secret(),
			'response'  => $token,
		),
	) ) );

	$response_arr = json_decode( $recaptcha_response, true );

	return ( ! empty( $response_arr['success'] ) && $response_arr['success'] );
}
