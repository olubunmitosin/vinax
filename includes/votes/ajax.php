<?php
/**
 * Snax Vote Ajax Functions
 *
 * @package snax
 * @subpackage Ajax
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Vote item ajax handler
 */
function snax_ajax_vote_item() {
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

	// Update current vote.
	if ( snax_user_voted( $item_id, $author_id ) ) {
		// User already upvoted and clicked upvote again, wants to remove vote.
		if ( snax_user_upvoted( $item_id, $author_id ) && 'upvote' === $type ) {
			$voted = snax_remove_vote( $item_id, $author_id );

			// User already downvoted and clicked downvote again, wants to remove vote.
		} else if ( snax_user_downvoted( $item_id, $author_id ) && 'downvote' === $type ) {
			$voted = snax_remove_vote( $item_id, $author_id );

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
	snax_render_voting_box( $item_id, $author_id );
	$html = ob_get_clean();

	if ( isset( $new_vote ) ) {
		do_action( 'snax_vote_saved',$item_id, $author_id );
	}

	snax_ajax_response_success( 'Vote added successfully.', array(
		'html' => $html,
	) );
	exit;
}
