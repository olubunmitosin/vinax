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

add_action( 'snax_handle_meme_submission', 'snax_process_meme_submission', 10, 2 );

/**
 * Meme submission handler
 *
 * @param array $data             Meme data.
 * @param WP    $request          Request object.
 */
function snax_process_meme_submission( $data, $request ) {
	$meme_raw = filter_input( INPUT_POST, 'snax-post-meme' );
	$meme_filtered = explode( ',', $meme_raw );
	$meme_decoded = base64_decode( $meme_filtered[1] );

	$data['meme'] = $meme_decoded;

	$post_id = snax_create_meme( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new meme post
 *
 * @param array $data   Meme data.
 *
 * @return int          Created post id.
 */
function snax_create_meme( $data ) {
	$format 			= 'meme';		// Item format.
	$post_format		= 'image';		// WP post format.
	$meme_background_id	= false;
	$meme_tempalte		= '';

	$defaults = array(
		'id'         => 0,
		'title'         => '',
		'source'        => '',
		'ref_link'      => '',
		'description'   => '',
		'meme'   		=> '',
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
		$meme_background_id = get_post_thumbnail_id( $orphan->ID );
		$meme_template = get_post_meta( $orphan->ID, '_snax_meme_template', true );
		wp_delete_post( $orphan->ID, true );
	}

	$meme_background = get_post( $meme_background_id );

	// Add meta for further processing.
	add_post_meta( $meme_background->ID, 'snax_meme_background', true );

	// Save meme image (Base 64).
	$upload_dir 		= wp_upload_dir();
	$upload_dest_dir 	= trailingslashit( $upload_dir['path'] );
	$meme_filename 		= 'meme-' . $meme_background->post_title . uniqid() . '.jpg';
	$meme_path			= $upload_dest_dir . $meme_filename;

	// Save in uploads dir.
	@file_put_contents( $meme_path, $data['meme'] );

	$attachment = array(
		'post_mime_type'	=> 'image/jpeg',
		'post_title' 		=> wp_strip_all_tags( $data['title'] ),
		'post_content' 		=> '',
		'post_status' 		=> 'inherit',
	);

	$meme_id = wp_insert_attachment( $attachment, $meme_path );

	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$media_data = wp_generate_attachment_metadata( $meme_id, $meme_path );
	wp_update_attachment_metadata( $meme_id,  $media_data );

	// Prepend media to post content.
	if ( $meme_id ) {
		$img = wp_get_attachment_image( $meme_id, 'large' );
		$img = str_replace( 'class="', 'class="aligncenter snax-figure-content ', $img );

		global $content_width;

		$figure = '[caption class="snax-figure" align="aligncenter" width="' . intval( $content_width ) . '"]';
		$figure .= $img;

		if ( ! empty( $data['source'] ) ) {
			$figure .= sprintf( '<a class="snax-figure-source" href="%s" rel="nofollow" target="_blank">%s</a>', esc_url( $data['source'] ), esc_url( $data['source'] ) );
		}

		$figure .= '[/caption]';

		$data['description'] = $figure . "\n\n" . $data['description'];
	}

	// We build img/a markup so we can allow here extra attributes.
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
		return new WP_Error( 'snax_meme_creating_failed', esc_html__( 'Some errors occured while creating meme.', 'snax' ) );
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

	// Attach original image to post.
	if ( $meme_background_id ) {
		// Attach media to item (Media Library, the "Uploded to" column).
		wp_update_post( array(
			'ID'            => $meme_background_id,
			'post_parent'   => $post_id,
		) );
	}


	// Set featured image.
	if ( snax_is_featured_media_field_disabled( 'meme' ) ) {
		snax_set_post_featured_image( $post_id, $meme_id );
	} else {
		$featured_image = snax_get_format_featured_image( 'meme', $author_id, $data['id'] );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

			snax_reset_format_featured_image( $featured_image );
		} else {
			snax_set_post_featured_image( $post_id, $meme_id );
		}
	}

	// Set WP post format.
	if ( $post_format ) {
		set_post_format( $post_id, $post_format );
	}

	add_post_meta( $post_id, '_snax_meme_template', $meme_template );

	// Format.
	snax_set_post_format( $post_id, $format );


	do_action( 'snax_post_added', $post_id, 'meme' );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_meme_featured_media_field() {
	// Before 6.0 meme had no the Featured Media field.
	$default = 'disabled';

	return apply_filters( 'snax_meme_featured_media_field', get_option( 'snax_meme_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_meme_show_featured_media() {
	$default = snax_get_legacy_show_featured_media_setting( 'meme' );

	return 'standard' === apply_filters( 'snax_meme_show_featured_media', get_option( 'snax_meme_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_meme_show_featured_media_field() {
	return 'disabled' !== snax_meme_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_meme_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_meme_category_field', get_option( 'snax_meme_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_meme_show_category_field() {
	return 'disabled' !== snax_meme_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_meme_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_meme_category_multi', get_option( 'snax_meme_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_meme_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_meme_category_whitelist', get_option( 'snax_meme_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_meme_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_meme_category_auto_assign', get_option( 'snax_meme_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_meme_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_meme_allow_snax_authors_to_add_referrals', get_option( 'snax_meme_allow_snax_authors_to_add_referrals', $default ) );
}