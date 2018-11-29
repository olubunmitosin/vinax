<?php
/**
 * Snax Format Functions
 *
 * @package snax
 * @subpackage Formats
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'snax_handle_gallery_submission',   'snax_process_gallery_submission', 10, 2 );
add_action( 'snax_post_published',              'snax_publish_gallery_items', 10, 2 );

/**
 * Gallery submission handler
 *
 * @param array $data             Gallery data.
 * @param WP    $request          Request object.
 */
function snax_process_gallery_submission( $data, $request ) {
	$post_id = snax_create_gallery( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new gallery post
 *
 * @param array $data   Gallery data.
 *
 * @return int          Created post id.
 */
function snax_create_gallery( $data ) {
	$defaults = array(
		'id' 			=> 0,
		'title'         => '',
		'description'   => '',
		'category_id'   => array(),
		'author'        => get_current_user_id(),
		'status'        => 'pending',
	);

	$data = wp_parse_args( $data, $defaults );

	$is_new_post = 0 === $data['id'];

	$author_id  = (int) $data['author'];
	$status 	= $data['status'];

	$new_post = array(
		'post_title'    => wp_strip_all_tags( $data['title'] ),
		'post_content'  => snax_kses_post( $data['description'] ),
		'post_author'   => $author_id,
		'post_status'   => $status,
		'post_type'     => 'post',
		'ID'			=> $data['id'],
	);

	add_filter( 'snax_is_format_being_published', '__return_true' );
	$post_id = wp_insert_post( $new_post );
	remove_filter( 'snax_is_format_being_published', '__return_true' );

	if ( 0 === $post_id ) {
		return new WP_Error( 'snax_gallery_creating_failed', esc_html__( 'Some errors occured while creating gallery.', 'snax' ) );
	}

	// Assign category.
	$category_id = $data['category_id'];

	if ( ! empty( $category_id ) ) {
		wp_set_post_categories( $post_id, $category_id );
	}

	// Reassign tags.
	snax_remove_post_tags( $post_id );

	$tags = $data['tags'];

	if ( ! empty( $tags ) ) {
		wp_set_post_tags( $post_id, $tags, true );
	}

	// Set gallery meta data.
	snax_set_post_format( $post_id, 'gallery' );


	if ( $is_new_post ) {
		snax_attach_user_orphan_items_to_post( $post_id, $data['author'] );
	}

	// Set featured image.
	if ( snax_is_featured_media_field_disabled( 'gallery' ) ) {
		snax_set_first_image_item_as_post_featured( $post_id );
	} else {
		$featured_image = snax_get_format_featured_image( 'gallery', $author_id, $data['id'] );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

			snax_reset_format_featured_image( $featured_image );
		} else {
			snax_set_first_image_item_as_post_featured( $post_id );
		}
	}

	do_action( 'snax_post_added', $post_id, 'gallery' );

	return $post_id;
}

/**
 * Publish gallery items
 */
function snax_publish_gallery_items( $post_id ) {
	if ( snax_is_post_a_gallery( $post_id ) ) {
		// Get pending items.
		$items = snax_get_items( $post_id, array(
			'post_status' => snax_get_item_pending_status(),
		) );

		// Publish them.
		foreach( $items as $item ) {
			wp_update_post( array(
				'ID'            => $item->ID,
				'post_status'   => snax_get_item_approved_status(),
			) );
		}
	}
}

/**
 * Check whether the post is a gallery
 *
 * @param int $post_id      Post id.
 *
 * @return bool
 */
function snax_is_post_a_gallery( $post_id = 0 ) {
	$format = snax_get_post_format( $post_id );

	return in_array( $format, array( 'gallery' ) );
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_gallery_featured_media_field() {
	// Before 6.0 gallery had no the Featured Media field.
	$default = 'disabled';

	return apply_filters( 'snax_gallery_featured_media_field', get_option( 'snax_gallery_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_gallery_show_featured_media() {
	$default = snax_get_legacy_show_featured_media_setting( 'gallery' );

	return 'standard' === apply_filters( 'snax_gallery_show_featured_media', get_option( 'snax_gallery_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_gallery_show_featured_media_field() {
	return 'disabled' !== snax_gallery_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_gallery_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_gallery_category_field', get_option( 'snax_gallery_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_gallery_show_category_field() {
	return 'disabled' !== snax_gallery_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_gallery_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_gallery_category_multi', get_option( 'snax_gallery_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_gallery_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_gallery_category_whitelist', get_option( 'snax_gallery_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_gallery_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_gallery_category_auto_assign', get_option( 'snax_gallery_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_gallery_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_gallery_allow_snax_authors_to_add_referrals', get_option( 'snax_gallery_allow_snax_authors_to_add_referrals', $default ) );
}
