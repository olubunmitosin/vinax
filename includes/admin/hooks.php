<?php
/**
 * Snax Admin Actions and Filters
 *
 * This file contains the actions and the filters that are used through out the plugin.
 * They are consolidated here to help developers searching for them.
 *
 * @package snax
 * @subpackage Core
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/** Actions ******************************************************* */

// Initialize admin area.
add_action( 'admin_init',   'snax_do_welcome_redirect' );

// Metaboxes.
add_action( 'load-nav-menus.php',   'snax_add_menu_endpoints_metabox' );
add_action( 'add_meta_boxes',       'snax_add_list_post_metabox', 10 ,2 );
add_action( 'add_meta_boxes',       'snax_add_gallery_post_metabox', 10 ,2 );
add_action( 'add_meta_boxes',       'snax_add_item_metabox', 10 ,2 );
add_action( 'add_meta_boxes',       'snax_add_post_metabox', 10 ,2 );
add_action( 'add_meta_boxes',       'snax_add_meme_template_metabox', 10 ,2 );
add_action( 'add_meta_boxes',       'snax_add_fake_votes_metabox', 10 ,2 );

add_action( 'save_post',            'snax_save_list_post_metabox' );
add_action( 'save_post',            'snax_save_gallery_post_metabox' );
add_action( 'save_post',            'snax_save_item_metabox' );
add_action( 'save_post',            'snax_save_post_metabox' );
add_action( 'save_post',            'snax_save_meme_template_metabox' );
add_action( 'save_post',            'snax_save_fake_votes_metabox' );

add_action( 'wp_loaded',            'snax_admin_handle_post_actions' );
add_action( 'wp_trash_post',		'snax_admin_trash_item' );

// Custom columns.
add_action( 'manage_posts_custom_column' ,  'snax_render_custom_columns', 10, 2 );
add_action( 'restrict_manage_posts',        'snax_render_custom_columns_filters' );
add_action( 'pre_get_posts',                'snax_filter_by_custom_columns' );

// Bulk edit.
add_action( 'bulk_edit_custom_box', 'snax_add_set_format_to_bulk_edit', 10, 2 );
add_action( 'save_post', 'snax_save_bulk_edit', 10, 1 );

// Permalinks.
add_action( 'admin_init', 'snax_save_permalinks' );

/** Filters ****************************************************** */

// Custom columns.
add_filter( 'manage_posts_columns' , 'snax_register_custom_columns' );

add_filter( 'snax_admin_get_settings_fields', 'snax_customize_admin_settings_fields' );
