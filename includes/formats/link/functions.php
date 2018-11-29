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

add_filter( 'snax_get_default_options',     'snax_link_format_options' );
add_filter( 'snax_get_formats',             'snax_register_link_format' );
add_action( 'snax_handle_link_submission',  'snax_process_link_submission', 10, 2 );

/**
 * Set the format active
 *
 * @param array $options    Options.
 *
 * @return array
 */
function snax_link_format_options( $options ) {
	$options['snax_active_formats'][] = 'link';

	return $options;
}

/**
 * Register the format
 *
 * @param array $formats        List of formats.
 *
 * @return array
 */
function snax_register_link_format( $formats ) {
	$format_var = snax_get_url_var( 'format' );

	$formats['link'] = array(
		'labels'		=> array(
			'name' 			=> __( 'Link', 'snax' ),
			'add_new'		=> __( 'Link', 'snax' ),
		),
		'description'	=> __( 'External site\'s link', 'snax' ),
		'position'		=> 45,
		'url'           => add_query_arg( $format_var, 'link' ),
	);

	return $formats;
}

/**
 * Link submission handler
 *
 * @param array $data             Link data.
 * @param WP    $request          Request object.
 */
function snax_process_link_submission( $data, $request ) {
	$post_id = snax_create_link( $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new link post
 *
 * @param array $data   Link data.
 *
 * @return int          Created post id.
 */
function snax_create_link( $data ) {
	$format = 'link';
	$post_format = 'link';
	$link_provider_name = '';

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

	$url     = filter_input( INPUT_POST, 'snax-post-url', FILTER_SANITIZE_URL );
	$url_tag = '';

	if ( ! empty( $url ) ) {
		$url_tag = sprintf( '<a href="%s">%s</a>', $url, $url );
	}

	$new_post = array(
		'post_title'    => wp_strip_all_tags( $data['title'] ),
		'post_content'  => $url_tag . snax_kses_post( $data['description'] ),
		'post_author'   => $author_id,
		'post_status'   => $status,
		'post_type'     => 'post',
		'ID'			=> $data['id'],
	);

	add_filter( 'snax_is_format_being_published', '__return_true' );
	$post_id = wp_insert_post( $new_post );
	remove_filter( 'snax_is_format_being_published', '__return_true' );

	if ( 0 === $post_id ) {
		return new WP_Error( 'snax_link_creating_failed', esc_html__( 'Some errors occured while creating link.', 'snax' ) );
	}

	// Set featured image.
	if ( ! snax_is_featured_media_field_disabled( 'link' ) ) {
		$featured_image = snax_get_format_featured_image( 'link', $author_id, $data['id'] );

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
	add_post_meta( $post_id, '_snax_link_provider_name', $link_provider_name );


	do_action( 'snax_post_added', $post_id, 'link' );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_link_featured_media_field() {
	$default = 'required';

	return apply_filters( 'snax_link_featured_media_field', get_option( 'snax_link_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_link_show_featured_media() {
	$default = 'optional';

	return 'standard' === apply_filters( 'snax_link_show_featured_media', get_option( 'snax_link_show_featured_media', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_link_show_featured_media_field() {
	return 'disabled' !== snax_link_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_link_category_field() {
	$default = 'optional';

	return apply_filters( 'snax_link_category_field', get_option( 'snax_link_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_link_show_category_field() {
	return 'disabled' !== snax_link_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_link_multiple_categories_selection() {
	$default = 'standard';

	return 'standard' === apply_filters( 'snax_link_category_multi', get_option( 'snax_link_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_link_get_category_whitelist() {
	$default = array( '' => '' );

	return apply_filters( 'snax_link_category_whitelist', get_option( 'snax_link_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_link_get_category_auto_assign() {
	$default = array( '' => '' );

	return apply_filters( 'snax_link_category_auto_assign', get_option( 'snax_link_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_link_allow_snax_authors_to_add_referrals() {
	$default = 'none';

	return 'standard' === apply_filters( 'snax_link_allow_snax_authors_to_add_referrals', get_option( 'snax_link_allow_snax_authors_to_add_referrals', $default ) );
}

/**
 * Return demo posts data
 *
 * @return array
 */
function snax_link_get_demos() {
	$post_ids = snax_get_demo_post_ids( 'link' );

	$demos_data = array();

	foreach ( $post_ids as $post_id ) {
		$post = get_post( $post_id );

		if ( 'link' !== get_post_format( $post ) ) {
			continue;
		}

		$url = get_url_in_content( $post->post_content );

		if ( empty( $url ) ) {
			continue;
		}

		$demos_data[] = array(
			'post_id' => $post_id,
			'url'     => $url,
		);
	}

	return $demos_data;
}
