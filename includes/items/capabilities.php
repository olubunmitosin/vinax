<?php
/**
 * Snax Items Capabilites
 *
 * @package snax
 * @subpackage Capabilities
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Map items caps to WordPress's existing capabilities
 *
 * @param array  $caps      Capabilities for meta capability.
 * @param string $cap       Capability name.
 * @param int    $user_id   User id.
 * @param mixed  $args      Arguments.
 *
 * @return array
 */
function snax_map_items_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	global $post;
	if ( isset( $post ) ) {
		$is_author = (string) get_current_user_id() === $post->post_author;
	}

	/* CAUTION: By default the "subscriber" role has only the "read" capability. */

	// What capability is being checked?
	switch ( $cap ) {

		/** Publish */

		case 'snax_publish_items':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array();

				$caps[] = 'do_not_allow';

			} elseif ( snax_skip_verification() ) {
				$caps = array();

				$caps[] = 'read';

				// Check first if user can "publish_posts". If so, use it.
				// The "snax_publish_items" is more restrictive. Should be checked only if user has no "publish_posts" capability.
			} elseif ( user_can( $user_id, 'publish_posts' ) ) {
				$caps = array();

				$caps[] = 'publish_posts';
			} elseif ( $is_author && snax_get_item_approved_status() === get_post_status( ) ) {
				$caps = array();
				$caps[] = 'snax_add_items' ;
			}

			// If user is not blocked and has no "publish_posts" capability, do nothig.
			// Let WP process capabilities checkc in default way.
			// At this point, only if user has explicilty added "snax_publish_items" capability, he will pass.
			break;

		/** Add new */

		case 'snax_add_items':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array();

				$caps[] = 'do_not_allow';

				// Check first if user can "edit_posts". If so, use it.
				// The "snax_add_items" is more restrictive. Should be checked only if user has no "edit_posts" capability.
			} elseif ( user_can( $user_id, 'edit_posts' ) ) {
				$caps = array();

				$caps[] = 'edit_posts';
			}

			break;

		/** Delete */

		case 'snax_delete_items' :

			// Set an empty array for the caps.
			$caps = array();

			// Block access if user is not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps[] = 'do_not_allow';

				// Grant access for users that can delete not their own posts (e.g. admins).
			} elseif ( user_can( $user_id, 'delete_others_posts' ) ) {
				$caps[] = 'delete_others_posts';

			} else {
				$post = get_post( $args[0] );

				if ( ! empty( $post ) ) {
					$post_type = get_post_type_object( $post->post_type );

					// Grant access for authors.
					if ( (int) $user_id === (int) $post->post_author ) {

						// Check first if user has higher access level.
						if ( user_can( $user_id, 'delete_posts' ) ) {
							$caps[] = $post_type->cap->delete_posts;
						} else {
							$caps[] = 'snax_delete_items';
						}

						// Fallback to post type "delete_others_posts" capability. Use any roles mananger plugin to assign.
					} else {
						$caps[] = $post_type->cap->delete_others_posts;
					}
				}
			}

			break;

		case 'snax_edit_others_items':

			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array( 'do_not_allow' );

			} elseif ( user_can( $user_id, 'edit_others_posts' ) ) {
				$caps = array( 'edit_others_posts' );

			}

			break;
	}

	return apply_filters( 'snax_map_items_caps', $caps, $cap, $user_id, $args );
}
