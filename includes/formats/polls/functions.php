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

add_action( 'snax_handle_classic_poll_submission',      'snax_process_classic_poll_submission', 10, 2 );
add_action( 'snax_handle_versus_poll_submission',       'snax_process_versus_poll_submission', 10, 2 );
add_action( 'snax_handle_binary_poll_submission',       'snax_process_binary_poll_submission', 10, 2 );

/**
 * Classic poll submission handler
 *
 * @param array $data             Text data.
 * @param WP    $request          Request object.
 */
function snax_process_classic_poll_submission( $data, $request ) {
	$post_id = snax_create_poll( 'classic', $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Versus poll submission handler
 *
 * @param array $data             Text data.
 * @param WP    $request          Request object.
 */
function snax_process_versus_poll_submission( $data, $request ) {
	$post_id = snax_create_poll( 'versus', $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Binary poll submission handler
 *
 * @param array $data             Text data.
 * @param WP    $request          Request object.
 */
function snax_process_binary_poll_submission( $data, $request ) {
	$post_id = snax_create_poll( 'binary', $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new poll
 *
 * @param string $poll_type         Poll type.
 * @param array  $data              Post data.
 *
 * @return int          Created post id.
 */
function snax_create_poll( $poll_type, $data ) {
	$defaults = array(
		'title'         => '',
		'description'   => '',
		'category_id'   => array(),
		'author'        => get_current_user_id(),
		'status'        => 'pending',
	);

	$data = wp_parse_args( $data, $defaults );

	$author_id = (int) $data['author'];

	$poll_id = $data['id'];

	if ( $poll_id ) {
		$poll = get_post( $poll_id );
	} else {
		$poll = snax_get_user_draft_poll( $poll_type, $author_id );
	}

	if ( ! $poll ) {
		return new WP_Error( 'snax_poll_creating_failed', esc_html__( 'User draft poll not exists!.', 'snax' ) );
	}

	$post_id = $poll->ID;
	$status  = $data['status'];

	$content = snax_kses_post( $data['description'] );
	$content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );

	// New poll data.
	$post_data = array(
		'ID'            => $post_id,
		'post_title'    => wp_strip_all_tags( $data['title'] ),
		'post_content'  => $content,
		'post_status'   => $status,
		'post_type'     => snax_get_poll_post_type(),
	);

	// Update poll.
	add_filter( 'snax_is_format_being_published', '__return_true' );
	wp_insert_post( $post_data );
	remove_filter( 'snax_is_format_being_published', '__return_true' );

	// Answers set.
	$answers_set = filter_input( INPUT_POST, 'snax_answers_set', FILTER_SANITIZE_STRING );

	add_post_meta( $post_id, '_snax_answers_set', $answers_set );

	// Set featured image.
	if ( ! snax_is_featured_media_field_disabled( 'poll' ) ) {
		$featured_image = snax_get_format_featured_image( $poll_type . '_poll', $author_id, $post_id );

		if ( $featured_image ) {
			set_post_thumbnail( $post_id, $featured_image->ID );

			snax_reset_format_featured_image( $featured_image );
		}
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

	// Format.
	snax_set_post_format( $post_id, $poll_type . '_poll' );

	// Where poll was created?
	add_post_meta( $post_id, '_snax_origin', 'front' );


	do_action( 'snax_post_added', $post_id, 'poll_' . $poll_type );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_poll_featured_media_field() {
	$default = snax_get_legacy_featured_media_required_setting();

	return apply_filters( 'snax_poll_featured_media_field', get_option( 'snax_poll_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_poll_show_featured_media() {
	// We have both types, get first and use it as a default. Now both poll types have one common setting.
	$default = snax_get_legacy_show_featured_media_setting( 'trivia_poll' );

	return 'standard' === apply_filters( 'snax_poll_show_featured_media', get_option( 'snax_poll_show_featured_media', $default ) );
}

/**
 * Check whether to allow guests to play.
 *
 * @return bool
 */
function snax_poll_allow_guests_to_play() {
	// We have both types, get first and use it as a default. Now both poll types have one common setting.
	$default = 'standard';

	return 'standard' === apply_filters( 'snax_poll_allow_guests_to_play', get_option( 'snax_poll_allow_guests_to_play', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_poll_show_featured_media_field() {
	return 'disabled' !== snax_poll_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_poll_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_poll_category_field', get_option( 'snax_poll_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_poll_show_category_field() {
	return 'disabled' !== snax_poll_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_poll_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_poll_category_multi', get_option( 'snax_poll_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_poll_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_poll_category_whitelist', get_option( 'snax_poll_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_poll_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_poll_category_auto_assign', get_option( 'snax_poll_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_poll_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_poll_allow_snax_authors_to_add_referrals', get_option( 'snax_poll_allow_snax_authors_to_add_referrals', $default ) );
}
