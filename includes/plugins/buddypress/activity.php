<?php
/**
 * BuddyPress Activity
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'snax_item_added',                  'snax_bp_item_added_activity', 10, 2 );
add_action( 'snax_vote_added',                  'snax_bp_vote_added_activity' );

add_filter( 'bp_get_activity_action_pre_meta',                  'snax_format_activity_action', 10, 2 );
add_filter( 'bp_blogs_format_activity_action_new_blog_post',    'snax_bp_blogs_format_activity_action_new_blog_post', 10, 2 );

/**
 * Add activity for newly added item
 *
 * @param int    $item_id       Newly added item id.
 * @param string $type          Type of aded item.
 */
function snax_bp_item_added_activity( $item_id, $type ) {
	if ( ! function_exists( 'bp_activity_add' ) ) {
		return;
	}

	if ( apply_filters( 'snax_bp_item_added_activity_disabled', false, $item_id, $type ) ) {
		return;
	}

	$item = get_post( $item_id );

	if ( ! snax_is_item( $item ) ) {
		return;
	}

	$post = get_post( $item->post_parent );

	// Item can be not attached to any post but in that case we don't want to notify when such an item is added.
	if ( ! snax_is_format( 'list', $post ) ) {
		return;
	}

	// Record own activity.
	bp_activity_add( array(
		'action'        => sprintf(
			snax_bp_item_added_activity_tpl(),
			bp_core_get_user_domain( $item->post_author ),
			bp_core_get_user_displayname( $item->post_author ),
			$type,
			get_permalink( $item ),
			get_the_title( $item ),
			get_permalink( $post ),
			get_the_title( $post )
		),
		'component'         => 'snax_item',
		'type'              => $type,
		'primary_link'      => get_permalink( $item ),
		'user_id'           => $item->post_author,
		'item_id'           => $item->ID,
		'secondary_item_id' => $post->ID,
	) );
}

/**
 * Return template for newley added item activity
 *
 * @return string
 */
function snax_bp_item_added_activity_tpl() {
	$tpl = _x( '<a href="%s">%s</a> added new %s <a href="%s">%s</a> to <a href="%s">%s</a>', 'BuddyPress activity: new item added', 'snax' );

	return apply_filters( 'snax_bp_item_added_activity_tpl', $tpl );
}

/**
 * Add activity for newly voted item
 *
 * @param array $data 	Vote data.
 */
function snax_bp_vote_added_activity( $data ) {
	if ( ! function_exists( 'bp_activity_add' ) ) {
		return;
	}

	if ( apply_filters( 'snax_bp_vote_added_activity_disabled', false, $data ) ) {
		return;
	}

	$item_id = $data['post_id'];

	$item = get_post( $item_id );
	$title = get_the_title( $item );

	// Record own activity.
	bp_activity_add( array(
		'action'        => sprintf(
			snax_bp_vote_added_activity_tpl(),
			bp_core_get_user_domain( $data['author_id'] ),
			bp_core_get_user_displayname( $data['author_id'] ),
			snax_get_upvote_value() === $data['vote'] ? __( 'upvoted', 'snax' ) : __( 'downvoted', 'snax' ),
			get_permalink( $item ),
			! empty( $title ) ? $title : __( 'post', 'snax' )
		),
		'component'         => 'snax_vote',
		'type'              => snax_get_upvote_value() === $data['vote'] ? 'upvote' : 'downvote',
		'primary_link'      => get_permalink( $item ),
		'user_id'           => $data['author_id'],
		'item_id'           => $item->ID,
	) );
}

/**
 * Return template for newley voted item activity
 *
 * @return string
 */
function snax_bp_vote_added_activity_tpl() {
	$tpl = _x( '<a href="%s">%s</a> %s for <a href="%s">%s</a>', 'BuddyPress activity: new vote added', 'snax' );

	return apply_filters( 'snax_bp_vote_added_activity_tpl', $tpl );
}

/**
 * Format activities
 *
 * @param string   $action         Full action string.
 * @param stdClass $activity       Activity object.
 *
 * @return string
 */
function snax_format_activity_action( $action, $activity ) {
	if ( 'snax_item' === $activity->component ) {
		$item_id = $activity->item_id;
		$item = get_post( $item_id );
		$post = get_post( $item->post_parent );

		$action = sprintf(
			snax_bp_item_added_activity_tpl(),
			bp_core_get_user_domain( $activity->user_id ),
			bp_core_get_user_displayname( $activity->user_id ),
			$activity->type,
			get_permalink( $item_id ),
			get_the_title( $item->ID ),
			get_permalink( $post ),
			get_the_title( $post->ID )
		);
	}

	if ( 'snax_vote' === $activity->component ) {
		$item_id = $activity->item_id;
		$item = get_post( $item_id );
		$title = get_the_title( $item );

		$action = sprintf(
			snax_bp_vote_added_activity_tpl(),
			bp_core_get_user_domain( $activity->user_id ),
			bp_core_get_user_displayname( $activity->user_id ),
			'upvote' === $activity->type ? __( 'upvoted', 'snax' ) : __( 'downvoted', 'snax' ),
			get_permalink( $item ),
			! empty( $title ) ? $title : __( 'post', 'snax' )
		);
	}

	return apply_filters( 'snax_format_activity_action', $action, $activity );
}

/**
 * Change title placeholder for activity
 *
 * @param string $action            Action link.
 * @param object $activity          Activity object.
 *
 * @return mixed
 */
function snax_bp_blogs_format_activity_action_new_blog_post( $action, $activity ) {
	if ( 'new_blog_post' === $activity->type ) {
		$post_id = $activity->secondary_item_id;

		if ( snax_is_format( 'list', $post_id ) || snax_is_format( 'gallery', $post_id ) ) {
			$action = snax_replace_title_placeholder( $action, $post_id );
		}
	}

	return $action;
}
