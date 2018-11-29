<?php
/**
 * Snax Settings Section
 *
 * @package snax
 * @subpackage Settings
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Register section and fields.
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_auth' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_auth' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_auth( $sections ) {
	$sections['snax_settings_auth'] = array(
		'title'    => __( 'Auth', 'snax' ),
		'callback' => 'snax_admin_settings_auth_section_description',
		'page'      => 'snax-auth-settings',
	);

	return $sections;
}

/**
 * Register section fields
 *
 * @param array $fields     Fields.
 *
 * @return array
 */
function snax_admin_settings_fields_auth( $fields ) {
	$fields['snax_settings_auth'] = array(
		'snax_facebook_app_id' => array(
			'title'             => __( 'Facebook App ID', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_facebook_app_id',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_login_recaptcha' => array(
			'title'             => __( 'reCaptcha for login form', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_login_recaptcha',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_recaptcha_site_key' => array(
			'title'             => __( 'reCaptcha Site Key', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_recaptcha_site_key',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_recaptcha_secret' => array(
			'title'             => __( 'reCaptcha Secret', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_recaptcha_secret',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
	);

	return $fields;
}

function snax_admin_auth_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'Auth', 'snax' ) ); ?></h2>
		<form action="options.php" method="post">

			<?php settings_fields( 'snax-auth-settings' ); ?>
			<?php do_settings_sections( 'snax-auth-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>

		</form>
	</div>

	<?php
}


/**
 * Auth section description
 */
function snax_admin_settings_auth_section_description() {}

/**
 * Enable reCaptcha for login
 */
function snax_admin_setting_callback_login_recaptcha() {
	?>
	<input name="snax_login_recaptcha" id="snax_login_recaptcha" type="checkbox" <?php checked( snax_is_recatpcha_enabled_for_login_form() ); ?> />
	<?php
}

/*
 * reCaptcha Site Key
 */
function snax_admin_setting_callback_recaptcha_site_key() {
	?>
	<input name="snax_recaptcha_site_key" id="snax_recaptcha_site_key" class="regular-text" type="text" value="<?php echo esc_attr( snax_get_recaptcha_site_key() ); ?>" />
	<p class="description">
		<?php echo wp_kses_post( sprintf( __( 'How do I get my <strong>reCaptcha API key pair</strong>? Use the <a href="%s" target="_blank">reCaptcha Getting Started</a> guide for help.', 'snax' ), esc_url( 'https://developers.google.com/recaptcha/intro' ) ) ); ?>
	</p>
	<?php
}

/*
 * reCaptcha Secret
 */
function snax_admin_setting_callback_recaptcha_secret() {
	?>
	<input name="snax_recaptcha_secret" id="snax_recaptcha_secret" class="regular-text" type="text" value="<?php echo esc_attr( snax_get_recaptcha_secret() ); ?>" />
	<?php
}
