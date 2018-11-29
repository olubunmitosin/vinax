<?php
/**
 * BuddyPress Notifications
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'snax_post_approved',   'snax_bp_post_approved_notification' );
add_action( 'snax_post_rejected',   'snax_bp_post_rejected_notification' );
add_action( 'snax_item_added',      'snax_bp_item_added_notification',10, 2 );
add_action( 'snax_item_approved',   'snax_bp_item_approved_notification' );
add_action( 'snax_item_rejected',   'snax_bp_item_rejected_notification' );
add_action( 'snax_vote_added',      'snax_bp_vote_added_notification' );
add_action( 'before_delete_post',	'snax_bp_delete_notifications_for_removed_posts');

/**
 * Notify author when new item was added to his post
 *
 * @param int $item_id       Newly added item id.
 */
function snax_bp_item_added_notification( $item_id ) {
	if ( ! bp_is_active( 'notifications' ) ) {
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

	// Notify post author.
	bp_notifications_add_notification( array(
		'user_id'           => $post->post_author,      // Receiver id.
		'item_id'           => $item->ID,
		'secondary_item_id' => $post->ID,
		'component_name'    => snax_items_bp_component_id(),
		'component_action'  => 'snax_item_added',
	) );
}

/**
 * Notify author when his item was approved
 *
 * @param int $item       Approved item.
 */
function snax_bp_item_approved_notification( $item ) {
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! snax_is_item( $item ) ) {
		return;
	}

	$post = get_post( $item->post_parent );

	// Item can be not attached to any post but in that case we don't want to notify when such an item is added.
	if ( ! snax_is_format( 'list', $post ) ) {
		return;
	}

	// Notify post author.
	bp_notifications_add_notification( array(
		'user_id'           => $item->post_author,      // Receiver id.
		'item_id'           => $item->ID,
		'secondary_item_id' => $post->ID,
		'component_name'    => snax_items_bp_component_id(),
		'component_action'  => 'snax_item_approved',
	) );
}

/**
 * Notify author when his item was rejected
 *
 * @param int $item       Approved item.
 */
function snax_bp_item_rejected_notification( $item ) {
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! snax_is_item( $item ) ) {
		return;
	}

	$post = get_post( $item->post_parent );

	// Item can be not attached to any post but in that case we don't want to notify when such an item is added.
	if ( ! snax_is_format( 'list', $post ) ) {
		return;
	}

	// Notify post author.
	bp_notifications_add_notification( array(
		'user_id'           => $item->post_author,      // Receiver id.
		'item_id'           => $item->ID,
		'secondary_item_id' => $post->ID,
		'component_name'    => snax_items_bp_component_id(),
		'component_action'  => 'snax_item_rejected',
	) );
}

/**
 * Notify author when his post was approved
 *
 * @param WP_Post $post      Approved post.
 */
function snax_bp_post_approved_notification( $post ) {
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! snax_is_format( null, $post ) ) {
		return;
	}

	bp_notifications_add_notification( array(
		'user_id'           => $post->post_author,      // Receiver id.
		'item_id'           => $post->ID,
		'component_name'    => snax_posts_bp_component_id(),
		'component_action'  => 'snax_post_approved',
	) );
}

/**
 * Notify author when his post was rejected
 *
 * @param WP_Post $post      Approved post.
 */
function snax_bp_post_rejected_notification( $post ) {
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	if ( ! snax_is_format( null, $post ) ) {
		return;
	}

	bp_notifications_add_notification( array(
		'user_id'           => $post->post_author,      // Receiver id.
		'item_id'           => $post->ID,
		'component_name'    => snax_posts_bp_component_id(),
		'component_action'  => 'snax_post_rejected',
	) );
}

/**
 * Notify author when his post/item has new vote
 *
 * @param array $data       Newly added vote data.
 */
function snax_bp_vote_added_notification( $data ) {
	if ( ! bp_is_active( 'notifications' ) ) {
		return;
	}

	$item_id = $data['post_id'];
	$author_id = $data['author_id'];

	$item = get_post( $item_id );

	if ( snax_is_item( $item ) ) {
		$post = get_post( $item->post_parent );
	} else {
		$post = $item;
	}

	// Notify post author.
	bp_notifications_add_notification( array(
		'user_id'           => $post->post_author,      // Receiver id.
		'item_id'           => $item->ID,
		'secondary_item_id' => $author_id,
		'component_name'    => snax_votes_bp_component_id(),
		'component_action'  => 'snax_vote_added',
	) );
}

/**
 * Format notifications for the Snax components.
 *
 * @param string $action            The kind of notification being rendered.
 * @param int    $item_id           The primary item ID.
 * @param int    $secondary_item_id The secondary item ID.
 * @param int    $total_items       The total number of messaging-related notifications waiting for the user.
 * @param string $format            'string' for BuddyBar-compatible notifications; 'array'
 *                                  for WP Toolbar. Default: 'string'.
 * @return string
 */
function snax_bp_format_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {

	switch ( $action ) {
		case 'snax_item_added':

			$item_parent_id = $secondary_item_id;
			$amount = 'single';

			$item 		 = get_post( $item_id );

			if ( ! $item ) {
				break;
			}

			$item_url    = get_permalink( $item_id );
			$post_url    = get_permalink( $item_parent_id );
			$post_title  = get_the_title( $item_parent_id );

			$author_id	 = (int) $item->post_author;
			$author_url	 = bp_core_get_user_domain( $author_id );
			$author_link = bp_core_get_user_displayname( $author_id );

			if ( $author_url ) {
				$author_link = '<a href="'. esc_url( $author_url ) .'">'. esc_html( $author_link ) .'</a>';
			}

			return apply_filters(
				'snax_items_bp_' . $amount . '_' . $action . '_notification',
				sprintf( __( '%s added new <a href="%s">item</a> to the <a href="%s">%s</a> post.', 'snax' ), $author_link, $item_url, $post_url, $post_title ),
				$item_url,
				$post_url,
				$post_title
			);

			break;

		case 'snax_item_approved':

			$item_url   	= get_permalink( $item_id );
			$item_title 	= get_the_title( $item_id );
			$reviewer_id	= (int) get_post_meta( $item_id, 'snax_approved_by', true );
			$reviewer_url	= bp_core_get_user_domain( $reviewer_id );
			$reviewer_link 	= bp_core_get_user_displayname( $reviewer_id );

			if ( $reviewer_url ) {
				$reviewer_link = '<a href="'. esc_url( $reviewer_url ) .'">'. esc_html( $reviewer_link ) .'</a>';
			}

			return apply_filters(
				'snax_items_bp_single_' . $action . '_notification',
				sprintf( __( 'Your item <a href="%s">%s</a> was approved by %s.', 'snax' ), $item_url, $item_title, $reviewer_link ),
				$item_url,
				$item_title
			);

			break;

		case 'snax_item_rejected':

			$item_url   	= get_permalink( $item_id );
			$item_title 	= get_the_title( $item_id );
			$reviewer_id	= (int) get_post_meta( $item_id, 'snax_rejected_by', true );
			$reviewer_url	= bp_core_get_user_domain( $reviewer_id );
			$reviewer_link 	= bp_core_get_user_displayname( $reviewer_id );

			if ( $reviewer_url ) {
				$reviewer_link = '<a href="'. esc_url( $reviewer_url ) .'">'. esc_html( $reviewer_link ) .'</a>';
			}

			return apply_filters(
				'snax_items_bp_single_' . $action . '_notification',
				sprintf( __( 'Your item <a href="%s">%s</a> was rejected by %s.', 'snax' ), $item_url, $item_title, $reviewer_link ),
				$item_url,
				$item_title
			);

			break;

		case 'snax_post_approved':

			$post_url   	= get_permalink( $item_id );
			$post_title 	= get_the_title( $item_id );
			$reviewer_id	= (int) get_post_meta( $item_id, 'snax_approved_by', true );
			$reviewer_url	= bp_core_get_user_domain( $reviewer_id );
			$reviewer_link 	= bp_core_get_user_displayname( $reviewer_id );

			if ( $reviewer_url ) {
				$reviewer_link = '<a href="'. esc_url( $reviewer_url ) .'">'. esc_html( $reviewer_link ) .'</a>';
			}

			return apply_filters(
				'snax_posts_bp_single_' . $action . '_notification',
				sprintf( __( 'Your post <a href="%s">%s</a> was approved by %s.', 'snax' ), $post_url, $post_title, $reviewer_link ),
				$post_url,
				$post_title
			);

			break;

		case 'snax_post_rejected':

			$post_url   	= get_permalink( $item_id );
			$post_title 	= get_the_title( $item_id );
			$reviewer_id	= (int) get_post_meta( $item_id, 'snax_rejected_by', true );
			$reviewer_url	= bp_core_get_user_domain( $reviewer_id );
			$reviewer_link 	= bp_core_get_user_displayname( $reviewer_id );

			if ( $reviewer_url ) {
				$reviewer_link = '<a href="'. esc_url( $reviewer_url ) .'">'. esc_html( $reviewer_link ) .'</a>';
			}

			return apply_filters(
				'snax_posts_bp_single_' . $action . '_notification',
				sprintf( __( 'Your post <a href="%s">%s</a> was rejected by %s.', 'snax' ), $post_url, $post_title, $reviewer_link ),
				$post_url,
				$post_title
			);

			break;

		case 'snax_vote_added':

			$item_url   = get_permalink( $item_id );
			$item_title = get_the_title( $item_id );
			$item_link  = '<a href="'. esc_url( $item_url ) .'">'. esc_html( $item_title ) .'</a>';

			$author_id  = $secondary_item_id;
			$author_url	= bp_core_get_user_domain( $author_id );
			$author_link 	= bp_core_get_user_displayname( $author_id );

			if ( $author_url ) {
				$author_link = '<a href="'. esc_url( $author_url ) .'">'. esc_html( $author_link ) .'</a>';
			}

			$vote = snax_get_vote_by_user( $author_id, $item_id );

			$type = esc_html__( 'voted', 'snax' );

			if ( false !== $vote ) {
				$type = snax_get_upvote_value() === (int) $vote->vote ? esc_html__( 'upvoted', 'snax' ) : esc_html__( 'downvoted', 'snax' );
			}

			return apply_filters(
				'snax_votes_bp_single_' . $action . '_notification',
				sprintf( __( '%s %s for your post %s.', 'snax' ), $author_link, $type, $item_link ),
				$item_url,
				$item_title
			);

			break;

		default:

			$custom_action_notification = apply_filters( 'snax_bp_' . $action . '_notification', null, $item_id, $secondary_item_id, $total_items, $format );

			if ( ! is_null( $custom_action_notification ) ) {
				return $custom_action_notification;
			}

			break;
	}

	/**
	 * Fires right before returning the formatted notifications.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action            The type of notification being rendered.
	 * @param int    $item_id           The primary item ID.
	 * @param int    $secondary_item_id The secondary item ID.
	 * @param int    $total_items       Total amount of items to format.
	 */
	do_action( 'snax_bp_format_notifications', $action, $item_id, $secondary_item_id, $total_items );

	return false;
}

/**
 * Delete notifications for post/item when it is deleted permanently
 *
 * @param int $post_id  ID of the post.
 * @return void
 */
function snax_bp_delete_notifications_for_removed_posts( $post_id ) {
	$post = get_post( $post_id );
	$user_id = $post->post_author;

	$component_name = snax_items_bp_component_id();
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_item_added' );
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_item_approved' );
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_item_rejected' );
	$component_name = snax_posts_bp_component_id();
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_post_approved' );
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_post_rejected' );
	$component_name = snax_votes_bp_component_id();
	bp_notifications_delete_notifications_by_item_id( $user_id, $post_id, $component_name, 'snax_vote_added' );
}
