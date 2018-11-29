<?php
/**
 * Facebook Instant Articles plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'instant_articles_content', 'snax_instant_articles_content' );

/**
 * Transform Snax post content to meet FB Instant Articles requirements
 *
 * @param string $content			Post content.
 *
 * @return string
 */
function snax_instant_articles_content( $content ) {
	$extra_content = '';
	$post_id = get_the_ID();

	$format = filter_input( INPUT_POST, 'snax-post-format', FILTER_SANITIZE_STRING );

 	if ( ! $format ) {
		$format = snax_get_format( $post_id );
	}

	if ( in_array( $format, array( 'list', 'ranked_list', 'classic_list', 'gallery' ), true ) ) {
		ob_start();

		snax_get_template_part( 'fb-instant-articles/content-list' );

		$extra_content .= ob_get_clean();

		// Snax items are not assigned at the time of list creation.
		if ( empty( $content ) && empty( $extra_content ) ) {
			$extra_content .= ' ';
		}
	}

	$content .= $extra_content;

	return $content;
}
