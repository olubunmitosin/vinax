<?php
/**
 * Snax User Roles
 *
 * @package snax
 * @subpackage Roles
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Register Snax Author role
 */
function snax_setup_user_roles() {
	$role = add_role( 'snax_author', esc_html__( 'Snax Author', 'snax' ) );

	// Skip if role already exists.
	if ( null === $role ) {
		return;
	}

	snax_add_capabilities_to_role( $role );

	// Set default role.
	$current_default_role = get_option( 'default_role' );

	if ( 'subscriber' === $current_default_role ) {
		update_option( 'default_role', 'snax_author' );
	}

	// Grant access for administrator.
	$admin_role = get_role( 'administrator' );

	snax_add_capabilities_to_role( $admin_role );
}

/**
 * Assign all Snax capabilities to the $role
 *
 * @param WP_Role $role         User role.
 */
function snax_add_capabilities_to_role( $role ) {
	if ( ! $role ) {
		return;
	}

	$role->add_cap( 'read' );   // Allow Snax Author access admin dashboard.
	$role->add_cap( 'snax_upload_files' );
	$role->add_cap( 'snax_add_posts' );
	$role->add_cap( 'snax_add_items' );
	$role->add_cap( 'snax_delete_items' );
	$role->add_cap( 'snax_vote_posts' );
	$role->add_cap( 'snax_vote_items' );
}

/**
 * Remove all Snax user roles
 */
function snax_remove_user_roles() {
	remove_role( 'snax_author' );
}

/**
 * Handle user actions
 */
function snax_handle_user_actions() {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	$action  = filter_input( INPUT_GET, 'snax_action', FILTER_SANITIZE_STRING );

	if ( ! empty( $action ) ) {
		switch ( $action ) {
			case 'reset_user_roles':
				snax_remove_user_roles();
				snax_setup_user_roles();
				break;
		}
	}
}
