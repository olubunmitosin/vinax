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

add_action( 'snax_handle_trivia_quiz_submission',       'snax_process_trivia_quiz_submission', 10, 2 );
add_action( 'snax_handle_personality_quiz_submission',  'snax_process_personality_quiz_submission', 10, 2 );

/**
 * Trivia quiz submission handler
 *
 * @param array $data             Quiz data.
 * @param WP    $request          Request object.
 */
function snax_process_trivia_quiz_submission( $data, $request ) {
	$post_id = snax_create_quiz( 'trivia', $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Personality quiz submission handler
 *
 * @param array $data             Quiz data.
 * @param WP    $request          Request object.
 */
function snax_process_personality_quiz_submission( $data, $request ) {
	$post_id = snax_create_quiz( 'personality', $data );

	if ( ! is_wp_error( $post_id ) ) {
		$url_var = snax_get_url_var( 'post_submission' );
		$redirect_url = add_query_arg( $url_var, 'success', get_permalink( $post_id ) );

		$redirect_url = apply_filters( 'snax_new_post_redirect_url', $redirect_url, $post_id );

		$request->set_query_var( 'snax_redirect_to_url', $redirect_url );
	}
}

/**
 * Create new quiz
 *
 * @param string $quiz_type         Quiz type.
 * @param array  $data              Post data.
 *
 * @return int          Created post id.
 */
function snax_create_quiz( $quiz_type, $data ) {
	$defaults = array(
		'title'         => '',
		'description'   => '',
		'category_id'   => array(),
		'author'        => get_current_user_id(),
		'status'        => 'pending',
	);

	$data = wp_parse_args( $data, $defaults );

	$author_id = (int) $data['author'];

	$quiz_id = $data['id'];

	if ( $quiz_id ) {
		$quiz = get_post( $quiz_id );
	} else {
		$quiz = snax_get_user_draft_quizz( $quiz_type, $author_id );
	}

	if ( ! $quiz ) {
		return new WP_Error( 'snax_quiz_creating_failed', esc_html__( 'User draft quiz not exists!.', 'snax' ) );
	}

	$post_id = $quiz->ID;
	$status  = $data['status'];

	$content = snax_kses_post( $data['description'] );
	$content = preg_replace( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '', $content );

	// New quiz data.
	$post_data = array(
		'ID'            => $post_id,
		'post_title'    => wp_strip_all_tags( $data['title'] ),
		'post_content'  => $content,
		'post_status'   => $status,
		'post_type'     => snax_get_quiz_post_type(),
	);

	// Update quiz.
	add_filter( 'snax_is_format_being_published', '__return_true' );
	wp_insert_post( $post_data );
	remove_filter( 'snax_is_format_being_published', '__return_true' );

	// Set featured image.
	if ( ! snax_is_featured_media_field_disabled( 'quiz' ) ) {
		$featured_image = snax_get_format_featured_image( $quiz_type . '_quiz', $author_id, $post_id );

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
	snax_set_post_format( $post_id, $quiz_type . '_quiz' );

	// Where quiz was created?
	add_post_meta( $post_id, '_snax_origin', 'front' );


	do_action( 'snax_post_added', $post_id, 'quiz_' . $quiz_type );

	return $post_id;
}

/**
 * Return featured media field visibility type
 *
 * @return string
 */
function snax_quiz_featured_media_field() {
	$default = snax_get_legacy_featured_media_required_setting();

	return apply_filters( 'snax_quiz_featured_media_field', get_option( 'snax_quiz_featured_media_field', $default ) );
}

/**
 * Check whether to show the Featured Media on a single post
 *
 * @return bool
 */
function snax_quiz_show_featured_media() {
	// We have both types, get first and use it as a default. Now both quiz types have one common setting.
	$default = snax_get_legacy_show_featured_media_setting( 'trivia_quiz' );

	return 'standard' === apply_filters( 'snax_quiz_show_featured_media', get_option( 'snax_quiz_show_featured_media', $default ) );
}

/**
 * Check whether to allow guests to play.
 *
 * @return bool
 */
function snax_quiz_allow_guests_to_play() {
	$default = 'standard';

	return 'standard' === apply_filters( 'snax_quiz_allow_guests_to_play', get_option( 'snax_quiz_allow_guests_to_play', $default ) );
}

/**
 * Check whether to show the Featured Media field on form
 *
 * @return bool
 */
function snax_quiz_show_featured_media_field() {
	return 'disabled' !== snax_quiz_featured_media_field();
}

/**
 * Return the Category field visibility type
 *
 * @return string
 */
function snax_quiz_category_field() {
	$default = snax_get_legacy_category_required_setting();

	return apply_filters( 'snax_quiz_category_field', get_option( 'snax_quiz_category_field', $default ) );
}

/**
 * Check whether to show the Category field on form
 *
 * @return bool
 */
function snax_quiz_show_category_field() {
	return 'disabled' !== snax_quiz_category_field();
}

/**
 * Check whether to allow multiple categories selection
 *
 * @return bool
 */
function snax_quiz_multiple_categories_selection() {
	$default = snax_get_legacy_category_multi_setting();

	return 'standard' === apply_filters( 'snax_quiz_category_multi', get_option( 'snax_quiz_category_multi', $default ) );
}

/**
 * Return list of allowed categories to select during front end post creation
 *
 * @return array
 */
function snax_quiz_get_category_whitelist() {
	$default = snax_get_legacy_category_whitelist_setting();

	return apply_filters( 'snax_quiz_category_whitelist', get_option( 'snax_quiz_category_whitelist', $default ) );
}

/**
 * Return list of categories to be auto-assigned during front end post creation
 *
 * @return array
 */
function snax_quiz_get_category_auto_assign() {
	$default = snax_get_legacy_category_auto_assign_setting();

	return apply_filters( 'snax_quiz_category_auto_assign', get_option( 'snax_quiz_category_auto_assign', $default ) );
}

/**
 * Return the Referral link field visibility type
 *
 * @return string
 */
function snax_quiz_allow_snax_authors_to_add_referrals() {
	$default = snax_get_legacy_referrals_setting();

	return 'standard' === apply_filters( 'snax_quiz_allow_snax_authors_to_add_referrals', get_option( 'snax_quiz_allow_snax_authors_to_add_referrals', $default ) );
}
