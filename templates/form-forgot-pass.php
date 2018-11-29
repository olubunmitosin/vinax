<?php
/**
 * Snax Forgot Pass Form
 *
 * @package snax 1.11
 * @subpackage Form
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Default 'redirect' value takes the user back to the request URI.
$snax_http_host     = filter_input( INPUT_SERVER, 'HTTP_HOST' );
$snax_request_uri   = filter_input( INPUT_SERVER, 'REQUEST_URI' );

$snax_redirect_to = ( is_ssl() ? 'https://' : 'http://' ) . $snax_http_host . $snax_request_uri;
$snax_redirect_to = apply_filters( 'snax_forgot_pass_redirect', $snax_redirect_to );
?>

<div class="snax-forgot-pass-tab snax-tab-inactive">

	<h2 class="g1-alpha g1-alpha-2nd"><?php esc_html_e( 'Forgot your password?', 'snax' ); ?></h2>

	<p>
		<?php esc_html_e( 'Enter your account data and we will send you a link to reset your password.', 'snax' ); ?>
	</p>

	<?php do_action( 'snax_forgot_pass_form_top' ); ?>

	<div class="snax-forgot-pass-form" data-snax-nonce="<?php echo esc_attr( wp_create_nonce( 'snax-ajax-forgot-pass-nonce' ) ); ?>">
		<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( get_site_url( null, 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
			<div class="snax-validation-error snax-forgot-pass-error-message"></div>
			<div class="snax-validation-error snax-forgot-pass-success-message"></div>
			<p class="forgot-username">
				<label for="user_login"><?php esc_html_e( 'Username or Email Address' ); ?></label>
				<input type="text" name="user_login" id="forgot-user_login" class="input" value="" size="20" placeholder="<?php esc_html_e( 'Username or Email Address' ); ?>" />
			</p>
			<?php
			/**
			 * Fires inside the lostpassword form tags, before the hidden fields.
			 *
			 * @since 2.1.0
			 */
			do_action( 'lostpassword_form' ); ?>

			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $snax_redirect_to ); ?>" />
			<p class="forgot-submit">
				<input type="submit" name="wp-submit" id="forgot-wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Reset password', 'snax' ); ?>" />
			</p>

			<a href="#" class="snax-back-to-login-tab"><?php echo esc_html_e( 'Back to Login', 'snax' ); ?></a>
		</form>
	</div>

	<?php do_action( 'snax_forgot_pass_form_bottom' ); ?>

</div>
