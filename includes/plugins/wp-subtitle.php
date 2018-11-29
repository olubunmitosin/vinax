<?php
/**
 * WP Subtitle plugin integration
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'the_preview', 'snax_wp_subtitle_skip_revisions_in_preview', 1, 2 );

/**
 * Skips WP Subtitle replacing revisions in the preview
 *
 * @param WP_Post  $post
 * @param WP_Query $query
 *
 * @return WP_Post
 */
function snax_wp_subtitle_skip_revisions_in_preview( $post, $query ) {
	remove_filter( 'the_preview', array( 'WPSubtitle', 'the_preview' ), 10 );
	// removed the lines that make sure the subtitle is up to date, because they broke more important things.
	return $post;
}
