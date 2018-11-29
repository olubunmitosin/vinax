<?php
/**
 * Snax User Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}


/**
 * Checks if user is not active
 *
 * @param int $user_id          The user id to check.
 *
 * @return bool
 */
function snax_is_user_inactive( $user_id = 0 ) {
	return ! snax_is_user_active( $user_id );
}

/**
 * Checks if user is logged in
 *
 * @param int $user_id      The user id to check.
 *
 * @return bool
 */
function snax_is_user_active( $user_id = 0 ) {
	$is_active = false;

	// Default to current user.
	if ( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	if ( ! empty( $user_id ) ) {
		$user           = get_user_by( 'id', $user_id );
		$user_logged_in = $user->exists();

		$is_active = $user_logged_in;
	}

	return apply_filters( 'snax_is_user_active', $is_active, $user_id );
}

/**
 * Return number of all user submitted items
 *
 * @param int  $author_id       Author id.
 * @param int  $post_id         Post id.
 * @param bool $ip_check        Whether or not to check IP address.
 *
 * @return int
 */
function snax_get_user_submission_count( $author_id, $post_id, $ip_check = false ) {
	$query_args = array(
		'post_type'   => snax_get_item_post_type(),
		'post_parent' => $post_id,
		'post_status' => array( snax_get_item_approved_status(), snax_get_item_pending_status(), 'draft' ),
		'author'      => $author_id,
	);


	$query = new WP_Query( $query_args );

	return (int) $query->found_posts;
}

/**
 * Check whether user can submit another item
 *
 * @param int $post_id          Post id.
 * @param int $user_id          User id.
 *
 * @return mixed|void
 */
function snax_user_reached_submitted_items_limit( $post_id = 0, $user_id = 0 ) {
	$reached    = true;
	$post       = get_post( $post_id );

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$is_admin	= user_can( $user_id, 'administrator' );
	$is_author  = (int) $user_id === (int) $post->post_author;

	// Admin/author has no limits.
	if ( $is_admin || $is_author ) {
		$reached = false;
	} else {
		$submission_count  = snax_get_user_submission_count( $user_id, $post->ID, true );
		$submissions_limit = snax_get_user_submission_limit();

		if ( -1 === $submissions_limit || $submission_count < $submissions_limit ) {
			$reached = false;
		}
	}

	return apply_filters( 'snax_user_reached_submitted_items_limit', $reached, $post->ID, $user_id );
}

/**
 * Check whether user can submit another post
 *
 * @param int $user_id          User id.
 *
 * @return mixed|void
 */
function snax_user_reached_submitted_posts_limit( $user_id = 0 ) {
	$reached    = true;
	$edit = false;
	$format	 = filter_input( INPUT_GET, snax_get_url_var( 'format' ), FILTER_SANITIZE_STRING );
	$post_id = filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );
	if ( snax_is_active_format( $format ) && $post_id && current_user_can( 'snax_edit_posts', $post_id ) ) {
		$edit = true;
	}

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Admin can submit without limits.
	if ( user_can( $user_id, 'administrator' ) || $edit ) {
		$reached = false;
	} else {
		// User uploads are limited, per day.
		$today = getdate( current_time( 'timestamp' ) );

		$posts_from_today = array(
			'year'      => $today['year'],
			'monthnum'  => $today['mon'],
			'day'       => $today['mday'],
		);

		$post_count  = snax_get_user_post_count( $user_id, $posts_from_today, true );
		$posts_limit = snax_get_user_posts_per_day();

		if ( -1 === $posts_limit || $post_count < $posts_limit ) {
			$reached = false;
		}
	}

	return apply_filters( 'snax_user_reached_submitted_posts_limit', $reached, $user_id );
}

/**
 * Allow author to access their draft/pending posts
 *
 * @param WP_Query $query               WP Query object.
 */
function snax_allow_authors_access_unpublished_posts( $query ) {
	/**
	 * Check if query is an instance of WP_Query.
	 * Some plugins, like BuddyPress may change it.
	 */
	if ( ! ( $query instanceof WP_Query ) ) {
		return;
	}

	if ( ! is_admin() && $query->is_main_query() ) {
		$post           = get_post( $query->get( 'p' ) );
		$unpublished_post   = in_array( get_post_status( $post ), array( snax_get_post_pending_status(), snax_get_post_draft_status() ), true );

		if ( $unpublished_post && ( snax_is_format( null, $post ) || snax_is_item( $post ) ) ) {
			// Author should see its pending posts.
			if ( (int) get_current_user_id() === (int) $post->post_author ) {
				$query->set( 'post_status', array( 'publish', 'pending', 'draft' ) );
			}
		}
	}
}

/**
 * Return url of user profile page
 *
 * @param int $user_id          User id.
 *
 * @return string
 */
function snax_get_user_profile_page( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'snax_user_profile_page', '', $user_id );
}

/**
 * Return url of pending posts page
 *
 * @param int $user_id      User id.
 *
 * @return string
 */
function snax_get_user_pending_posts_page( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'snax_user_pending_posts_page', '', $user_id );
}

/**
 * Return url of approved posts page
 *
 * @param int $user_id      User id.
 *
 * @return string
 */
function snax_get_user_approved_posts_page( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'snax_user_approved_posts_page', '', $user_id );
}

/**
 * Return url of user's pending items page
 *
 * @param int $user_id          User id.
 *
 * @return string
 */
function snax_get_user_pending_items_page( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'snax_user_pending_items_page', '', $user_id );
}

/**
 * Return url of user's approved items page
 *
 * @param int $user_id          User id.
 *
 * @return string
 */
function snax_get_user_approved_items_page( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	return apply_filters( 'snax_user_approved_items_page', '', $user_id );
}

/**
 * Return the number of user items
 *
 * @param int $user_id          User id.
 *
 * @return int
 */
function snax_get_user_item_count( $user_id = 0 ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$query_args = array(
		'author'                => $user_id,
		'post_type'             => snax_get_item_post_type(),
		'post_status'           => 'publish',
		'posts_per_page'        => -1,
		'ignore_sticky_posts'   => false,
	);

	$query = new WP_Query( $query_args );

	$count = $query->found_posts;

	return apply_filters( 'snax_user_item_count', $count, $user_id );
}

/**
 * Return number of user submitted posts
 *
 * @param int   $user_id          User id.
 * @param array $extra_args     Extra query args.
 * @param bool  $ip_check        Whether or not to check IP address.
 *
 * @return int
 */
function snax_get_user_post_count( $user_id = 0, $extra_args = array(), $ip_check = false ) {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$query_args = array(
		'author'                => $user_id,
		'post_type'             => 'post',
		'post_status'           => array( snax_get_post_approved_status(), snax_get_post_pending_status() ),
		'tax_query'            => array(
			array(
				'taxonomy' => snax_get_snax_format_taxonomy_slug(),
				'field' => 'slug',
				'operator' => 'EXISTS',
			),
		),
		'posts_per_page'        => -1,
		'ignore_sticky_posts'   => false,
	);

	$query_args = wp_parse_args( $query_args, $extra_args );

	$query = new WP_Query( $query_args );

	$count = $query->found_posts;

	return apply_filters( 'snax_user_post_count', $count, $user_id );
}

/**
 * Return all Snax Items without parent that belongs to the user
 *
 * @param string $parent_format        Snax format.
 * @param int    $user_id              User id.
 *
 * @return array
 */
function snax_get_user_orphan_items( $parent_format, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return array();
	}

	$query_args = array(
		'author'            => $user_id,
		'post_parent'       => 0, // Orphans are not assigned.
		'post_type'         => snax_get_item_post_type(),
		'post_status'       => array( 'publish', 'pending', 'draft' ),
		'posts_per_page'    => -1,
		'meta_query'        => array(
			array(
				'key'       => '_snax_parent_format',
				'value'     => $parent_format,
				'compare'   => '=',
			),
		),
	);

	$posts = get_posts( $query_args );

	return $posts;
}

/**
 * Get user uploaded media belongs to a post
 *
 * @param string $type          Media type.
 * @param int    $user_id       User id.
 * @param int    $post_id       Post id.
 *
 * @return int            Media id or 0 if not found.
 */
function snax_get_user_uploaded_media_id( $type, $user_id = 0, $post_id = 0 ) {
	$media_id = 0;

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $post_id ) {
		$post_id = get_the_ID();
	}

	$query_args = array(
		'author'                => $user_id,
		'post_type'             => 'attachment',
		'post_status'           => 'inherit',
		'post_parent'           => 0, // It can't be assigned to any post.
		'meta_query'            => array(
			'relation'  => 'AND',
			array(
				'key'   => '_snax_media_belongs_to',
				'value' => $post_id,
			),
			array(
				'key'   => '_snax_media_type',
				'value' => $type,
			),
		),
		'limit'                 => 1,
	);

	$posts = get_posts( $query_args );

	if ( 1 === count( $posts ) ) {
		$media_id = $posts[0]->ID;
	}

	return $media_id;
}

/**
 * Remove user uploaded media
 *
 * @param int   $user_id        User id.
 * @param array $extra_args     Extra WP_Query argunents. To override defualts.
 *
 * @return bool
 */
function snax_remove_user_uploaded_media( $user_id = 0, $extra_args = array() ) {
	$bool = true;

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$query_args = array(
		'author'                => $user_id,
		'post_type'             => 'attachment',
		'post_status'           => 'inherit',
		'post_parent'           => 0, // It can't be assigned to any post.
		'meta_query'            => array(
			array(
				'key'     => '_snax_media',
				'compare' => 'EXISTS',
			),
		),
		'ignore_sticky_posts'   => false,
	);

	$query_args = wp_parse_args( $query_args, $extra_args );

	$attahcments = get_posts( $query_args );

	foreach ( $attahcments as $attahcment ) {
		$bool = (bool) wp_delete_attachment( $attahcment->ID, true );
	}

	return $bool;
}

/**
 * Return all format related media uploaded by the user
 *
 * @param string $parent_format        Snax parent post format.
 * @param int    $user_id              User id.
 *
 * @return array
 */
function snax_get_user_uploaded_media( $parent_format, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return array();
	}

	$query_args = array(
		'author'                => $user_id,
		'post_type'             => 'attachment',
		'post_status'           => 'inherit',
		'post_parent'           => 0, // It can't be assigned to any post.
		'meta_query'            => array(
			array(
				'key'       => '_snax_parent_format',
				'value'     => $parent_format,
				'compare'   => '=',
			),
		),
	);

	$query_args = wp_parse_args( $query_args );

	$posts = get_posts( $query_args );

	return $posts;
}

/**
 * Get login popup url variable
 *
 * @return str
 */
function snax_get_login_popup_url_variable() {
	$prefix = snax_get_url_var_prefix();
	$suffix = apply_filters( 'snax_get_login_popup_url_variable_suffix', '_login_popup' );
	return $prefix . $suffix;
}

/**
 * Replace reset link in the mail with our own
 *
 * @param str    $message    Email content.
 * @param str    $key 		 Reset key.
 * @param str 	 $user_login User login.
 * @param WpUser $user_data  User object.
 * @return str
 */
function snax_set_custom_password_reset_url_in_mail( $message, $key, $user_login, $user_data ) {
	if ( ! snax_enable_login_popup() ) {
		return $message;
	}

	$redirect_url = filter_input( INPUT_POST, 'redirect_to', FILTER_SANITIZE_STRING );
	$redirect_url = strtok( $redirect_url, '?' );
	if ( ! filter_var( $redirect_url, FILTER_VALIDATE_URL ) ) {
		$redirect_url = get_home_url();
	}
	if ( strpos( $redirect_url, "/?" ) ) {
		$snax_reset_url = $redirect_url . '&' . snax_get_login_popup_url_variable() . '=reset_password';
	} else {
		$snax_reset_url = $redirect_url . '/?' . snax_get_login_popup_url_variable() . '=reset_password';
	}
	if ( ! empty( $snax_reset_url ) ) {
		$snax_reset_url = $snax_reset_url . '&key=' . $key . '&login=' . rawurlencode( $user_login );
		$message = preg_replace( '/<htt(.*)>/', $snax_reset_url, $message );
	}
	return $message;
}

/**
 * Filters the login URL.
 *
 * @param string $login_url    The login URL. Not HTML-encoded.
 * @param string $redirect     The path to redirect to on login, if supplied.
 * @param bool   $force_reauth Whether to force reauthorization, even if a cookie is present.
 */
function snax_login_url( $login_url, $redirect, $force_reauth ) {
	if ( ! snax_enable_login_popup() ) {
		return $login_url;
	}

	if ( strpos( $redirect, 'wp-admin' ) ) {
		return $login_url;
	}
	if ( strpos( $redirect, site_url() ) === 0 && filter_var( $redirect, FILTER_VALIDATE_URL ) ) {
		$redirect = str_replace( '?' . snax_get_login_popup_url_variable(), '', $redirect );
		if ( substr( $redirect, -1 ) !== '/' ) {
			$redirect .= '/';
		}
		return $redirect . '?' . snax_get_login_popup_url_variable();
	}
	return home_url() . '/?' . snax_get_login_popup_url_variable();
}

/**
 * Filters the Lost Password URL.

 * @param string $lostpassword_url 	The lost password page URL.
 * @param string $redirect         	The path to redirect to on login.
 */
function snax_lostpassword_url( $lostpassword_url, $redirect ) {
	if ( ! snax_enable_login_popup() ) {
		return $lostpassword_url;
	}

	if ( strpos( $redirect, 'wp-admin' ) ) {
		return $lostpassword_url;
	}
	if ( strpos( $redirect, site_url() ) === 0 && filter_var( $redirect, FILTER_VALIDATE_URL ) ) {
		if ( substr( $redirect, -1 ) !== '/' ) {
			$redirect .= '/';
		}
		return $redirect . '?' . snax_get_login_popup_url_variable() . '=forgot_password';
	}
	return home_url() . '/?' . snax_get_login_popup_url_variable() . '=forgot_password';
}

