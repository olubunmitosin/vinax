<?php
/**
 * Snax Posts Capabilites
 *
 * @package snax
 * @subpackage Capabilities
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Map posts caps to WordPress's existing capabilities
 *
 * @param array  $caps      Capabilities for meta capability.
 * @param string $cap       Capability name.
 * @param int    $user_id   User id.
 * @param mixed  $args      Arguments.
 *
 * @return array
 */
function snax_map_posts_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	/* CAUTION: By default the "subscriber" role has only the "read" capability. */

	// What capability is being checked?
	switch ( $cap ) {

		/** Publish */

		case 'snax_publish_posts':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array();

				$caps[] = 'do_not_allow';

			} elseif ( snax_skip_verification() ) {
				$caps = array();

				$caps[] = 'read';

				// Check first if user can "publish_posts". If so, use it.
				// The "snax_publish_posts" is more restrictive. Should be checked only if user has no "publish_posts" capability.
			} elseif ( user_can( $user_id, 'publish_posts' ) ) {
				$caps = array();

				$caps[] = 'publish_posts';
			}

			// If user is not blocked and has no "publish_posts" capability, do nothing.
			// Let WP process capabilities check in default way.
			// At this point, only if user has explicitly added "snax_publish_posts" capability, the test will pass.
			break;

		/** Edit */

		case 'snax_edit_posts':
			$post = ! empty( $args[0] ) ? get_post( $args[0] ) : null;

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array();

				$caps[] = 'do_not_allow';

			} elseif ( $post && snax_is_post_format_editable( $post ) ) {
				// Owner can edit posts that are not published yet.
				if ( $user_id === (int) $post->post_author ) {
					$caps = array();

					$caps[] = 'read';
				}

				// Check first if user can "edit_posts". If so, use it.
				// The "snax_edit_posts" is more restrictive. Should be checked only if user has no "edit_posts" capability.
				if ( user_can( $user_id, 'edit_posts' ) ) {
					$caps = array();

					$caps[] = 'edit_posts';
				}
			}

			// If user is not blocked and has no "edit_posts" capability, do nothing.
			// Let WP process capabilities check in default way.
			// At this point, only if user has explicitly added "snax_edit_posts" capability, the test will pass.
			break;

		/** Add new */

		case 'snax_add_posts':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array();

				$caps[] = 'do_not_allow';

				// Check first if user can "edit_posts". If so, use it.
				// The "snax_add_posts" is more restrictive. Should be checked only if user has no "edit_posts" capability.
			} elseif ( user_can( $user_id, 'edit_posts' ) ) {
				$caps = array();

				$caps[] = 'edit_posts';
			}

			break;
	}

	return apply_filters( 'snax_map_item_meta_caps', $caps, $cap, $user_id, $args );
}
