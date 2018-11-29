<?php
/**
 * Snax Login Form
 *
 * @package snax 1.11
 * @subpackage Form
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-login-tab snax-tab-active">

	<h2 class="g1-alpha"><?php esc_html_e( 'Log in', 'snax' ); ?></h2>

	<?php $snax_has_top_filter = has_filter( 'snax_login_form_top' ); ?>

	<?php if ( $snax_has_top_filter ) : ?>
		<h3 class="g1-delta snax-login-with-social-network"><?php esc_html_e( 'With social network:', 'snax' ); ?></h3>
	<?php endif; ?>

	<?php do_action( 'snax_login_form_top' ); ?>

	<?php if ( snax_show_wp_login_form() ) : ?>

		<?php if ( $snax_has_top_filter ) : ?>
			<h3 class="g1-delta"><span><?php esc_html_e( 'Or with username:', 'snax' ); ?></span></h3>
		<?php endif; ?>

		<h4 class="snax-form-legend snax-form-legend-sign-in"><?php esc_html_e( 'Sign in', 'snax' ); ?></h4>

		<div class="snax-login-form" data-snax-nonce="<?php echo esc_attr( wp_create_nonce( 'snax-ajax-login-nonce' ) ); ?>">
			<?php
			wp_login_form( array(
				'remember' => false,
				'form_id'	=> 'loginform-in-popup',
			) );
			?>
		</div>

		<a class="snax-link-forgot-pass" href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'snax' ); ?></a>

		<?php if ( get_option( 'users_can_register' ) ) : ?>
			<p class="snax-form-tip snax-form-tip-register"><?php esc_html_e( 'Don\'t have an account?', 'snax' ); ?> <a
					href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Register', 'snax' ); ?></a>
			</p>
		<?php endif; ?>

	<?php endif; ?>

	<?php do_action( 'snax_login_form_bottom' ); ?>

</div>
