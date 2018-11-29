<?php
/**
 * Snax Settings Sections
 *
 * @package snax
 * @subpackage Settings
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_sections_path = trailingslashit( dirname( __FILE__ ) );

require_once $snax_sections_path . 'section-general.php';
require_once $snax_sections_path . 'section-pages.php';
require_once $snax_sections_path . 'section-voting.php';
require_once $snax_sections_path . 'section-auth.php';
require_once $snax_sections_path . 'section-permalinks.php';

/**
 * Get the main settings sections.
 *
 * @return array
 */
function snax_admin_get_settings_sections() {
	return (array) apply_filters( 'snax_admin_get_settings_sections', array() );
}

/**
 * Get all of the settings fields.
 *
 * @return array
 */
function snax_admin_get_settings_fields() {
	return (array) apply_filters( 'snax_admin_get_settings_fields', array() );
}


/**
 * Get settings fields by section.
 *
 * @param string $section_id    Section id.
 *
 * @return mixed                False if section is invalid, array of fields otherwise.
 */
function snax_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty.
	if ( empty( $section_id ) ) {
		return false;
	}

	$fields = snax_admin_get_settings_fields();
	$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

	return (array) apply_filters( 'snax_admin_get_settings_fields_for_section', $retval, $section_id );
}
