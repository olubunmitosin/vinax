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
 * Load poll result
 */
function snax_ajax_save_poll_answer() {
	$poll_id = filter_input( INPUT_POST, 'poll_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! $poll_id ) {
		snax_ajax_response_error( 'Poll id not set!' );
		exit;
	}

	if ( ! snax_is_poll( $poll_id ) ) {
		snax_ajax_response_error( 'This is not a poll!' );
		exit;
	}

	$author_id = (int) filter_input( INPUT_POST, 'author_id', FILTER_SANITIZE_NUMBER_INT );

	$question_id = filter_input( INPUT_POST, 'question_id', FILTER_SANITIZE_NUMBER_INT );
	$answer_id   = filter_input( INPUT_POST, 'answer_id', FILTER_SANITIZE_NUMBER_INT );
	$add_points  = filter_input( INPUT_POST, 'add_points', FILTER_SANITIZE_NUMBER_INT );
	if ( 1 === (int) $add_points ) {
		$res = snax_poll_add_answer( $poll_id, $author_id, $question_id, $answer_id );
	} else {
		$res = true;
	}

	if ( is_wp_error( $res ) ) {
		snax_ajax_response_error( 'Poll answer not added!', array(
			'error_code'    => $res->get_error_code(),
			'error_message' => $res->get_error_message(),
		) );
		exit;
	}

	$results 				= snax_get_poll_results( $poll_id );
	$results['shareHTML'] 	= snax_get_poll_share_links( $poll_id, $question_id, $answer_id );

	$response_args = array(
		'results' 		=> $results,
	);

	snax_ajax_response_success( 'Poll saved and template generated successfully.', $response_args );
	exit;
}
