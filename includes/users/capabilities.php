<?php
/**
 * Snax Users Capabilites
 *
 * @package snax
 * @subpackage Capabilities
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Maps users caps to WordPress's existing capabilities
 *
 * @param array  $caps      Capabilities for meta capability.
 * @param string $cap       Capability name.
 * @param int    $user_id   User id.
 * @param mixed  $args      Arguments.
 *
 * @return array
 */
function snax_map_users_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	/* CAUTION: By default the "subscriber" role has only the "read" capability. */

	// What capability is being checked?
	switch ( $cap ) {

		// We use media_upload_form() which internally checks if user has the "upload_files" capability.
		// There is no way to hook there to use our "snax_upload_files" capability.
		// On the other hand, we can't grant users the "upload_files" capability. It's too high access level.
		// All we can to do is to hook into post upload params, check if it's our plugin call and if so,
		// we can allow access to users with the "snax_upload_files" capability.
		// The "snax_upload_files" is more restricitve than the "upload_files", so we can cast it up.
		case 'upload_files' :

			if ( user_can( $user_id, 'snax_upload_files' ) && snax_is_media_upload_action() ) {
				$caps = array( 'snax_upload_files' );

			}

			// Otherwise, we should do nothing and let WP check default "upload_files" capability.
			break;

		case 'snax_upload_files':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array( 'do_not_allow' );
			}

			// Block if upload limit reached while uploading new media to new post.
			if ( snax_is_media_upload_action( 'new_post_upload' ) ) {
				$media_uploaded_to_format = snax_get_media_upload_format();
				$uploaded_items = array();

				switch ( $media_uploaded_to_format ) {
					case 'list':
					case 'gallery':
						$uploaded_items = snax_get_user_orphan_items( $media_uploaded_to_format, $user_id );
						break;

					case 'text':
						$uploaded_items = snax_get_user_uploaded_media( $media_uploaded_to_format, $user_id );
						break;
				}

				// @todo - each format should have own limit
				$items_limit = snax_get_new_post_items_limit();

				if ( -1 !== $items_limit && count( $uploaded_items ) >= $items_limit ) {
					$caps = array( 'do_not_allow' );
				}
			}

			break;
	}

	return apply_filters( 'snax_map_users_caps', $caps, $cap, $user_id, $args );
}
