<?php
/**
 * Snax Install Function
 *
 * @package snax
 * @subpackage InstallFunctions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Create database tables for all modules
 */
function snax_install_schemas() {
	snax_install_votes_schema();
	snax_install_polls_schema();
}

/**
 * Install table 'snax_votes'
 */
function snax_install_votes_schema() {
	global $wpdb;

	$current_ver    = '1.0';
	$installed_ver  = get_option( 'snax_votes_table_version' );

	// Create table only if needed.
	if ( $installed_ver !== $current_ver ) {
		$table_name      = $wpdb->prefix . snax_get_votes_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		vote_id bigint(20) unsigned NOT NULL auto_increment,
		post_id bigint(20) NOT NULL ,
		vote int(2) NOT NULL,
		author_id bigint(20) NOT NULL default '0',
  		author_ip varchar(100) NOT NULL default '',
		author_host varchar(200) NOT NULL,
		date datetime NOT NULL default '0000-00-00 00:00:00',
  		date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (vote_id),
		KEY post_id (post_id)
	) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'snax_votes_table_version', $current_ver );
	}
}

/**
 * Install table 'snax_polls'
 */
function snax_install_polls_schema() {
	global $wpdb;

	$current_ver    = '1.0';
	$installed_ver  = get_option( 'snax_polls_table_version' );

	// Create table only if needed.
	if ( $installed_ver !== $current_ver ) {
		$table_name      = $wpdb->prefix . snax_get_polls_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		ID bigint(20) unsigned NOT NULL auto_increment,
		poll_id bigint(20) NOT NULL,
		question_id bigint(20) NOT NULL,
		answer_id bigint(20) NOT NULL,
		author_id bigint(20) NOT NULL default '0',
		date datetime NOT NULL default '0000-00-00 00:00:00',
  		date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (ID),
		KEY poll_id (poll_id)
	) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'snax_polls_table_version', $current_ver );
		add_action( 'shutdown', 'flush_rewrite_rules' );
	}
}

/**
 * Load default plugin options into database, if not already exist
 */
function snax_load_default_options() {
	$defaults = snax_get_default_options();

	foreach ( $defaults as $id => $value ) {
		$option = get_option( $id );

		// If not set.
		if ( false === $option ) {
			add_option( $id, $value );
		}

		// Activate text format if option already exists.
		if ( 'snax_active_formats' === $id && $option ) {
			if ( ! in_array( 'audio', $option ) ) {
				$option[] = 'audio';
				$option[] = 'video';
				$option[] = 'text';
				update_option( $id, $option );
			}
		}

		// Activate new forms (audio, video, text) if option already exists.
		if ( 'snax_active_item_forms' === $id && $option ) {
			if ( ! in_array( 'audio', $option ) ) {
				$option[] = 'audio';
				$option[] = 'video';
				$option[] = 'text';
				update_option( $id, $option );
			}
		}
	}
}

/**
 * If there is no Frontend Submission page user can start playing with the plugin, we need to create one
 */
function snax_create_and_assign_frontend_page() {
	$page_id = get_option( 'snax_frontend_submission_page_id' );

	// Skip, if already set.
	if ( false !== $page_id ) {
		return;
	}

	$page_title = apply_filters( 'snax_frontend_submission_page_default_title', esc_html__( 'Frontend Submission', 'snax' ) );
	$page       = get_page_by_title( $page_title );

	// Create new page if not exists.
	if ( null === $page ) {
		$page_id = wp_insert_post( array(
			'post_title'    => wp_strip_all_tags( $page_title ),
			'post_type'     => 'page',
			'post_status'   => 'publish',
		) );
	} else {
		$page_id = $page->ID;
	}

	// Assign.
	if ( $page_id > 0 ) {
		update_option( 'snax_frontend_submission_page_id', $page_id );
	}
}
