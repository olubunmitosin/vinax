<?php
/**
 * WooCommerce plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'woocommerce_prevent_admin_access', 'snax_woocommerce_prevent_admin_access', 99 );

function snax_woocommerce_prevent_admin_access( $prevent ) {
	$snax_action = filter_input( INPUT_POST, 'snax_media_upload_action', FILTER_SANITIZE_STRING );

	if ( ! empty( $snax_action ) ) {
		$prevent = false;
	}

	return $prevent;
}
