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

<div class="snax-gdpr-tab snax-tab-inactive">

	<h2 class="g1-alpha"><?php esc_html_e( 'Log in', 'snax' ); ?></h2>

	<h3 class="g1-delta"><?php esc_html_e( 'Privacy Policy', 'snax' ); ?></h3>

	<p><?php do_action( 'snax_gdpr_consent_text' ); ?></p>

	<a class="g1-button g1-button-l g1-button-wide g1-button-solid snax-login-gdpr-accept" href="#"><?php esc_html_e( 'Acceept', 'snax' ); ?></a>

</div>
