<?php
/**
 * AMP AJAX functions
 *
 * @license For the full license information, please view the Licensing folder
 * that was distributed with this source code.
 *
 * @package Snax_Plugin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'wp_ajax_snax_amp_vote_item',           'snax_amp_ajax_vote_item' );
add_action( 'wp_ajax_nopriv_snax_amp_vote_item',    'snax_amp_ajax_vote_item' );

/**
 * Vote item ajax handler
 */
function snax_amp_ajax_vote_item() {

	header( 'Content-type: application/json' );
	header( 'Access-Control-Allow-Credentials: true' );
	header( 'Access-Control-Allow-Origin: *.ampproject.org' );
	header( 'AMP-Access-Control-Allow-Source-Origin: ' . ( isset($_SERVER['HTTPS'] ) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] );
	header( 'Access-Control-Expose-Headers: AMP-Access-Control-Allow-Source-Origin' );

	check_ajax_referer( 'snax-vote-item', 'security' );

	// Sanitize item id.
	$item_id = (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $item_id ) {
		snax_ajax_response_error( 'Item id not set!' );
		exit;
	}

	$guest_voting_disabled = ! snax_guest_voting_is_enabled();

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( $guest_voting_disabled && 0 === $author_id ) {
		snax_ajax_response_error( 'Author id not set!' );
		exit;
	}

	// Sanitize type.
	$type = filter_input( INPUT_POST, 'snax_vote_type', FILTER_SANITIZE_STRING );

	if ( ! in_array( $type, array( 'upvote', 'downvote' ), true ) ) {
		snax_ajax_response_error( 'Vote type is not allowed!' );
		exit;
	}

	// Check creds.
	if ( $guest_voting_disabled && ! user_can( $author_id, 'snax_vote_items', $item_id ) ) {
		snax_ajax_response_error( sprintf( 'Author %d is not allowed to vote for this item.', $author_id ) );
		exit;
	}
	$class = $type;
	// Update current vote.
	if ( snax_user_voted( $item_id, $author_id ) ) {
		// User already upvoted and clicked upvote again, wants to remove vote.
		if ( snax_user_upvoted( $item_id, $author_id ) && 'upvote' === $type ) {
			$voted = snax_remove_vote( $item_id, $author_id );
			$class = '';

			// User already downvoted and clicked downvote again, wants to remove vote.
		} else if ( snax_user_downvoted( $item_id, $author_id ) && 'downvote' === $type ) {
			$voted = snax_remove_vote( $item_id, $author_id );
			$class = '';

			// User decided to vote opposite.
		} else {
			$voted = snax_toggle_vote( $item_id, $author_id );
		}

		// New vote.
	} else {
		$new_vote = array(
			'post_id'   => $item_id,
			'author_id' => $author_id,
		);

		if ( 'upvote' === $type ) {
			$voted = snax_upvote_item( $new_vote );
		} else {
			$voted = snax_downvote_item( $new_vote );
		}
	}

	if ( is_wp_error( $voted ) ) {
		snax_ajax_response_error( sprintf( 'Failed to vote for item with id %d', $item_id ), array(
			'error_code'    => esc_html( $voted->get_error_code() ),
			'error_message' => esc_html( $voted->get_error_message() ),
		) );
		exit;
	}

	ob_start();
	echo intval( snax_get_voting_score( $item_id ) );
	$html = ob_get_clean();

	snax_ajax_response_success( 'Vote added successfully.', array(
		'html' => $html,
		'class' => $class,
	) );
	exit;
}
