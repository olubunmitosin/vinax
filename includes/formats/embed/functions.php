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

add_action( 'snax_handle_embed_submission', 'snax_process_embed_submission', 10, 2 );

/**
 * Embed submission handler
 *
 * @param array $data             Embed data.
 * @param WP    $request          Request object.
 */
function snax_process_embed_submission( $data, $request ) {
	$post_id = snax_create_embed( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new embed post
 *
 * @param array $data   Embed data.
 *
 * @return int          Created post id.
 */
function snax_create_embed( $data ) {
	$format = 'embed';
	$post_format = false;
	$embed_provider_name = '';

	$defaults = array(
		'id'			=> 0,
		'title'         => '',
		'description'   => '',
		'category_id'   => array(),
		'author'        => get_current_user_id(),
		'status'		=> 'pending',
		'source'        => '',
		'ref_link'      => '',
	);

	$data = wp_parse_args( $data, $defaults );

	$author_id  = (int) $data['author'];
	$status 	= $data['status'];

	$orphans    = snax_get_user_orphan_items( $format, $author_id );
	$media_url  = '';

	// We loop over orphans but should be only one.
	// If there are more orphans we will use embed from the last one.
	// At the end, remove all orphans.
	foreach ( $orphans as $orphan ) {
		$media_url = snax_get_first_url_in_content( $orphan );
		$embed_provider_name = get_post_meta( $orphan->ID, '_snax_embed_provider_name', true );

		$post_format = get_post_format( $orphan );

		wp_delete_post( $orphan->ID, true );
	}

	// Prepend media to post content.
	if ( $media_url ) {
		$media = snax_get_content_media_html( $media_url, $data['source'], $format );

		$data['description'] = $media . "\n\n" . $data['description'];
	}

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
		return new WP_Error( 'snax_embed_creating_failed', esc_html__( 'Some errors occured while creating embed.', 'snax' ) );
	}

	// Set featured image.
	if ( ! snax_is_featured_media_field_disabled( 'embed' ) ) {
		$featured_image = snax_get_format_featured_image( 'embed', $author_id, $data['id'] );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

			// Attach featured media to item (Media Library, the "Uploaded to" column).
			wp_update_post( array(
				'ID'            => $featured_image->ID,
				'post_parent'   => $post_id,
			) );

			snax_reset_format_featured_image( $featured_image );
		}
	}

	// Referral link.
	update_post_meta( $post_id, '_snax_ref_link', $data['ref_link'] );

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

	// Set WP post format.
	if ( $post_format ) {
		set_post_format( $post_id, $post_format );
	}

	// Set post metadata.
	snax_set_post_format( $post_id, $format );
	add_post_meta( $post_id, '_snax_embed_provider_name', $embed_provider_name );


	do_action( 'snax_post_added', $post_id, 'embed' );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_embed_featured_media_field() {
	// Before 6.0 image had no the Featured Media field.
	$default = 'disabled';

	return apply_filters( 'snax_embed_featured_media_field', get_option( 'snax_embed_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_embed_show_featured_media() {
	$default = snax_get_legacy_show_featured_media_setting( 'embed' );

	return 'standard' === apply_filters( 'snax_embed_show_featured_media', get_option( 'snax_embed_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_embed_show_featured_media_field() {
	return 'disabled' !== snax_embed_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_embed_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_embed_category_field', get_option( 'snax_embed_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_embed_show_category_field() {
	return 'disabled' !== snax_embed_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_embed_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_embed_category_multi', get_option( 'snax_embed_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_embed_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_embed_category_whitelist', get_option( 'snax_embed_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_embed_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_embed_category_auto_assign', get_option( 'snax_embed_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_embed_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_embed_allow_snax_authors_to_add_referrals', get_option( 'snax_embed_allow_snax_authors_to_add_referrals', $default ) );
}
