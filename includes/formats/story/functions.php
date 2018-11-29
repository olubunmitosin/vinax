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

add_action( 'snax_handle_text_submission', 'snax_process_text_submission', 10, 2 );

/**
 * Text submission handler
 *
 * @param array $data             Text data.
 * @param WP    $request          Request object.
 */
function snax_process_text_submission( $data, $request ) {
	$post_id = snax_create_text( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new text post
 *
 * @param array $data   Text data.
 *
 * @return int          Created post id.
 */
function snax_create_text( $data ) {
	$defaults = array(
		'id' 			=> 0,
		'title'         => '',
		'description'   => '',
		'category_id'   => array(),
		'tags'          => '',
		'author'        => get_current_user_id(),
		'status'        => 'pending',
		'ref_link'      => '',
	);

	$data = wp_parse_args( $data, $defaults );

	$author_id  = (int) $data['author'];
	$status 	= $data['status'];
	$content 	= $data['description'];

	$content = force_balance_tags( $content );

	// Convert image to figure.
	$converted = snax_convert_format_elements( $content );

	$content 	= $converted['content'];
	$media_ids 	= $converted['media_ids'];

	// We build img/a markup so we can allowe here extra attributes.
	$extra_allowed_html = array(
		'img' => array(
			'src' 	        => true,
			'data-src'      => true,
			'srcset'	    => true,
			'data-srcset'   => true,
			'class'	        => true,
			'alt' 	        => true,
			'width'		    => true,
			'height'	    => true,
			'sizes'	        => true,
		),
		'a' => array(
			'href' 		    => true,
			'class' 	    => true,
			'rel' 		    => true,
			'target'	    => true,
		),
		'blockquote' => array(
			'class' 	    => true,
		),
	);
	$content = snax_kses_post( $content, $extra_allowed_html );

	$content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );

	$content = apply_filters( 'snax_text_processed_content', $content );

	$new_post = array(
		'post_title'    => wp_strip_all_tags( $data['title'] ),
		'post_content'  => $content,
		'post_author'   => $author_id,
		'post_status'   => $status,
		'post_type'     => 'post',
		'ID'			=> $data['id'],
	);

	add_filter( 'snax_is_format_being_published', '__return_true' );
	$post_id = wp_insert_post( $new_post );
	remove_filter( 'snax_is_format_being_published', '__return_true' );

	if ( 0 === $post_id ) {
		return new WP_Error( 'snax_text_creating_failed', esc_html__( 'Some errors occured while creating text.', 'snax' ) );
	}

	// Assign media to post.
	foreach ( $media_ids as $media_index => $media_id ) {
		// Attach media to post.
		wp_update_post( array(
			'ID'            => $media_id,
			'post_parent'   => $post_id,
		) );
	}

	// Set featured image.
	if ( ! snax_is_featured_media_field_disabled( 'text' ) ) {
		$featured_image = snax_get_format_featured_image( 'text', $author_id, $data['id'] );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

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

	$format = 'text';

	// Format.
	snax_set_post_format( $post_id, $format );


	do_action( 'snax_post_added', $post_id, 'text' );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_text_featured_media_field() {
	$default = snax_get_legacy_featured_media_required_setting();

	return apply_filters( 'snax_text_featured_media_field', get_option( 'snax_text_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_text_show_featured_media() {
	$default = snax_get_legacy_show_featured_media_setting( 'text' );

	return 'standard' === apply_filters( 'snax_text_show_featured_media', get_option( 'snax_text_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_text_show_featured_media_field() {
	return 'disabled' !== snax_text_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_text_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_text_category_field', get_option( 'snax_text_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_text_show_category_field() {
	return 'disabled' !== snax_text_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_text_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_text_category_multi', get_option( 'snax_text_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_text_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_text_category_whitelist', get_option( 'snax_text_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_text_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_text_category_auto_assign', get_option( 'snax_text_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_text_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_text_allow_snax_authors_to_add_referrals', get_option( 'snax_text_allow_snax_authors_to_add_referrals', $default ) );
}
