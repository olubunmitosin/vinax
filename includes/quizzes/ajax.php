<?php
/**
 * Front Ajax Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Load quiz result
 */
function snax_ajax_load_quiz_result() {
	$quiz_id = filter_input( INPUT_POST, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! $quiz_id ) {
		snax_ajax_response_error( 'Quiz id not set!' );
		exit;
	}

	if ( ! snax_is_quiz( $quiz_id ) ) {
		snax_ajax_response_error( 'This is not a quiz!' );
		exit;
	}

	$answers = filter_input( INPUT_POST, 'answers', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

	// Sanitize.
	$answers = array_map( 'absint', $answers );

	$quiz_summary = filter_input( INPUT_POST, 'summary', FILTER_SANITIZE_STRING );

	$html = '';

	// Trivia.
	if ( snax_is_trivia_quiz( $quiz_id ) ) {
		$html = snax_get_trivia_quiz_result( $answers, $quiz_id, $quiz_summary );
	}

	// Personality.
	if ( snax_is_personality_quiz( $quiz_id ) ) {
		$html = snax_get_personality_quiz_result( $answers, $quiz_id, $quiz_summary );
	}

	$response_args = array(
		'html' => $html,
	);

	snax_ajax_response_success( 'Result template generated successfully.', $response_args );
	exit;
}
