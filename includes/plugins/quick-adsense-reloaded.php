<?php
/**
 * WP Quads plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'init',             'snax_quads_register_ad_locations' );
add_action( 'snax_after_item',  'snax_quads_render_ad_location', 10, 2 );
add_filter( 'quads_post_types', 'snax_quads_post_types' );


/**
 * Register custom ad locations
 */
function snax_quads_register_ad_locations() {
	if ( ! function_exists( 'quads_register_ad' ) ) {
		return;
	}

	quads_register_ad( array(
		'location'    => 'snax_after_item_1',
		'description' => esc_html__( 'After Snax Item', 'snax' ) . ' #1',
	) );

	quads_register_ad( array(
		'location'    => 'snax_after_item_2',
		'description' => esc_html__( 'After Snax Item', 'snax' ) . ' #2',
	) );

	quads_register_ad( array(
		'location'    => 'snax_after_item_3',
		'description' => esc_html__( 'After Snax Item', 'snax' ) . ' #3',
	) );
}

/**
 * Render ad location
 *
 * @param WP_Post $post         Post object.
 * @param int     $index        Current index inside the loop.
 */
function snax_quads_render_ad_location( $post, $index ) {
	$final_index = $index + 1;

	switch ( $final_index ) {
		case 1:
			if ( quads_has_ad( 'snax_after_item_1' ) ) {
				snax_get_template_part( 'quads/after-item-1' );
			}

			break;

		case 2:
			if ( quads_has_ad( 'snax_after_item_2' ) ) {
				snax_get_template_part( 'quads/after-item-2' );
			}
			break;

		case 3:
			if ( quads_has_ad( 'snax_after_item_3' ) ) {
				snax_get_template_part( 'quads/after-item-3' );
			}
			break;

		default:
			break;
	}
}

/*
 * Add WP QUADS support for our custom post types
 *
 * @param array $r      Array of post type names.
 * @return array
 */
function snax_quads_post_types( $r ) {
	$quiz = snax_get_quiz_post_type();

	$r[ $quiz ] = $quiz;

	return $r;
}