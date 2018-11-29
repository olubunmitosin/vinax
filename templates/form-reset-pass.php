<?php
/**
 * Reset password form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

?>

<div class="snax-reset-tab snax-tab-inactive">

<?php
if ( is_user_logged_in() ) {
	wp_redirect( home_url() );
	exit;
}

$key 	= filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING );
$login 	= filter_input( INPUT_GET, 'login', FILTER_SANITIZE_STRING );
$user 	= check_password_reset_key( $key, $login );

if ( ! $user || is_wp_error( $user ) ) { ?>
	<div class="snax-reset-pass-form">
		<h2><?php esc_html_e( 'Your password reset link appears to be invalid or expired.' ); ?></h2>
	</div>

<?php
} else {
	$cookie_name = 'wp-resetpass-' . COOKIEHASH;
	$cookie_value = sprintf( '%s:%s', wp_unslash( $login ), wp_unslash( $key ) );
?>

	<h2 class="g1-alpha g1-alpha-2nd"><?php esc_html_e( 'Reset your password', 'snax' ); ?></h2>

	<?php do_action( 'snax_reset_pass_form_top' ); ?>

	<div class="snax-reset-pass-form">

	<form name="snax_reset_password_form" id="snax_reset_password_form" method="get"  action="<?php echo esc_url( get_site_url( null, 'wp-login.php', 'login_post' ) ); ?>">

		<p class="reset-pass">
			<label for="new_password"><?php esc_html_e( 'New password' ); ?></label>
			<input type="password" name="pass1" id="new_password" class="input" value="" size="20" placeholder="<?php esc_html_e( 'New password' ); ?>"  required />
		</p>

		<p class="reset-pass">
			<label for="repeat_password"><?php esc_html_e( 'Confirm new password' ); ?></label>
			<input type="password" name="pass2" id="repeat_password" class="input" value="" size="20" placeholder="<?php esc_html_e( 'Confirm new password' ); ?>"  required />
		</p>

		<input type="hidden" class="rp-cookie-name" value="<?php echo esc_attr( $cookie_name ); ?>" />
		<input type="hidden" class="rp-cookie_value" value="<?php echo esc_attr( $cookie_value ); ?>" />
		<input type="hidden" name="rp_key" class="rp-key" value="<?php echo esc_attr( $key ); ?>" />
		<input type="hidden" name="login" class="login" value="<?php echo esc_attr( $login ); ?>" />

		<!-- Fallback for no-js, send the user to the wp-login form with a valid GET request -->
		<input type="hidden" name="action" value="resetpass" />
		<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>" />

		<p class="reset-password-submit">
			<input type="submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Set new password', 'snax' ); ?>" />
		</p>

		<div class="snax-validation-error snax-reset-pass-error-message"></div>
		<div class="snax-validation-error snax-reset-pass-success-message"></div>

		<a href="#" class="snax-back-to-login-tab"><?php echo esc_html_e( 'Back to Login', 'snax' ); ?></a>

	</form>
</div>

	<?php do_action( 'snax_reset_form_bottom' ); ?>
<?php
}
?>

</div>
