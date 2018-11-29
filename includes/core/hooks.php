<?php
/**
 * Snax Actions and Filters
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

// Activation.
add_action( 'snax_activation', 'snax_install_schemas' );
add_action( 'snax_activation', 'snax_default_setup' );
add_action( 'snax_activation', 'snax_setup_user_roles' );
add_action( 'snax_activation', 'snax_welcome_redirect' );

// Init.
add_action( 'plugins_loaded',   'snax_loaded', 10 );
add_action( 'plugins_loaded',   'snax_install_schemas', 11 );
add_action( 'snax_loaded',      'snax_register_shortcodes', 10 );
add_action( 'init',             'snax_init' );
add_action( 'snax_init',        'snax_hide_admin_bar', 10 );
add_action( 'snax_init', 		'snax_block_dashboard_access', 10 );

// Network.
add_filter( 'network_admin_plugin_action_links', 'snax_network_admin_plugin_action_links', 10, 4 );

// Loop.
add_action( 'loop_start',   'snax_custom_loop_start', 0 );
add_action( 'loop_end',     'snax_custom_loop_end', 0 );

// Common.
add_action( 'template_redirect', 			'snax_redirect_to_url' );
add_action( 'snax_new_post_redirect_url',	'snax_redirect_to_draft_edition', 10, 2 );
add_action( 'wp_logout', 					'snax_logout_redirect' );
add_action( 'snax_enqueue_fb_sdk', 			'snax_enqueue_fb_sdk' );

// Ajax / CRUD.
add_action( 'wp_ajax_snax_add_media_item',      'snax_ajax_add_media_item' );
add_action( 'wp_ajax_snax_add_embed_item',      'snax_ajax_add_embed_item' );
add_action( 'wp_ajax_snax_add_text_item',       'snax_ajax_add_text_item' );
add_action( 'wp_ajax_snax_update_items',        'snax_ajax_update_items' );
add_action( 'wp_ajax_snax_delete_item',         'snax_ajax_delete_item' );
add_action( 'wp_ajax_snax_delete_media',        'snax_ajax_delete_media' );
add_action( 'wp_ajax_snax_update_media_meta',	'snax_ajax_update_media_meta' );
add_action( 'wp_ajax_snax_vote_item',           'snax_ajax_vote_item' );
add_action( 'wp_ajax_nopriv_snax_vote_item',    'snax_ajax_vote_item' );
add_action( 'wp_ajax_snax_set_item_as_featured','snax_ajax_set_item_as_featured' );
add_action( 'wp_ajax_snax_load_user_uploaded_images', 'snax_ajax_load_user_uploaded_images' );
add_action( 'wp_ajax_snax_save_image_from_url', 'snax_ajax_save_image_from_url' );
add_action( 'wp_ajax_snax_import_meme', 		'snax_ajax_import_meme' );

// Ajax / Templates.
add_action( 'wp_ajax_snax_load_featured_image_tpl', 'snax_ajax_load_featured_image_tpl' );
add_action( 'wp_ajax_snax_load_media_item_tpl', 'snax_ajax_load_media_item_tpl' );
add_action( 'wp_ajax_snax_load_content_embed_tpl', 'snax_ajax_load_content_embed_tpl' );
add_action( 'wp_ajax_snax_load_embed_item_tpl', 'snax_ajax_load_embed_item_tpl' );
add_action( 'wp_ajax_snax_load_item_card_tpl',  'snax_ajax_load_item_card_tpl' );
add_action( 'wp_ajax_snax_load_media_tpl',      'snax_ajax_load_media_tpl' );
add_action( 'wp_ajax_snax_load_embed_tpl',      'snax_ajax_load_embed_tpl' );
add_action( 'wp_ajax_snax_cancel_submission',      'snax_ajax_cancel_submission' );
add_action( 'wp_ajax_snax_fetch_og_data',      'snax_ajax_fetch_og_data' );

// Ajax login/forgot pass.
add_action( 'wp_ajax_nopriv_snax_login',        'snax_ajax_login' );
add_action( 'wp_ajax_nopriv_snax_forgot_pass',  'snax_ajax_forgot_pass' );

add_action( 'wp_ajax_snax_get_tags',      		'snax_ajax_get_tags' );

// Post.
add_action( 'parse_request',            'snax_handle_post_actions' );
add_action( 'admin_init',               'snax_handle_user_actions' );
add_action( 'before_delete_post',       'snax_remove_post_dependencies' );
add_action( 'pre_get_posts',            'snax_allow_authors_access_unpublished_posts' );
add_action( 'transition_post_status',   'snax_post_status_changed', 10, 3 );
add_action( 'snax_item_approved',       'snax_post_update_submission_count' );
add_action( 'snax_after_content_single_post',	'snax_render_post_referral_link', 9 );
add_action( 'snax_after_content_single_post',	'snax_post_scripts' );
add_action( 'snax_after_content_single_post',	'snax_render_meme_recaption' );
add_action( 'snax_after_content_single_post',	'snax_render_meme_see_similar' );
add_action( 'snax_after_content_single_post',	'snax_render_post_origin' );
add_action( 'snax_bump_post_submission_count',  'snax_close_post_submission', 10, 3 );
add_action( 'snax_post_approved',               'snax_post_items_update_status', 10, 1 );

// Slug (auto-modification turned off for now).
//add_action( 'save_post',                'snax_remove_placeholder_from_slug_on_create', 10, 3 );
//add_filter( 'get_sample_permalink',     'snax_remove_placeholder_from_slug_on_edit', 10, 5 ); // Post slug inline edition.

// Item.
add_action( 'wp_insert_post',                   'snax_update_post_on_item_change', 10, 3 );
// We don't want to show built-in featured media (even if set and supported) on:
// 1. single snax item post
// 2. single image post format
// 3. single embed post format
// For all above cases the featured media just duplicate, on a single post view, media in post content.
add_action( 'loop_start',                       'snax_disable_default_featured_media' );
add_action( 'loop_end',                         'snax_enable_default_featured_media' );
add_filter( 'the_content',                      'snax_enable_default_featured_media_in_content', 5 );
add_action( 'snax_after_content_single_item',   'snax_item_scripts' );
add_action( 'transition_post_status',           'snax_item_status_changed', 10, 3 );
add_action( 'admin_menu',						'disable_new_item_for_snax_items' );
add_filter( 'comment_post_redirect', 			'snax_return_item_comment_via_ajax', 10, 2 );
add_action( 'save_post', 'snax_reload_meta_when_item_saved' );

// Vote.
add_action( 'snax_item_added', 'snax_init_votes_metadata', 10, 2 );
add_action( 'comment_reply_link_args', 'snax_add_votes_to_comments', 10, 3 );

// Popup.
add_action( 'wp_footer', 'snax_render_popup_content' );

// Login/Register.
add_action( 'snax_popup_content',   'snax_render_login_form' );
add_filter( 'login_form_top',       'snax_render_login_form_errors', 5 );
add_filter( 'login_form_middle',    'snax_render_login_recaptcha' );
add_filter( 'login_url',    		'snax_login_url', 10, 3 );
add_filter( 'lostpassword_url',    	'snax_lostpassword_url', 10, 2 );

// Widgets.
add_action( 'widgets_init', 'snax_widgets_init' );

// Media.
add_action( 'snax_before_item_media', 'snax_before_media_hooks' );
add_action( 'snax_before_card_media', 'snax_before_media_hooks' );
remove_action( 'snax_after_item_media', 'snax_after_media_hooks' );
remove_action( 'snax_after_card_media', 'snax_after_media_hooks' );

// Admin bar.
add_action( 'admin_bar_menu', 'snax_admin_bar_menu', 500 );

// Frontend submission.
add_action( 'snax_frontend_submission_form_end', 'snax_frontend_submission_render_hidden_data' );
add_action( 'snax_frontend_submission_form_end', 'snax_frontend_submission_render_scripts' );
add_action( 'parse_request', 'snax_handle_new_submission' );
add_action( 'parse_request', 'snax_set_demo_data' );
add_action( 'parse_request', 'snax_set_edit_data' );

add_action( 'snax_before_upload_form', 'snax_upload_form_load_resources' );
add_action( 'snax_after_upload_form', 'snax_upload_form_render_form_internals' );
add_action( 'snax_before_upload_form', 'snax_set_new_upload_size_limit' );
add_action( 'snax_after_upload_form', 'snax_reset_upload_size_limit' );
add_action( 'snax_before_upload_form', 'snax_allow_snax_author_to_upload' );
add_action( 'snax_after_upload_form', 'snax_deny_snax_author_to_upload' );
add_action( 'snax_before_frontend_submission_form', 'snax_frontend_submission_validation_feedback' );
add_action( 'snax_before_frontend_submission_form', 'snax_frontend_submission_draft_saved' );

add_action( 'snax_end_form_edit_item_embed', 'snax_render_embed_form_internals' );
add_action( 'snax_end_form_edit_item_text',  'snax_render_text_form_internals' );

add_filter( 'snax_form_file_upload_methods', 'snax_upload_media_from_embed_code', 10, 2 );

// Demo.
add_action( 'snax_frontend_submission_form_start', 'snax_demo_post' );

// Feedback.
add_action( 'wp_footer', 'snax_render_feedback' );

// Cron.
add_action( 'snax_clean_up_junk_uploads',      'snax_do_clean_up_junk_uploads' );

// Mail notifications.
add_action( 'snax_item_added',                  'snax_notify_admin_about_new_item', 10, 3 );

// Backward compatibility.
add_action( 'snax_post_added', 'snax_do_format_specific_action', 10, 2 );

// Edit post link.
add_action( 'wp_footer', 'snax_render_edit_post_link' );

// Waiting room.

add_action( 'pre_get_posts', 	        'snax_apply_waiting_room_filter',5, 1 );
add_filter( 'template_include', 	    'snax_apply_waiting_room_template', 1, 1 );


/** Filters ******************************************************* */


// Capabilities.
add_filter( 'map_meta_cap', 'snax_map_meta_caps', 10, 4 );
add_filter( 'snax_map_meta_caps', 'snax_map_posts_caps', 10, 4 );
add_filter( 'snax_map_meta_caps', 'snax_map_items_caps', 10, 4 );
add_filter( 'snax_map_meta_caps', 'snax_map_votes_caps', 10, 4 );
add_filter( 'snax_map_meta_caps', 'snax_map_users_caps', 10, 4 );

// Post.
add_filter( 'post_class',                       'snax_add_post_class', 10, 3 );
add_filter( 'the_content',						'snax_post_prepend_notes' );
// Priority of 9 - before default 10 and do_shortcode on 11.
add_filter( 'the_content',                      'snax_post_content' , 9 );
add_filter( 'the_content',                      'snax_add_caption_source' );

add_filter( 'the_posts',                        'snax_post_pagination', 10, 2 );
add_filter( 'snax_show_item_upvote_link', 		'snax_post_disable_voting_actions', 10, 2 );
add_filter( 'snax_show_item_downvote_link', 	'snax_post_disable_voting_actions', 10, 2 );
add_filter( 'snax_is_post_open_for_submission', 'snax_allow_submitting_to_closed_list', 10, 2 );
add_filter( 'snax_render_new_item_form',		'snax_render_new_item_form_for_admins', 10, 1 );

// Post excerpt.
add_filter( 'get_the_excerpt', 'snax_remove_post_content', 5 );
add_filter( 'get_the_excerpt', 'snax_restore_post_content', 15 );

// Post title.
add_filter( 'the_title', 'snax_add_post_info_to_title', 10, 2 );
add_filter( 'pre_get_document_title', 'snax_post_title_short_circuit', 99, 1 );   	// Hook at the end. Eg. Yoast SEO uses this hook with 15 priority.
add_filter( 'wp_title', 'snax_post_title_short_circuit', 99, 3 );   				// Hook at the end. Eg. Yoast SEO uses this hook with 15 priority.
add_filter( 'document_title_parts', 'snax_post_title_parts', 10, 1 );

// Item.
add_filter( 'the_content', 			'snax_strip_embed_url_from_embed_content', 5 );    // 5 to hook before WP converts embed url to code.
add_filter( 'the_content', 			'snax_item_content' );
add_filter( 'the_title', 			'snax_item_title', 10, 2 );
add_filter( 'the_content',      	'snax_item_prepend_notes' );
add_filter( 'get_canonical_url',	'snax_item_canonical_url', 1, 2 );

// Item comments.
add_action( 'wp_ajax_snax_load_more_item_comments', 		'snax_ajax_load_more_item_comments' );
add_action( 'wp_ajax_nopriv_snax_load_more_item_comments',	'snax_ajax_load_more_item_comments' );
add_filter( 'comment_reply_link_args', 'snax_item_comment_respond_id', 10, 3 );
add_filter( 'cancel_comment_reply_link', 'snax_item_cancel_comment_respond_id', 10, 3 );

// Frontend submission.
add_filter( 'snax_show_create_button',  		'snax_hide_create_button' );
add_filter( 'the_content',              		'snax_append_frontend_submission_form' );
add_filter( 'the_title',                		'snax_frontend_submission_the_title', 10, 2 );
add_filter( 'body_class',               		'snax_frontend_submission_body_class' );

// Reset password and login page.
add_filter( 'retrieve_password_message', 		'snax_set_custom_password_reset_url_in_mail', 90, 4 );

// Media.
add_filter( 'snax_delete_media', 'snax_prevent_deletion_attached_media', 11, 2 );

// Froala editor.
add_filter( "async_upload_snax_froala_image", 'snax_froala_image_uploaded_response' );

// Menu.
add_filter( 'wp_setup_nav_menu_item', 'snax_setup_nav_menu_item', 10, 1 );

// Embeds.
add_filter( 'pre_oembed_result',                            'snax_facebook_oembed_result', 10, 3 );
add_action( 'snax_post_format_embed_created',               'snax_custom_download_embed_featured_media' );
add_filter( 'snax_custom_download_embed_featured_media',    'snax_custom_featured_media_supported_providers', 10, 2 );
add_filter( 'plugins_loaded',								'snax_add_embedly_providers' );
add_filter( 'the_content',                      			'snax_add_embedly_script', 90, 1 );

// Votes.
add_filter( 'snax_content_shortcode_output', 	'snax_allow_voting_for_post_types' );
add_filter( 'snax_get_voting_score', 			'snax_fake_vote_count', 11, 2 );
add_action( 'snax_post_voting_box',             'snax_render_post_voting_box' );
add_action( 'snax_post_added',                  'snax_disable_fake_votes', 10, 2 );

// Body classes.
add_filter( 'body_class', 'snax_body_class' );

add_filter( 'wp_video_extensions', 'snax_allow_playing_mov_files' );
add_filter( 'wp_video_shortcode', 'snax_fix_mov_videos', 99, 5 );
