<?php
/**
 * Admin Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Load stylesheets.
 */
function snax_admin_enqueue_styles() {}

/**
 * Load javascripts.
 */
function snax_admin_enqueue_scripts() {}

/**
 * Register a page for new quiz type selection
 */
function snax_register_new_quiz_page() {
	global $submenu;

	$parent_slug = 'edit.php?post_type=' . snax_get_quiz_post_type();

	if ( ! isset( $submenu[ $parent_slug ] ) ) {
		return;
	}

	$menu_title = $submenu[ $parent_slug ][10][0];
	$capability = $submenu[ $parent_slug ][10][1];

	// Hide default "Add New" link.
	unset( $submenu[ $parent_slug ][10] );

	// Add a new "Add New" page.
	add_submenu_page(
		$parent_slug,
		$menu_title,
		$menu_title,
		$capability,
		'new-quiz',
		'snax_render_new_quiz_page'
	);
}

/**
 * Render a page for new quiz type selection
 */
function snax_render_new_quiz_page() {
	snax_get_template_part( 'quizzes/new-quiz' );
}

/**
 * Override default "Add New" url for a quiz post type
 *
 * @param string $url     The complete admin area URL including scheme and path.
 * @param string $path    Path relative to the admin area URL. Blank string if no path is specified.
 *
 * @return string
 */
function snax_redirect_to_new_quiz_page( $url, $path ) {
	if ( 'post-new.php?post_type=' . snax_get_quiz_post_type() === $path ) {
		$url = snax_get_new_quiz_page_url();
	}

	return $url;
}

/**
 * Return url to the new quiz page
 *
 * @return string
 */
function snax_get_new_quiz_page_url() {
	return 'edit.php?post_type=' . snax_get_quiz_post_type() . '&page=new-quiz';
}

/**
 * Return url to the new Trivia quiz page
 *
 * @return string
 */
function snax_get_new_trivia_quiz_page_url() {
	return admin_url() . 'post-new.php?post_type=snax_quiz&type=' . snax_get_trivia_quiz_type();
}

/**
 * Return url to the new Personality quiz page
 *
 * @return string
 */
function snax_get_new_personality_quiz_page_url() {
	return admin_url() . 'post-new.php?post_type=snax_quiz&type=' . snax_get_personality_quiz_type();
}

/**
 * Render Quiz Form
 *
 * @param string $post		Post object.
 */
function snax_render_quiz_form( $post ) {
	$quiz_post_type = snax_get_quiz_post_type();

	if ( get_post_type( $post ) !== $quiz_post_type ) {
		return;
	}

	// Get type from url.
	$quiz_type = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );


	// If not set, read from meta.
	if ( ! $quiz_type ) {
		$quiz_type = snax_get_quiz_type( $post );
	}

	// Fallback to default type.
	if ( ! snax_is_valid_quiz_type( $quiz_type ) ) {
		$quiz_type = 'trivia';
	}

	// Load CSS.
	wp_enqueue_style( 'snax-quiz', snax_get_includes_url() . 'quizzes/admin/css/quiz.css', array(), '1.0' );

	// Load JS.
	wp_enqueue_media();
	wp_enqueue_script( 'snax-quiz-common', 		snax_get_includes_url() . 'quizzes/admin/js/common.js', array( 'jquery' ), snax_get_version() );
	wp_enqueue_script( 'snax-' . $quiz_type . '-quiz', 	snax_get_includes_url() . 'quizzes/admin/js/' . $quiz_type . '-quiz.js', array( 'snax-quiz-common', 'jquery', 'jquery-ui-sortable' ), snax_get_version() );

	$quiz_config = array(
		'i18n' => array(
			'yes'	=> __( "Yes", 'snax' ),
			'no'	=> __( "No", 'snax' ),
		),
	);
	wp_localize_script( 'snax-' . $quiz_type . '-quiz', 'snax_' . $quiz_type . '_quiz_config', wp_json_encode( $quiz_config ) );

	// Load template.
	snax_get_template_part( 'quizzes/' . $quiz_type . '/form/quiz-tpl' );
}

/**
 * Save quiz.
 *
 * @param int 	  $post_id The post ID.
 * @param WP_Post $post The post object.
 * @param bool 	  $update Whether this is an existing post being updated or not.
 */
function snax_save_quiz_form( $post_id, $post, $update ) {
	if ( ! snax_is_quiz( $post ) ) {
		return;
	}

	$quiz_type = filter_input( INPUT_POST, 'snax_quiz', FILTER_SANITIZE_STRING );

	// Is valid type?
	if ( ! snax_is_valid_quiz_type( $quiz_type ) ) {
		return;
	}

	// Save quiz type.
	update_post_meta( $post_id, '_snax_quiz_type', $quiz_type );

	// Save quiz format.
	snax_set_post_format( $post_id, $quiz_type . '_quiz' );

	// Save settings.
	snax_save_quiz_settings( $post_id, $post, $update );
}

/**
 * Save quiz settings.
 *
 * @param int 	  $post_id The post ID.
 * @param WP_Post $post The post object.
 * @param bool 	  $update Whether this is an existing post being updated or not.
 */
function snax_save_quiz_settings( $post_id, $post, $update ) {
	$reveal_correct_wrong_answers 	= filter_input( INPUT_POST, 'snax_reveal_correct_wrong_answers', FILTER_SANITIZE_STRING );
	$one_question_per_page 			= filter_input( INPUT_POST, 'snax_one_question_per_page', FILTER_SANITIZE_STRING );
	$shuffle_questions 				= filter_input( INPUT_POST, 'snax_shuffle_questions', FILTER_SANITIZE_STRING );
	$questions_per_quiz 			= filter_input( INPUT_POST, 'snax_questions_per_quiz', FILTER_SANITIZE_STRING );
	$shuffle_answers 				= filter_input( INPUT_POST, 'snax_shuffle_answers', FILTER_SANITIZE_STRING );
	$start_quiz 					= filter_input( INPUT_POST, 'snax_start_quiz', FILTER_SANITIZE_STRING );
	$play_again 					= filter_input( INPUT_POST, 'snax_play_again', FILTER_SANITIZE_STRING );
	$share_results 					= filter_input( INPUT_POST, 'snax_share_results', FILTER_SANITIZE_STRING );
	$share_to_unlock 				= filter_input( INPUT_POST, 'snax_share_to_unlock', FILTER_SANITIZE_STRING );

	// Save settings.
	update_post_meta( $post_id, '_snax_reveal_correct_wrong_answers', $reveal_correct_wrong_answers );
	update_post_meta( $post_id, '_snax_one_question_per_page', $one_question_per_page );
	update_post_meta( $post_id, '_snax_shuffle_questions', $shuffle_questions );
	update_post_meta( $post_id, '_snax_questions_per_quiz', $questions_per_quiz );
	update_post_meta( $post_id, '_snax_shuffle_answers', $shuffle_answers );
	update_post_meta( $post_id, '_snax_start_quiz', $start_quiz );
	update_post_meta( $post_id, '_snax_play_again', $play_again );
	update_post_meta( $post_id, '_snax_share_results', $share_results );
	update_post_meta( $post_id, '_snax_share_to_unlock', $share_to_unlock );
}

/**
 * Create or update a question.
 *
 * @param array  $postarr	                An array of elements that make up a post to update or insert.
 * @param int    $media_id	                Optional. Media id assigned to the question.
 * @param string $answers_tpl               Optional. Answers template.
 * @param bool   $title_hide                Optional. Whether to hide title or not.
 * @param bool   $answers_labels_hide       Optional. Whether to hide answers labels or not.
 *
 * @return int|WP_Error		The post ID on success. WP_Error on failure.
 */
function snax_insert_question( $postarr, $media_id = 0, $answers_tpl = 'text', $title_hide = false, $answers_labels_hide = false ) {
	$defaults = array(
		'post_type' 	=> snax_get_question_post_type(),
		'post_status' 	=> 'publish',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	$post_id = wp_insert_post( $postarr, true );

	// Title hide.
	update_post_meta( $post_id, '_snax_title_hide', $title_hide );

	// Media.
	if ( $media_id ) {
		set_post_thumbnail( $post_id, $media_id );
	} elseif ( has_post_thumbnail( $post_id ) ) {
		delete_post_thumbnail( $post_id );
	}

	// Answers template.
	update_post_meta( $post_id, '_snax_answers_tpl', $answers_tpl );

	// Answers labels hide.
	update_post_meta( $post_id, '_snax_answers_labels_hide', $answers_labels_hide );

	return $post_id;
}

/**
 * Delete a question.
 *
 * @param array $postarr		An array of elements that make up a post to update or insert.
 *
 * @return WP_Post|WP_Error		The deleted post object on success. WP_Error on failure.
 */
function snax_delete_question( $postarr ) {
	$defaults = array(
		'ID' 	=> '',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	$deleted = wp_delete_post( $postarr['ID'], true );

	if ( false === $deleted ) {
		return new WP_Error( 'deletion_failed' );
	}

	return $deleted;
}

/**
 * Create or update an answer.
 *
 * @param array $postarr	An array of elements that make up a post to update or insert.
 * @param bool  $correct	Optional. Is a correct answer.
 * @param int   $media_id	Optional. Media id assigned to the question.
 *
 * @return int|WP_Error		The post ID on success. WP_Error on failure.
 */
function snax_insert_answer( $postarr, $correct = '', $media_id = 0 ) {
	$defaults = array(
		'post_type' 	=> snax_get_answer_post_type(),
		'post_status' 	=> 'publish',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	// Insert/update WP post.
	$post_id = wp_insert_post( $postarr, true );

	// Correct.
	update_post_meta( $post_id, '_snax_correct', $correct );

	// Media.
	if ( $media_id ) {
		set_post_thumbnail( $post_id, $media_id );
	} elseif ( has_post_thumbnail( $post_id ) ) {
		delete_post_thumbnail( $post_id );
	}

	return $post_id;
}

/**
 * Delete an answer.
 *
 * @param array $postarr		An array of elements that make up a post to update or insert.
 *
 * @return WP_Post|WP_Error		The deleted post object on success. WP_Error on failure.
 */
function snax_delete_answer( $postarr ) {
	$defaults = array(
		'ID' 	=> '',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	$deleted = wp_delete_post( $postarr['ID'], true );

	if ( false === $deleted ) {
		return new WP_Error( 'deletion_failed' );
	}

	return $deleted;
}

/**
 * Create or update a result.
 *
 * @param array $postarr	An array of elements that make up a post to update or insert.
 * @param int   $range_low	Optional. Low range value.
 * @param int   $range_high	Optional. High range value.
 * @param int   $media_id	Optional. Media id assigned to the result.
 *
 * @return int|WP_Error		The post ID on success. WP_Error on failure.
 */
function snax_insert_result( $postarr, $range_low = 0, $range_high = 0, $media_id = 0 ) {
	$defaults = array(
		'post_type' 	=> snax_get_result_post_type(),
		'post_status' 	=> 'publish',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	$post_id = wp_insert_post( $postarr, true );

	// Range.
	update_post_meta( $post_id, '_snax_range_low',  $range_low );
	update_post_meta( $post_id, '_snax_range_high', $range_high );

	// Media.
	if ( $media_id ) {
		set_post_thumbnail( $post_id, $media_id );
	} elseif ( has_post_thumbnail( $post_id ) ) {
		delete_post_thumbnail( $post_id );
	}

	return $post_id;
}

/**
 * Delete a result.
 *
 * @param array $postarr		An array of elements that make up a post to update or insert.
 *
 * @return WP_Post|WP_Error		The deleted post object on success. WP_Error on failure.
 */
function snax_delete_result( $postarr ) {
	$defaults = array(
		'ID' 	=> '',
	);

	$postarr = wp_parse_args( $postarr, $defaults );

	$deleted = wp_delete_post( $postarr['ID'], true );

	if ( false === $deleted ) {
		return new WP_Error( 'deletion_failed' );
	}

	return $deleted;
}