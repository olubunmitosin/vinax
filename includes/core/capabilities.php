<?php
/**
 * Snax Capabilities
 *
 * @package snax
 * @subpackage Capabilities
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Maps post/item caps to WordPress's existing capabilities
 *
 * @param array  $caps Capabilities for meta capability.
 * @param string $cap Capability name.
 * @param int    $user_id User id.
 * @param mixed  $args Arguments.
 *
 * @return array
 */
function snax_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	return apply_filters( 'snax_map_meta_caps', $caps, $cap, $user_id, $args );
}
