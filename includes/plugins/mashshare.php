<?php
/**
 * MashShare plugin integration
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'mashsb_rwmb_meta_boxes', 'snax_mashshare_add_post_types_to_metaboxes', 99, 1 );

/**
 * Add snax post types to MashShare metabox
 *
 * @param arr $metaboxes  Array of MashShare metaboxes.
 * @return arr
 */
function snax_mashshare_add_post_types_to_metaboxes( $metaboxes ) {
	$metaboxes[0]['pages'][] = 'snax_quiz';
	return $metaboxes;
}
