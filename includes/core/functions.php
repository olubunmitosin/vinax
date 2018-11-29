<?php
/**
 * Snax Core Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Get the plugin version
 *
 * @return string
 */
function snax_get_version() {
	return snax()->version;
}

/**
 * Get the plugin database version
 *
 * @return string
 */
function snax_get_db_version() {
	return snax()->db_version;
}

/**
 * Get the table name of the votes table
 *
 * @return string
 */
function snax_get_votes_table_name() {
	return snax()->votes_table_name;
}

/**
 * Get the table name of the polls table
 *
 * @return string
 */
function snax_get_polls_table_name() {
	return snax()->polls_table_name;
}

/**
 * Return the plugin basename
 *
 * @return string
 */
function snax_get_plugin_basename() {
	return snax()->basename;
}

/**
 * Return the plugin directory base path
 *
 * @return string
 */
function snax_get_plugin_dir() {
	return snax()->plugin_dir;
}

/**
 * Return the plugin assets (css, js, images) base url
 *
 * @return string
 */
function snax_get_assets_url() {
	return snax()->assets_url;
}

/**
 * Return the plugin includes base path
 *
 * @return string
 */
function snax_get_includes_dir() {
	return snax()->includes_dir;
}

/**
 * Return the plugin includes base url
 *
 * @return string
 */
function snax_get_includes_url() {
	return snax()->includes_url;
}

/**
 * Check whether the plugin is active and plugin can rely on it
 *
 * @param string $plugin Base plugin path.
 *
 * @return bool
 */
function snax_can_use_plugin( $plugin ) {
	// Detect plugin. For use on Front End only.
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	return is_plugin_active( $plugin );
}

/**
 * Return the correct admin URL based on WordPress configuration.
 *
 * @param string $path Optional. The sub-path under /wp-admin to be appended to the admin URL.
 *
 * @param string $scheme The scheme to use. Default is 'admin', which
 *                       obeys {@link force_ssl_admin()} and {@link is_ssl()}. 'http'
 *                       or 'https' can be passed to force those schemes.
 *
 * @return string        Admin url link with optional path appended.
 */
function snax_admin_url( $path = '', $scheme = 'admin' ) {
	// Links belong in network admin.
	if ( is_network_admin() ) {
		$url = network_admin_url( $path, $scheme );

		// Links belong in site admin.
	} else {
		$url = admin_url( $path, $scheme );
	}

	return $url;
}

/**
 * Hide admin bar for all Snax Authors
 */
function snax_hide_admin_bar() {
	// If user is admin or super admin, don't change visibility.
	if ( current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	// Change admin bar visibility only for Snax authors.
	// Otherwise, leave current admin bar state untouched.
	if ( current_user_can( 'snax_author' ) ) {
		$show = ( false === snax_disable_admin_bar() );

		show_admin_bar( $show );
	}
}

/**
 * Block dashboard access for all Snax Authors
 */
function snax_block_dashboard_access() {
	$is_doing_ajax 			= defined( 'DOING_AJAX' ) && DOING_AJAX;
	$is_snax_doing_ajax		= (bool) filter_input( INPUT_POST, 'snax_media_upload_action', FILTER_SANITIZE_STRING );
	$is_admin_page_request 	= is_admin();

	// If not admin trying to access WP Dashboard.
	if (
		is_user_logged_in() &&						// Check only if logged in user
		$is_admin_page_request &&					// is trying to access the backend side.
		! current_user_can( 'administrator' ) &&	// Skip checking if admin try to access.
		! $is_doing_ajax &&							// Skip checking if WP doing AJAX request.
		! $is_snax_doing_ajax						// Skip checking if Snax doing AJAX request.
	) {
		if ( snax_disable_dashboard_access() ) {
			wp_redirect( home_url() );
			exit;
		}
	}
}

/**
 * Set activation flag
 */
function snax_welcome_redirect() {
	$set_transient = true;
	$request_uri = filter_input( INPUT_SERVER, 'REQUEST_URI' );

	// Triggered via TGMPA?
	if ( false !== strpos( $request_uri, 'tgmpa-nonce' ) ) {
		$set_transient = false;
	}

	if ( $set_transient ) {
		set_transient( '_snax_do_activation_redirect', true, 30 );
	}
}

/**
 * Register shortcodes
 */
function snax_register_shortcodes() {
	add_shortcode( 'snax_content', 'snax_content_shortcode' );
}

/**
 * Disable Snax activation in the Network Admin Plugins list table
 *
 * @param array  $actions     An array of plugin action links.
 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
 * @param array  $plugin_data An array of plugin data.
 * @param string $context     The plugin context. Defaults are 'All', 'Active',
 *                            'Inactive', 'Recently Activated', 'Upgrade',
 *                            'Must-Use', 'Drop-ins', 'Search'.
 *
 * @return array
 */
function snax_network_admin_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
	if ( $plugin_file === snax()->basename ) {
		$actions['activate'] = '<p>'. __( 'Network activation disabled. Activate this plugin in a single site context	.', 'snax' ) . '</p>';
	}

	return $actions;
}

/**
 * Return full variable name ready to use in url
 *
 * @param string $name			Base name of variable.
 *
 * @return string	Prefix for url variables
 */
function snax_get_url_var( $name ) {
	$prefix = snax_get_url_var_prefix();

	// Suffix with _.
	if ( ! empty( $prefix ) ) {
		$prefix = rtrim( $prefix, '_' ) . '_';
	}

	return $prefix . $name;
}
