<?php
/**
 * BuddyPress Snax Plugin
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_action( 'bp_include', 'snax_setup_buddypress', 10 );

/**
 * Setup BuddyPress
 */
function snax_setup_buddypress() {
	if ( ! function_exists( 'buddypress' ) ) {
		/**
		 * Create helper for BuddyPress 1.6 and earlier.
		 *
		 * @return bool
		 */
		function buddypress() {
			return isset( $GLOBALS['bp'] ) ? $GLOBALS['bp'] : false;
		}
	}

	// Bail if in maintenance mode.
	if ( ! buddypress() || buddypress()->maintenance_mode ) {
		return;
	}
	$bp_path = trailingslashit( dirname( __FILE__ ) );
	require( $bp_path . 'functions.php' );

	// Load template functions.
	require( $bp_path . 'template-functions.php' );

	// Load notifications.
	if ( bp_is_active( 'notifications' ) ) {
		require( $bp_path . 'notifications.php' );
	}

	// Load activities.
	if ( bp_is_active( 'activity' ) ) {
		require( $bp_path . 'activity.php' );
	}

	/* Activate our custom components */
	global $pagenow;
	$forced =  ( 'options-permalink.php' === $pagenow ) ? true : false;
	snax_bp_activate_components( $forced );

	/** COMPONENTS ********************************** */

	// Instantiate BuddyPress components.
	snax()->plugins->buddypress = new stdClass();

	// Posts.
	$posts_component_id = snax_posts_bp_component_id();

	if ( bp_is_active( $posts_component_id ) ) {
		require( $bp_path . 'components/posts.php' );

		$posts_component = new Snax_Posts_BP_Component();

		snax()->plugins->buddypress->$posts_component_id = $posts_component;

		// Register our custom componentns references into BP to enable BP notifications built-in system.
		// BP checkes active notifications components and only in this way we can inject our components into it.
		buddypress()->$posts_component_id = $posts_component;
	}

	// Items.
	$items_component_id = snax_items_bp_component_id();

	if ( bp_is_active( $items_component_id ) ) {
		require( $bp_path . 'components/items.php' );

		$items_component = new Snax_Items_BP_Component();

		snax()->plugins->buddypress->$items_component_id = $items_component;

		buddypress()->$items_component_id = $items_component;
	}

	// Votes.
	$votes_component_id = snax_votes_bp_component_id();

	if ( bp_is_active( $votes_component_id ) ) {
		require( $bp_path . 'components/votes.php' );

		$votes_component = new Snax_Votes_BP_Component();

		snax()->plugins->buddypress->$votes_component_id = $votes_component;

		buddypress()->$votes_component_id = $votes_component;
	}
}
