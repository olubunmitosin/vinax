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
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_pages' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_pages' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_pages( $sections ) {
	$sections['snax_settings_pages'] = array(
		'title'    => __( 'Pages', 'snax' ),
		'callback' => 'snax_admin_settings_pages_section_description',
		'page'      => 'snax-pages-settings',
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
function snax_admin_settings_fields_pages( $fields ) {
	$fields['snax_settings_pages'] = array(
		// Frontend Submission.
		'snax_frontend_submission_page_id' => array(
			'title'             => __( 'Frontend Submission', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_frontend_submission_page',
			'sanitize_callback' => 'snax_sanitize_published_post',
			'args'              => array(),
		),
		// Terms & Conditions.
		'snax_legal_page_id' => array(
			'title'             => __( 'Terms and Conditions', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_legal_page',
			'sanitize_callback' => 'snax_sanitize_published_post',
			'args'              => array(),
		),
		// Report.
		'snax_report_page_id' => array(
			'title'             => __( 'Report', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_report_page',
			'sanitize_callback' => 'snax_sanitize_published_post',
			'args'              => array(),
		),
	);

	return $fields;
}

function snax_admin_pages_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'Pages', 'snax' ) ); ?></h2>
		<form action="options.php" method="post">

			<?php settings_fields( 'snax-pages-settings' ); ?>
			<?php do_settings_sections( 'snax-pages-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>

		</form>
	</div>

	<?php
}

/**
 * Pages section description
 */
function snax_admin_settings_pages_section_description() {}

/**
 * Frontend Submission page
 */
function snax_admin_setting_callback_frontend_submission_page() {
	$selected_page_id = snax_get_frontend_submission_page_id();
	?>

	<?php wp_dropdown_pages( array(
		'name'             => 'snax_frontend_submission_page_id',
		'show_option_none' => esc_html__( '- None -', 'snax' ),
		'selected'         => absint( $selected_page_id ),
	) );

	if ( ! empty( $selected_page_id ) ) :
		?>
		<a href="<?php echo esc_url( snax_get_frontend_submission_page_url() ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'View', 'snax' ); ?></a>
		<?php
	endif;
}

/**
 * Legal page
 */
function snax_admin_setting_callback_legal_page() {
	$selected_page_id = snax_get_legal_page_id();
	?>

	<?php wp_dropdown_pages( array(
		'name'             => 'snax_legal_page_id',
		'show_option_none' => esc_html__( '- None -', 'snax' ),
		'selected'         => absint( $selected_page_id ),
	) );

	if ( ! empty( $selected_page_id ) ) :
		?>
		<a href="<?php echo esc_url( snax_get_legal_page_url() ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'View', 'snax' ); ?></a>
		<?php
	endif;
}

/**
 * Report page
 */
function snax_admin_setting_callback_report_page() {
	$selected_page_id = snax_get_report_page_id();
	?>

	<?php wp_dropdown_pages( array(
		'name'             => 'snax_report_page_id',
		'show_option_none' => esc_html__( '- None -', 'snax' ),
		'selected'         => absint( $selected_page_id ),
	) );

	if ( ! empty( $selected_page_id ) ) :
		?>
		<a href="<?php echo esc_url( snax_get_report_page_url() ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'View', 'snax' ); ?></a>
		<?php
	endif;
}
