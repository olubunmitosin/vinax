<?php
/**
 * Front Hooks
 *
 * @package snax
 * @subpackage Hooks
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Content.
add_filter( 'the_content', 'snax_render_quiz' );
add_filter( 'the_posts',   'snax_generate_quiz_pagination', 10, 2 );

// Assets.
add_action( 'wp_enqueue_scripts', 'snax_quiz_enqueue_scripts' );
