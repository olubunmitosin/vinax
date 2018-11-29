<?php
/**
 * BuddyPress Template Functions
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/** Screens ********************************************************/


/**
 * Hook "Approved Posts" template into plugins template
 */
function snax_member_screen_approved_posts() {
	add_action( 'bp_template_content', 'snax_member_approved_posts_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_approved_posts', 'members/single/plugins' ) );
}

/**
 * Hook "Draft Posts" template into plugins template
 */
function snax_member_screen_draft_posts() {
	add_action( 'bp_template_content', 'snax_member_draft_posts_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_draft_posts', 'members/single/plugins' ) );
}

/**
 * Hook "Pending Posts" template into plugins template
 */
function snax_member_screen_pending_posts() {
	add_action( 'bp_template_content', 'snax_member_pending_posts_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_pending_posts', 'members/single/plugins' ) );
}

/**
 * Hook "Approved Items" template into plugins template
 */
function snax_member_screen_approved_items() {
	add_action( 'bp_template_content', 'snax_member_approved_items_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_approved_items', 'members/single/plugins' ) );
}

/**
 * Hook "Pending Items" template into plugins template
 */
function snax_member_screen_pending_items() {
	add_action( 'bp_template_content', 'snax_member_pending_items_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_pending_items', 'members/single/plugins' ) );
}

/**
 * Hook "Upvotes" template into plugins template
 */
function snax_member_screen_upvotes() {
	add_action( 'bp_template_content', 'snax_member_upvotes_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_upvotes', 'members/single/plugins' ) );
}

/**
 * Hook "Downvotes" template into plugins template
 */
function snax_member_screen_downvotes() {
	add_action( 'bp_template_content', 'snax_member_downvotes_content' );
	bp_core_load_template( apply_filters( 'snax_member_screen_downvotes', 'members/single/plugins' ) );
}


/** Templates ******************************************************/


/**
 * Approved posts template part
 */
function snax_member_approved_posts_content() {
	?>

	<div id="snax-posts snax-approved-posts">

		<?php snax_get_template_part( 'buddypress/posts/section-approved' ); ?>

	</div>

	<?php
}

/**
 * Pending posts template part
 */
function snax_member_pending_posts_content() {
	?>

	<div id="snax-posts snax-pending-posts">

		<?php snax_get_template_part( 'buddypress/posts/section-pending' ); ?>

	</div>

	<?php
}

/**
 * Draft posts template part
 */
function snax_member_draft_posts_content() {
	?>

	<div id="snax-posts snax-draft-posts">

		<?php snax_get_template_part( 'buddypress/posts/section-draft' ); ?>

	</div>

	<?php
}

/**
 * Approved items template part
 */
function snax_member_approved_items_content() {
	?>

	<div id="snax-items snax-approved-items">

		<?php snax_get_template_part( 'buddypress/items/section-approved' ); ?>

	</div>

	<?php
}

/**
 * Pending items template part
 */
function snax_member_pending_items_content() {
	?>

	<div id="snax-items snax-pending-items">

		<?php snax_get_template_part( 'buddypress/items/section-pending' ); ?>

	</div>

	<?php
}

/**
 * Upvotes template part
 */
function snax_member_upvotes_content() {
	?>

	<div id="snax-upvotes">

		<?php snax_get_template_part( 'buddypress/votes/section-upvotes' ); ?>

	</div>

	<?php
}

/**
 * Downvotes template part
 */
function snax_member_downvotes_content() {
	?>

	<div id="snax-downvotes">

		<?php snax_get_template_part( 'buddypress/votes/section-downvotes' ); ?>

	</div>

	<?php
}
