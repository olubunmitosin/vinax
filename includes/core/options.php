<?php
/**
 * Snax Options
 *
 * @package snax
 * @subpackage Options
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Get the default options
 *
 * @return array Filtered option names and values
 */
function snax_get_default_options() {

	return apply_filters( 'snax_get_default_options', array(

		/* General */

		'snax_active_formats'           => array( 'list', 'ranked_list', 'classic_list', 'image', 'audio', 'video', 'gallery', 'embed', 'text', 'meme', 'trivia_quiz', 'personality_quiz', 'classic_poll', 'versus_poll', 'binary_poll' ),
		'snax_items_per_page'           => 20,
		'snax_show_item_count_in_title' => 'on',
		'snax_max_upload_size'          => 2 * 1024 * 1024, // 2MB.

		/* Lists */

		'snax_active_item_forms'        => array( 'image', 'embed', 'audio', 'video' ),
		'snax_show_open_list_in_title'  => 'on',
		'snax_user_submission_limit'    => 10,  // How many items user can submit (-1 for no limit).

		/* Votes */

		'snax_voting_is_enabled'		=> 'on',
		'snax_guest_voting_is_enabled'	=> 'on',
		'snax_voting_post_types'		=> array( 'post' ),
		'snax_fake_vote_count_base'		=> '',

		/* Demo */

		'snax_demo_mode'                => 'off',

	) );
}

/**
 * Init plugin for immediate use
 */
function snax_default_setup() {
	snax_load_default_options();
	snax_create_and_assign_frontend_page();
}

/** General ********************************************************** */

/**
 * Return active formats.
 *
 * @param array   $default Optional. Default value.
 * @param WP_Post $post Optional. Default value.
 *
 * @return array
 */
function snax_get_active_formats_ids( $default = array(), $post = null ) {
	$post = get_post( $post );

	$formats_ids = (array) get_option( 'snax_active_formats', $default );

	return apply_filters( 'snax_active_formats', $formats_ids, $post );
}

/**
 * Return ordered list of formats.
 *
 * @return array
 */
function snax_get_formats_order() {
	$order_str = get_option( 'snax_formats_order', '' );
	$order_arr = explode( ',', $order_str );

	$order_arr = array_filter( $order_arr );

	return apply_filters( 'snax_formats_order', $order_arr );
}

/**
 * Return number of items to display on a single page, global setting
 *
 * @param int $default Optional. Default value.
 *
 * @return mixed|void
 */
function snax_get_global_items_per_page( $default = 20 ) {
	return apply_filters( 'snax_items_per_page', (int) get_option( 'snax_items_per_page', $default ) );
}

/**
 * Check whether to show items count in a post title.
 *
 * @param string $default Optional. Default value.
 *
 * @return bool
 */
function snax_show_item_count_in_title( $default = 'on' ) {
	$show = apply_filters( 'snax_show_item_count_in_title', get_option( 'snax_show_item_count_in_title', $default ) );

	return 'on' === $show;
}

/**
 * Check whether to disable traditinal WP login form.
 *
 * @param string $default Optional. Default value.
 *
 * @return bool
 */
function snax_disable_wp_login( $default = '' ) {
	if ( apply_filters( 'snax_disable_wp_login_option_active', false ) ) {
		$show = apply_filters( 'snax_disable_wp_login', get_option( 'snax_disable_wp_login', $default ) );
	} else {
		$show = '';
	}

	return 'on' === $show;
}

/**
 * Check whether to disable admin bar for logged in Snax Authors.
 *
 * @param string $default Optional. Default value.
 *
 * @return bool
 */
function snax_disable_admin_bar( $default = 'on' ) {
	$show = apply_filters( 'snax_disable_admin_bar', get_option( 'snax_disable_admin_bar', $default ) );

	return 'on' === $show;
}

/**
 * Check whether to disable dashboard access for logged in Snax Authors.
 *
 * @param string $default Optional. Default value.
 *
 * @return bool
 */
function snax_disable_dashboard_access( $default = '' ) {
	$disable = apply_filters( 'snax_disable_dashboard_access', get_option( 'snax_disable_dashboard_access', $default ) );

	return 'on' === $disable;
}

/**
 * Return Facebook App ID
 *
 * @return string
 */
function snax_get_facebook_app_id() {
	return apply_filters( 'snax_facebook_app_id', get_option( 'snax_facebook_app_id', '' ) );
}

/**
 * Check whether to allow users direct publishing
 *
 * @return bool
 */
function snax_skip_verification() {
	return 'standard' === apply_filters( 'snax_skip_verification', get_option( 'snax_skip_verification', 'none' ) );
}

/**
 * Check whether to send mail to admi when new post/item was added
 *
 * @return bool
 */
function snax_mail_notifications() {
	return 'standard' === apply_filters( 'snax_mail_notifications', get_option( 'snax_mail_notifications', 'standard' ) );
}

/**
 * Check whether to send mail to admin when new post/item was added
 *
 * @return bool
 */
function snax_show_origin() {
	return 'standard' === apply_filters( 'snax_show_origin', get_option( 'snax_show_origin', 'standard' ) );
}

/**
 * Check whether to allow Froala in items
 *
 * @return bool
 */
function snax_froala_for_items() {
	return 'standard' === apply_filters( 'snax_froala_for_items', get_option( 'snax_froala_for_items', 'none' ) );
}

/**
 * Check whether to allow Froala in open list items
 *
 * @return bool
 */
function snax_froala_for_list_items() {
	return 'standard' === apply_filters( 'snax_froala_for_list_items', get_option( 'snax_froala_for_list_items', 'none' ) );
}

/**
 * Check whether to allow comments for items
 *
 * @return bool
 */
function snax_display_comments_on_lists() {
	return 'standard' === apply_filters( 'snax_display_comments_on_lists', get_option( 'snax_display_comments_on_lists', 'standard' ) );
}

/**
 * Check whether to enable the login popup
 *
 * @return bool
 */
function snax_enable_login_popup() {
	return 'standard' === apply_filters( 'snax_enable_login_popup', get_option( 'snax_enable_login_popup', 'standard' ) );
}

/** Lists ************************************************************ */

/**
 * Return the list of active item forms.
 *
 * @param array   $default Optional. Default value.
 * @param WP_Post $post Optional. Default value.
 *
 * @return array
 */
function snax_get_active_item_forms_ids( $default = array(), $post = null ) {
	$post = get_post( $post );

	$forms_ids = (array) get_option( 'snax_active_item_forms', $default );

	return apply_filters( 'snax_active_item_forms', $forms_ids, $post );
}

/**
 * Return number of posts to display on a single page
 *
 * @param int $default              Optional. Default value.
 *
 * @return int
 */
function snax_get_posts_per_page( $default = 10 ) {
	return apply_filters( 'snax_posts_per_page', (int) get_option( 'snax_posts_per_page', $default ) );
}


/**
 * Check whether to show open list info in a post title.
 *
 * @param string $default Optional. Default value.
 *
 * @return bool
 */
function snax_show_open_list_in_title( $default = 'on' ) {
	$show = apply_filters( 'snax_show_open_list_in_title', get_option( 'snax_show_open_list_in_title', $default ) );

	return 'on' === $show;
}

/**
 * Return items count placeholder, used in titles.
 *
 * @param string $default Optional. Default value.
 *
 * @return string
 */
function snax_get_post_title_item_count_placeholder( $default = '%%items%%' ) {
	return apply_filters( 'snax_post_title_item_count_placeholder', $default );
}

/**
 * Is the anonymous posting allowed?
 *
 * @param bool $default Optional. Default value.
 *
 * @return bool
 */
function snax_allow_anonymous( $default = false ) {
	return apply_filters( 'snax_allow_anonymous', (bool) get_option( 'snax_allow_anonymous', $default ) );
}


/**
 * Return number of votes to display on a single page
 *
 * @param int $default Optional. Default value.
 *
 * @return mixed|void
 */
function snax_get_votes_per_page( $default = 3 ) {
	return apply_filters( 'snax_votes_per_page', (int) $default );
}


/** User > Slugs ******************************************************** */

/**
 * Return the user upvotes slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_upvotes_slug( $default = 'upvotes' ) {
	return apply_filters( 'snax_get_user_upvotes_slug', $default );
}

/**
 * Return the user downvotes slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_downvotes_slug( $default = 'downvotes' ) {
	return apply_filters( 'snax_get_user_downvotes_slug', $default );
}

/**
 * Return the user approved posts slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_approved_posts_slug( $default = 'approved' ) {
	return apply_filters( 'snax_get_user_approved_posts_slug', $default );
}

/**
 * Return the user draft posts slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_draft_posts_slug( $default = 'draft' ) {
	return apply_filters( 'snax_get_user_draft_posts_slug', $default );
}

/**
 * Return the user pending posts slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_pending_posts_slug( $default = 'pending' ) {
	return apply_filters( 'snax_get_user_pending_posts_slug', $default );
}

/**
 * Return the user approved items slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_approved_items_slug( $default = 'approved' ) {
	return apply_filters( 'snax_get_user_approved_items_slug', $default );
}

/**
 * Return the user pending items slug
 *
 * @param string $default Default value.
 *
 * @return string
 */
function snax_get_user_pending_items_slug( $default = 'pending' ) {
	return apply_filters( 'snax_get_user_pending_items_slug', $default );
}

/** Pages ******************************************************** */


/**
 * Return url of the Terms and Conditions page
 *
 * @param string $default Optional. Default value.
 *
 * @return string
 */
function snax_get_legal_page_url( $default = '' ) {
	$page_id = snax_get_legal_page_id();

	return ! empty( $page_id ) ? get_permalink( $page_id ) : $default;
}

/**
 * Return ID of the Terms and Conditions page
 *
 * @return int
 */
function snax_get_legal_page_id() {
	return apply_filters( 'snax_legal_page_id', get_option( 'snax_legal_page_id' ) );
}

/**
 * Return url of the page where user can submit a story
 *
 * @param string $default Optional. Default value.
 *
 * @return string
 */
function snax_get_frontend_submission_page_url( $default = '' ) {
	$page_id = snax_get_frontend_submission_page_id();

	return ! empty( $page_id ) ? get_permalink( $page_id ) : $default;
}

/**
 * Return ID of the page where user can submit a story
 *
 * @return int
 */
function snax_get_frontend_submission_page_id() {
	return (int) apply_filters( 'snax_frontend_submission_page_id', get_option( 'snax_frontend_submission_page_id' ) );
}

/**
 * Return url of the page where user can report any kind of abuse
 *
 * @param string $default Optional. Default value.
 *
 * @return string
 */
function snax_get_report_page_url( $default = '' ) {
	$page_id = snax_get_report_page_id();

	return ! empty( $page_id ) ? get_permalink( $page_id ) : $default;
}

/**
 * Return ID of the page where user can report any kind of abuse
 *
 * @return int
 */
function snax_get_report_page_id() {
	return apply_filters( 'snax_report_page_id', get_option( 'snax_report_page_id' ) );
}


/* Votes *********************************************************** */

/**
 * Check whether the voting system is enabled (globally, for all formats and post types)
 *
 * @return bool
 */
function snax_voting_is_enabled() {
	return 'on' === apply_filters( 'snax_voting_is_enabled', get_option( 'snax_voting_is_enabled', 'on' ) );
}

/**
 * Check whether guest user can vote
 *
 * @return bool
 */
function snax_guest_voting_is_enabled() {
	return 'on' === apply_filters( 'snax_guest_voting_is_enabled', get_option( 'snax_guest_voting_is_enabled', 'on' ) );
}

/**
 * Return list of post types than can be voted
 *
 * @return array
 */
function snax_voting_get_post_types() {
	$post_types = get_option( 'snax_voting_post_types', array() );

	// Allow voting for snax item on a single item page.
	$post_types[] = snax_get_item_post_type();

	$post_types = apply_filters( 'snax_voting_post_types', $post_types );

	if ( ! is_array( $post_types ) ) {
		$post_types = array();
	}

	return $post_types;
}

/**
 * Return fake vote count base
 *
 * @return int
 */
function snax_get_fake_vote_count_base() {
	return apply_filters( 'snax_fake_vote_count_base', get_option( 'snax_fake_vote_count_base', '' ) );
}

/**
 * Check whether to disable fake votes for new submissions
 *
 * @return int
 */
function snax_is_fake_vote_disabled_for_new() {
	return 'on' === apply_filters( 'snax_fake_vote_for_new', get_option( 'snax_fake_vote_for_new', 'on' ) );
}

/* Auth ************************************************************ */

/**
 * Check whether the login reCaptcha is enabled
 *
 * @return bool
 */
function snax_is_recatpcha_enabled_for_login_form() {
	return 'on' === apply_filters( 'snax_recatpcha_enabled_for_login_form', get_option( 'snax_login_recaptcha', 'off' ) );
}

/**
 * Return reCaptcha Site Key
 *
 * @return string
 */
function snax_get_recaptcha_site_key() {
	return apply_filters( 'snax_recaptcha_site_key', get_option( 'snax_recaptcha_site_key', '' ) );
}

/**
 * Return reCaptcha Secret
 *
 * @return string
 */
function snax_get_recaptcha_secret() {
	return apply_filters( 'snax_recaptcha_secret', get_option( 'snax_recaptcha_secret', '' ) );
}

/**
 * Return reCaptcha JS API url
 *
 * @return string
 */
function snax_get_recaptcha_js_api_url() {
	return apply_filters( 'snax_recaptcha_js_api_url', 'https://www.google.com/recaptcha/api.js' );
}

/**
 * Return reCaptcha site verify API url
 *
 * @return string
 */
function snax_get_recaptcha_verify_api_url() {
	return apply_filters( 'snax_recaptcha_verify_api_url', 'https://www.google.com/recaptcha/api/siteverify' );
}

/* Demo ************************************************************ */

/**
 * Check whether the demo mode is enabled
 *
 * @return bool
 */
function snax_is_demo_mode() {
	return apply_filters( 'snax_is_demo_mode', true );
}

/**
 * Return id of demo post
 *
 * @param string $format 		Post format.
 *
 * @return bool|int        		False if not set.
 */
function snax_get_demo_post_id( $format ) {
	if ( ! $format ) {
		return false;
	}

	$post_id = intval( get_option( 'snax_demo_'. $format .'_post_id' ) );

	if ( ! $post_id ) {
		$post_id = false;
	}

	return apply_filters( 'snax_demo_post_id', $post_id, $format );
}

/**
 * Return ids of demo posts
 *
 * @param string $format 		Post format.
 *
 * @return array
 */
function snax_get_demo_post_ids( $format ) {
	if ( ! $format ) {
		return array();
	}

	$post_ids = get_option( 'snax_demo_'. $format .'_post_ids' );

	if ( ! $post_ids ) {
		$post_ids = array();
	}

	return (array) apply_filters( 'snax_demo_post_ids', $post_ids, $format );
}

/* Limits ************************************************************ */

// -------------
// --- IMAGE ---
// -------------

/**
 * Check whether image upload is allowed
 *
 * @return bool
 */
function snax_is_image_upload_allowed() {
	return 'standard' === apply_filters( 'snax_image_upload_allowed', get_option( 'snax_image_upload_allowed', 'standard' ) );
}

/**
 * Return maximum size (in bytes) of uploaded image files
 *
 * @return int
 */
function snax_get_image_max_upload_size() {
	$bytes_2mb = 2 * 1024 * 1024;

	return apply_filters( 'snax_max_upload_size', get_option( 'snax_max_upload_size', $bytes_2mb ) );
}

/**
 * Return all allowed image mime types
 *
 * @return array
 */
function snax_get_all_image_allowed_types() {
	$types = array(
		'jpg',
		'jpeg',
		'png',
		'gif',
	);

	return apply_filters( 'snax_all_image_allowed_types', $types );
}

/**
 * Return image allowed mime types
 *
 * @return array
 */
function snax_get_image_allowed_types() {
	return apply_filters( 'snax_image_allowed_types', get_option( 'snax_image_allowed_types', snax_get_all_image_allowed_types() ) );
}

// -------------
// --- AUDIO ---
// -------------

/**
 * Check whether audio upload is allowed
 *
 * @return bool
 */
function snax_is_audio_upload_allowed() {
	return 'standard' === apply_filters( 'snax_audio_upload_allowed', get_option( 'snax_audio_upload_allowed', 'standard' ) );
}

/**
 * Return maximum size (in bytes) of uploaded audio files
 *
 * @return int
 */
function snax_get_audio_max_upload_size() {
	$bytes_5mb = 5 * 1024 * 1024;

	return apply_filters( 'snax_audio_max_upload_size', get_option( 'snax_audio_max_upload_size', $bytes_5mb ) );
}

/**
 * Return all allowed audio mime types
 *
 * @return array
 */
function snax_get_all_audio_allowed_types() {
	$types = array(
		'mp3',
		'm4a',
		'ogg',
		'wav',
	);

	return apply_filters( 'snax_all_audio_allowed_types', $types );
}

/**
 * Return audio allowed mime types
 *
 * @return array
 */
function snax_get_audio_allowed_types() {
	return apply_filters( 'snax_audio_allowed_types', get_option( 'snax_audio_allowed_types', array( 'mp3' ) ) );
}

// -------------
// --- VIDEO ---
// -------------

/**
 * Check whether video upload is allowed
 *
 * @return bool
 */
function snax_is_video_upload_allowed() {
	return 'standard' === apply_filters( 'snax_video_upload_allowed', get_option( 'snax_video_upload_allowed', 'standard' ) );
}

/**
 * Return maximum size (in bytes) of uploaded video files
 *
 * @return int
 */
function snax_get_video_max_upload_size() {
	$bytes_10mb = 10 * 1024 * 1024;

	return apply_filters( 'snax_video_max_upload_size', get_option( 'snax_video_max_upload_size', $bytes_10mb ) );
}

/**
 * Return all allowed video mime types
 *
 * @return array
 */
function snax_get_all_video_allowed_types() {
	$types = array(
		'mp4',
		'm4v',
		'mov',
		'wmv',
		'avi',
		'mpg',
		'ogv',
		'3gp',
		'3g2',
	);

	return apply_filters( 'snax_all_video_allowed_types', $types );
}

/**
 * Return video allowed mime types
 *
 * @return array
 */
function snax_get_video_allowed_types() {
	return apply_filters( 'snax_video_allowed_types', get_option( 'snax_video_allowed_types', array( 'mp4' ) ) );
}

// -------------
// --- ITEMS ---
// -------------

/**
 * Return maximum number of items that can be uploaded to an existing post
 *
 * @param int $default Optional. Default value.
 *
 * @return int
 */
function snax_get_user_submission_limit( $default = 10 ) {
	return (int) apply_filters( 'snax_user_submission_limit', get_option( 'snax_user_submission_limit', $default ) );
}

/**
 * Return maximum number of tags that can be assigned to a post during submission
 *
 * @return int
 */
function snax_get_tags_limit() {
	return (int) apply_filters( 'snax_tags_limit', get_option( 'snax_tags_limit', 10 ) );
}

/**
 * Return maximum number of user submitted posts, in a day
 *
 * @param int $default Optional. Default value.
 *
 * @return int
 */
function snax_get_user_posts_per_day( $default = 1 ) {
	return (int) apply_filters( 'snax_user_posts_per_day', get_option( 'snax_user_posts_per_day', $default ) );
}

/**
 * Return maximum number of items that can be uploaded to a post during submission
 *
 * @param int $default Optional. Default value.
 *
 * @return int
 */
function snax_get_new_post_items_limit( $default = 20 ) {
	return (int) apply_filters( 'snax_new_post_items_limit', get_option( 'snax_new_post_items_limit', $default ) );
}

/**
 * Return maximum number of characters allowed in a post title
 *
 * @return int
 */
function snax_get_post_title_max_length() {
	return (int) apply_filters( 'snax_post_title_max_length', get_option( 'snax_post_title_max_length', 64 ) );
}

/**
 * Return maximum number of characters allowed in a post description (short content)
 *
 * @return int
 */
function snax_get_post_description_max_length() {
	return (int) apply_filters( 'snax_post_description_max_length', get_option( 'snax_post_description_max_length', 3600 ) );
}


/**
 * Return maximum number of characters allowed in a post description (short content)
 *
 * @return int
 */
function snax_get_post_description_word_limit() {
    return (int) apply_filters( 'snax_post_description_word_limit', get_option( 'snax_post_description_word_limit', 100 ) );
}


/**
 * Return maximum number of characters allowed in a post content
 *
 * @return int
 */
function snax_get_post_content_max_length() {
	return (int) apply_filters( 'snax_post_content_max_length', get_option( 'snax_post_content_max_length', 7200 ) );
}

/**
 * Return maximum number of characters allowed in a post title
 *
 * @return int
 */
function snax_get_item_title_max_length() {
	return (int) apply_filters( 'snax_item_title_max_length', get_option( 'snax_item_title_max_length', 64 ) );
}

/**
 * Return maximum number of characters allowed in a post content
 *
 * @return int
 */
function snax_get_item_content_max_length() {
	return (int) apply_filters( 'snax_item_content_max_length', get_option( 'snax_item_content_max_length', 3600 ) );
}

/**
 * Return maximum number of characters allowed in an item source
 *
 * @return int
 */
function snax_get_item_source_max_length() {
	return (int) apply_filters( 'snax_item_source_max_length', get_option( 'snax_item_source_max_length', 256 ) );
}

/**
 * Return maximum number of characters allowed in an item referral link
 *
 * @return int
 */
function snax_get_item_ref_link_max_length() {
	return (int) apply_filters( 'snax_item_ref_link_max_length', get_option( 'snax_item_ref_link_max_length', 1024 ) );
}

/**
 * Return the slug of the custom post type for items
 *
 * @return string 	Snax item slug
 */
function snax_get_item_slug() {
	return apply_filters( 'snax_item_slug', get_option( 'snax_item_slug', 'snax_item' ) );
}

/**
 * Return the url prefix for snax elements
 *
 * @return string 	Url variable
 */
function snax_get_url_var_prefix() {
	$default = get_option( 'snax_url_var_prefix' );

	// Set default only if is not set.
	if ( false === $default ) {
		$default = 'snax';
	}

	return apply_filters( 'snax_url_var_prefix', $default );
}

// -------------
// --- POLLS ---
// -------------
/**
 * Return per user votes limit.
 *
 * @param int $default Optional. Default value.
 *
 * @return int
 */
function snax_get_limits_poll_vote_limit( $default = -1 ) {
	return (int) apply_filters( 'snax_limits_poll_vote_limit', get_option( 'snax_limits_poll_vote_limit', $default ) );
}

/* Embedly ************************************************************ */

/**
 * Check whether the Embedly integration is enabled
 *
 * @return bool
 */
function snax_is_embedly_enabled() {
	return 'on' === apply_filters( 'snax_is_embedly_enabled', get_option( 'snax_embedly_enable', 'off' ) );
}

/**
 * Check whether the Embedly dark skin is enabled
 *
 * @return bool
 */
function snax_is_embedly_dark_skin() {
	return 'on' === apply_filters( 'snax_is_embedly_dark_skin', get_option( 'snax_embedly_dark_skin', 'off' ) );
}

/**
 * Check whether the Embedly share buttons are enabled
 *
 * @return bool
 */
function snax_is_embedly_buttons() {
	return 'on' === apply_filters( 'snax_is_embedly_buttons', get_option( 'snax_embedly_buttons', 'on' ) );
}

/**
 * Return Embedly width
 *
 * @return int
 */
function snax_get_embedly_width() {
	return apply_filters( 'snax_embedly_width', get_option( 'snax_embedly_width', '' ) );
}

/**
 * Get embed alignment
 *
 * @return bool
 */
function snax_get_embedly_alignment() {
	return apply_filters( 'snax_embedly_alignment', get_option( 'snax_embedly_alignment', 'center' ) );
}

/**
 * Return Facebook App ID
 *
 * @return string
 */
function snax_get_embedly_api_key() {
	return apply_filters( 'snax_embedly_api_key', get_option( 'snax_embedly_api_key', '' ) );
}

/* Memes ************************************************************ */

/**
 * Check whether the recaption the post is enabled.
 *
 * @return bool
 */
function snax_is_memes_recaption_enabled() {
	return 'on' === apply_filters( 'snax_is_memes_recaption_enabled', get_option( 'snax_memes_recaption_enable', 'off' ) );
}
/**
 * Check whether the content field is enabled
 *
 * @return bool
 */
function snax_is_memes_content_enabled() {
	return 'on' === apply_filters( 'snax_is_memes_content_enabled', get_option( 'snax_memes_content_enable', 'off' ) );
}
