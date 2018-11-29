<?php
/**
 * WPML plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'snax_frontend_submission_page_id',	'snax_wpml_translate_page_id' );
add_filter( 'snax_legal_page_id', 				'snax_wpml_translate_page_id' );
add_filter( 'snax_report_page_id', 				'snax_wpml_translate_page_id' );

/**
 * Return page id in current language
 *
 * @param int $page_id			Page id.
 *
 * @return int
 */
function snax_wpml_translate_page_id( $page_id ) {
	$page_id = wpml_object_id_filter( $page_id, 'page', true );

	return $page_id;
}
