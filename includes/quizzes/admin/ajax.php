<?php
/**
 * Admin Ajax Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Question CRUD
 */
function snax_ajax_sync_question() {
	$request_action = filter_input( INPUT_POST, 'request_action', FILTER_SANITIZE_STRING );

	// Delete question.
	if ( 'DELETE' === $request_action ) {
		$id = (int) filter_input( INPUT_GET, 'question_id', FILTER_SANITIZE_NUMBER_INT );

		// Delete question.
		$deleted = snax_delete_question( array(
			'ID' => $id,
		) );

		if ( is_wp_error( $deleted ) ) {
			snax_ajax_response_error( 'Question deletion failed!', array(
				'error_code'    => $deleted->get_error_code(),
				'error_message' => $deleted->get_error_message(),
			) );
			exit;
		}

		snax_ajax_response_success( 'Question deleted.' );
		exit;
	}

	// Add/Edit question.
	$request_body = file_get_contents('php://input');
	$data = json_decode( $request_body, true );

	$nonce = $data['security'];

	// Check ajax referer.
	if ( ! wp_verify_nonce( $nonce, 'quizzard-quiz' ) ) {
		die( '-1' );
	}

	$quiz_type = $data['quiz_type'];

	if ( ! $quiz_type ) {
		snax_ajax_response_error( 'Quiz type not set!' );
		exit;
	}

	if ( ! snax_is_valid_quiz_type( $quiz_type ) ) {
		snax_ajax_response_error( 'Invalid quiz type!' );
		exit;
	}

	$quiz_id = absint( $data['quiz_id'] );

	if ( ! $quiz_id ) {
		snax_ajax_response_error( 'Quiz id not set!' );
		exit;
	}

	$title = $data['title'];

	if ( ! $title ) {
		snax_ajax_response_error( 'Question title not set!' );
		exit;
	}

	$id 		 = absint( $data['id'] );
	$title_hide  = (bool) $data['title_hide'];
	$order 		 = absint( $data['order'] );
	$media 		 = $data['media'];
	$media_id 	 = (int) $media['id'];
	$answers_tpl = $data['answers_tpl'];
	$answers_labels_hide  = (bool) $data['answers_labels_hide'];

	// Create new question.
	$question_id = snax_insert_question( array(
		'ID'			=> $id,
		'post_title'	=> $title,
		'post_parent' 	=> $quiz_id,
		'menu_order'	=> $order,
	), $media_id, $answers_tpl, $title_hide, $answers_labels_hide );

	// Add some answers to new question?
	$answers = $data['answers'];

	if ( ! $id && ! empty( $answers ) ) {
		foreach ( $answers as $answer ) {
			snax_insert_answer( array(
				'post_title'	=> $answer['title'],
				'post_parent' 	=> $question_id,
				'menu_order'	=> $answer['order'],
			), $answer['correct']);
		}
	}

	if ( is_wp_error( $question_id ) ) {
		snax_ajax_response_error( 'Question creation failed!', array(
			'error_code'    => $question_id->get_error_code(),
			'error_message' => $question_id->get_error_message(),
		) );
		exit;
	}

	echo wp_json_encode( snax_get_question( $question_id ) );
	exit;
}

/**
 * Answer CRUD
 */
function snax_ajax_sync_answer() {
	$request_action = filter_input( INPUT_POST, 'request_action', FILTER_SANITIZE_STRING );

	// Delete answer.
	if ( 'DELETE' === $request_action ) {
		$id = (int) filter_input( INPUT_GET, 'answer_id', FILTER_SANITIZE_NUMBER_INT );

		// Delete question.
		$deleted = snax_delete_answer( array(
			'ID' => $id,
		) );

		if ( is_wp_error( $deleted ) ) {
			snax_ajax_response_error( 'Answer deletion failed!', array(
				'error_code'    => $deleted->get_error_code(),
				'error_message' => $deleted->get_error_message(),
			) );
			exit;
		}

		snax_ajax_response_success( 'Answer deleted.' );
		exit;
	}

	// Add/Edit answer.
	$request_body = file_get_contents('php://input');
	$data = json_decode( $request_body, true );

	$nonce = $data['security'];

	// Check ajax referer.
	if ( ! wp_verify_nonce( $nonce, 'quizzard-quiz' ) ) {
		die( '-1' );
	}

	$question_id = absint( $data['question_id'] );

	if ( ! $question_id ) {
		snax_ajax_response_error( 'Question id not set!' );
		exit;
	}

	$title = $data['title'];

	if ( ! $title ) {
		snax_ajax_response_error( 'Answer title not set!' );
		exit;
	}

	$id 		= (int) $data['id'];
	$order 		= (int) $data['order'];
	$correct 	= $data['correct'];
	$media 		= $data['media'];
	$media_id 	= (int) $media['id'];

	// Create new answer.
	$answer_id = snax_insert_answer( array(
		'ID'			=> $id,
		'post_title'	=> $title,
		'post_parent' 	=> $question_id,
		'menu_order'	=> $order,
	), $correct, $media_id );

	if ( is_wp_error( $question_id ) ) {
		snax_ajax_response_error( 'Answer creation failed!', array(
			'error_code'    => $question_id->get_error_code(),
			'error_message' => $question_id->get_error_message(),
		) );
		exit;
	}

	echo wp_json_encode( snax_get_answer( $answer_id ) );
	exit;
}

/**
 * Result CRUD
 */
function snax_ajax_sync_result() {
	$request_action = filter_input( INPUT_POST, 'request_action', FILTER_SANITIZE_STRING );

	// Delete result.
	if ( 'DELETE' === $request_action ) {
		$id = (int) filter_input( INPUT_GET, 'result_id', FILTER_SANITIZE_NUMBER_INT );

		// Delete result.
		$deleted = snax_delete_result( array(
			'ID' => $id,
		) );

		if ( is_wp_error( $deleted ) ) {
			snax_ajax_response_error( 'Result deletion failed!', array(
				'error_code'    => $deleted->get_error_code(),
				'error_message' => $deleted->get_error_message(),
			) );
			exit;
		}

		snax_ajax_response_success( 'Result deleted.' );
		exit;
	}

	// Add/Edit result.
	$request_body = file_get_contents('php://input');
	$data = json_decode( $request_body, true );

	$nonce = $data['security'];

	// Check ajax referer.
	if ( ! wp_verify_nonce( $nonce, 'quizzard-quiz' ) ) {
		die( '-1' );
	}

	$quiz_type = $data['quiz_type'];

	if ( ! $quiz_type ) {
		snax_ajax_response_error( 'Quiz type not set!' );
		exit;
	}

	if ( ! snax_is_valid_quiz_type( $quiz_type ) ) {
		snax_ajax_response_error( 'Invalid quiz type!' );
		exit;
	}

	$quiz_id = absint( $data['quiz_id'] );

	if ( ! $quiz_id ) {
		snax_ajax_response_error( 'Quiz id not set!' );
		exit;
	}

	$title = $data['title'];

	if ( ! $title ) {
		snax_ajax_response_error( 'Result title not set!' );
		exit;
	}

	$range 		= $data['range'];
	$range_low 	= (int) $range['low'];
	$range_high	= (int) $range['high'];

	$description = wp_kses_post( $data['description'] );

	$id 		= absint( $data['id'] );
	$order 		= absint( $data['order'] );
	$media 		= $data['media'];
	$media_id 	= (int) $media['id'];

	// Create new result.
	$result_id = snax_insert_result( array(
		'ID'			=> $id,
		'post_title'	=> $title,
		'post_content'	=> $description,
		'post_parent' 	=> $quiz_id,
		'menu_order'	=> $order,
	), $range_low, $range_high ,$media_id );

	if ( is_wp_error( $result_id ) ) {
		snax_ajax_response_error( 'Result creation failed!', array(
			'error_code'    => $result_id->get_error_code(),
			'error_message' => $result_id->get_error_message(),
		) );
		exit;
	}

	echo wp_json_encode( snax_get_result( $result_id ) );
	exit;
}
