<?php
/**
 * Snax Votes Capabilites
 *
 * @package snax
 * @subpackage Capabilities
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Map votes caps to WordPress's existing capabilities
 *
 * @param array  $caps      Capabilities for meta capability.
 * @param string $cap       Capability name.
 * @param int    $user_id   User id.
 * @param mixed  $args      Arguments.
 *
 * @return array
 */
function snax_map_votes_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	/* CAUTION: By default the "subscriber" role has only the "read" capability. */

	// What capability is being checked?
	switch ( $cap ) {

		case 'snax_vote_posts':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array( 'do_not_allow' );

			}

			break;

		case 'snax_vote_items':

			// Block access if user if not active.
			if ( snax_is_user_inactive( $user_id ) ) {
				$caps = array( 'do_not_allow' );

			}

			break;
	}

	return apply_filters( 'snax_map_votes_caps', $caps, $cap, $user_id, $args );
}
