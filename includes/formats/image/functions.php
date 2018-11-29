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

add_action( 'snax_handle_image_submission', 'snax_process_image_submission', 10, 2 );

/**
 * Image submission handler
 *
 * @param array $data             Image data.
 * @param WP    $request          Request object.
 */
function snax_process_image_submission( $data, $request ) {
	$post_id = snax_create_image( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new image post
 *
 * @param array $data   Image data.
 *
 * @return int          Created post id.
 */
function snax_create_image( $data ) {
	$format 		= 'image';		// Item format.
	$post_format	= 'image';		// WP post format.
	$media_id		= false;

	$defaults = array(
		'id' 			=> 0,
		'title'         => '',
		'source'        => '',
		'ref_link'      => '',
		'description'   => '',
		'category_id'   => array(),
		'tags'          => '',
		'author'        => get_current_user_id(),
		'status'        => 'pending',
	);

	$data = wp_parse_args( $data, $defaults );

	$author_id  = (int) $data['author'];
	$status 	= $data['status'];

	$orphans = snax_get_user_orphan_items( $format, $data['author'] );

	// We loop over orphans but should be only one.
	// If are more orphans we will use featrued image from last one.
	// At the end, remove all orphans.
	foreach ( $orphans as $orphan ) {
		$media_id = get_post_thumbnail_id( $orphan->ID );

		wp_delete_post( $orphan->ID, true );
	}

	// We need to use the full size for very high images.
	$media_meta = wp_get_attachment_metadata( $media_id );
	$media_size = 'large';
	if ( $media_meta && $media_meta['height'] > 1024 && $media_meta['height'] > $media_meta['width'] ) {
		$media_size = 'full';
	}

	// Prepend media to post content.
	if ( $media_id ) {
		$img = wp_get_attachment_image( $media_id, $media_size );
		$img = str_replace( 'class="', 'class="aligncenter snax-figure-content ', $img );

		global $content_width;

		$figure = '[caption class="snax-figure" align="aligncenter" width="' . intval( $content_width ) . '"]';
		$figure .= $img;

		if ( ! empty( $data['source'] ) ) {
			$figure .= sprintf( '<a class="snax-figure-source" href="%s" rel="nofollow" target="_blank">%s</a>', esc_url( $data['source'] ), esc_url( $data['source'] ) );
		}

		$figure .= '[/caption]';

		$figure = apply_filters( 'snax_image_post_content', $figure, $media_id );

		$data['description'] = $figure . "\n\n" . $data['description'];
	}

	// We build img/a markup so we can allowe here extra attributes.
	$extra_allowed_html = array(
		'img' => array(
			'src' 		=> true,
			'class'		=> true,
			'alt' 		=> true,
			'width'		=> true,
			'height'	=> true,
			'srcset'	=> true,
		),
		'a' => array(
			'href' 		=> true,
			'class' 	=> true,
			'rel' 		=> true,
			'target'	=> true,
		),
	);

	$content = snax_kses_post( $data['description'], $extra_allowed_html );

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
		return new WP_Error( 'snax_image_creating_failed', esc_html__( 'Some errors occured while creating image.', 'snax' ) );
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

	// Set featured image.
	if ( snax_is_featured_media_field_disabled( 'image' ) ) {
		snax_set_post_featured_image( $post_id, $media_id );
	} else {
		$featured_image = snax_get_format_featured_image( 'image', $author_id, $data['id'] );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

			snax_reset_format_featured_image( $featured_image );
		} else {
			snax_set_post_featured_image( $post_id, $media_id );
		}
	}

	// Set WP post format.
	if ( $post_format ) {
		set_post_format( $post_id, $post_format );
	}

	// Format.
	snax_set_post_format( $post_id, $format );

	do_action( 'snax_post_added', $post_id, 'image' );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_image_featured_media_field() {
	// Before 6.0 image had no the Featured Media field.
	$default = 'disabled';

	return apply_filters( 'snax_image_featured_media_field', get_option( 'snax_image_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_image_show_featured_media() {
	$default = snax_get_legacy_show_featured_media_setting( 'image' );

	return 'standard' === apply_filters( 'snax_image_show_featured_media', get_option( 'snax_image_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_image_show_featured_media_field() {
	return 'disabled' !== snax_image_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_image_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_image_category_field', get_option( 'snax_image_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_image_show_category_field() {
	return 'disabled' !== snax_image_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_image_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_image_category_multi', get_option( 'snax_image_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_image_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_image_category_whitelist', get_option( 'snax_image_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_image_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_image_category_auto_assign', get_option( 'snax_image_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_image_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_image_allow_snax_authors_to_add_referrals', get_option( 'snax_image_allow_snax_authors_to_add_referrals', $default ) );
}
