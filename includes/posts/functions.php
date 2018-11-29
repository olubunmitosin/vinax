<?php
/**
 * Snax Posts Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Return the post types that can be used as "Snax Post"
 *
 * @return array
 */
function snax_get_post_supported_post_types() {
	return apply_filters( 'snax_post_supported_post_types', array( 'post' ) );
}

/**
 * Check whether user can submit new items to the post.
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return bool
 */
function snax_is_post_open_for_submission( $post_id = 0 ) {
	$post    = get_post( $post_id );
	$is_open = true;

	$current_datetime = new DateTime( current_time( 'mysql' ) );
	$start_date       = snax_get_post_submission_start_date( $post );
	$end_date         = snax_get_post_submission_end_date( $post );

	if ( 'none' === get_post_meta( $post_id, '_snax_post_submission', true ) ) {
		$is_open = false;
	}

	if ( ! empty( $start_date ) ) {
		$start_datetime = new DateTime( $start_date );

		// Closed, start date is in the future.
		if ( $start_datetime > $current_datetime ) {
			$is_open = false;
		}
	}

	if ( ! empty( $end_date ) ) {
		$end_datetime = new DateTime( $end_date );

		// Closed, end date passed.
		if ( $end_datetime < $current_datetime ) {
			$is_open = false;
		}
	}

	return apply_filters( 'snax_is_post_open_for_submission', $is_open, $post );
}

/**
 * Allow admins and authors submitting new items to closed list.
 *
 * @param bool    $is_open		Current state.
 * @param WP_Post $post			Current post.
 *
 * @return bool
 */
function snax_allow_submitting_to_closed_list( $is_open, $post ) {
	if ( current_user_can( 'administrator' ) ) {
		return true;
	}

	// Is post author?
	if ( (int) get_current_user_id() === (int) $post->post_author ) {
		return true;
	}

	return $is_open;
}

/**
 * Always render new item form for admins
 *
 * @param bool $show  Whether to show the form.
 * @return bool
 */
function snax_render_new_item_form_for_admins( $show ) {
	if ( current_user_can( 'administrator' ) ) {
		return true;
	}
	return $show;
}

/**
 * Check whether new items submission is forbidden for the post.
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return bool
 */
function snax_is_post_closed_for_submission( $post_id = 0 ) {
	return ! snax_is_post_open_for_submission( $post_id );
}

/**
 * Check whether user can vote for items.
 *
 * @param int|WP_Post $post_id Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return bool
 */
function snax_is_post_open_for_voting( $post_id = 0 ) {
	$post    = get_post( $post_id );
	$is_open = true;

	$current_datetime = new DateTime( current_time( 'mysql' ) );
	$start_date       = snax_get_post_voting_start_date( $post );
	$end_date         = snax_get_post_voting_end_date( $post );

	if ( 'none' === get_post_meta( $post_id, '_snax_post_voting', true ) ) {
		$is_open = false;
	}

	if ( ! empty( $start_date ) ) {
		$start_datetime = new DateTime( $start_date );

		// Closed, start date is in the future.
		if ( $start_datetime > $current_datetime ) {
			$is_open = false;
		}
	}

	if ( ! empty( $end_date ) ) {
		$end_datetime = new DateTime( $end_date );

		// Closed, end date passed.
		if ( $end_datetime < $current_datetime ) {
			$is_open = false;
		}
	}

	return apply_filters( 'snax_is_post_open_for_voting', $is_open, $post );
}

/**
 * Disable voting option for post
 *
 * @param bool $enabled          Current state.
 * @param int  $post_id          Post object.
 *
 * @return bool
 */
function snax_post_disable_voting_actions($enabled, $post_id ) {
	// Snax item.
	if ( snax_is_item( $post_id ) ) {
		$item_parent = wp_get_post_parent_id( $post_id );

		if ( snax_is_format( 'list', $item_parent ) && ! snax_is_post_open_for_voting( $item_parent ) ) {
			$enabled = false;
		}
	}

	return $enabled;
}

/**
 * Return the total submission count of the $post
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int                          Post submission count.
 */
function snax_get_post_submission_count( $post_id = 0 ) {
	$post = get_post( $post_id );

	$count = (int) get_post_meta( $post->ID, '_snax_submission_count', true );

	return (int) apply_filters( 'snax_get_post_submission_count', $count, $post->ID );
}

/**
 * Add custom CSS classes
 *
 * @param array       $classes          An array of post classes.
 * @param array       $class            An array of additional classes added to the post.
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return array
 */
function snax_add_post_class( $classes, $class, $post_id ) {
	if ( snax_is_format( 'list', $post_id ) ) {
		$classes[] = 'snax-list';
	}

	return $classes;
}

/**
 * Hook into the_title
 *
 * @param string      $title                    Current title value.
 * @param int|WP_Post $post_id                  Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_add_post_info_to_title( $title, $post_id = null ) {
	if ( ! ( snax_is_format( 'list', $post_id ) || snax_is_format( 'gallery', $post_id ) ) ) {
		return $title;
	}

	// Replace placeholder for both lists and galleries.
	$placeholder_exists = snax_title_placeholder_exists( $title );

	// Replace the placeholder on a single post and inside loops and return (no further action required).
	if ( $placeholder_exists ) {
		$title = snax_replace_title_placeholder( $title, $post_id );
	}

	// Outside loops, like menu item titles, etc., we don't want to append the info.
	if ( ! in_the_loop() && ! snax_in_custom_loop() ) {
		return $title;
	}

	// The same for a single post page.
	if ( is_single( $post_id ) ) {
		return $title;
	}

	// But number of submissions and Open List badge should be applied only for Open Lists. Galleries have no submissions.
	if ( snax_is_post_open_list( $post_id ) ) {
		if ( snax_show_open_list_in_title() ) {
			$title .= __( ' (Open list)', 'snax' );
		}

		if ( ! $placeholder_exists && snax_show_item_count_in_title() ) {
			$submission_count = snax_get_post_submission_count( $post_id );

			$title .= ' ' . sprintf( _n( '(%s submission)', '(%s submissions)', $submission_count, 'snax' ), $submission_count );
		}
	}

	return $title;
}

/**
 * Remove "items" placeholder from page slug
 *
 * @param array  $permalink      Array of permalink and slug.
 * @param int    $post_id        Post ID or post object.
 * @param string $title          Optional. Title. Default null.
 * @param string $name           Optional. Name. Default null.
 *
 * @return array                    Array with two entries of type string.
 */
function snax_remove_placeholder_from_slug_on_edit( $permalink, $post_id, $title, $name ) {
	remove_filter( 'get_sample_permalink', 'snax_remove_placeholder_from_slug_on_edit', 10 );

	$title = str_replace( snax_get_post_title_item_count_placeholder(), '', $title );

	$permalink = get_sample_permalink( $post_id, $title, $name );

	add_filter( 'get_sample_permalink', 'snax_remove_placeholder_from_slug_on_edit', 10, 5 );

	return $permalink;
}

/**
 * Remove 'items' placeholder from slug
 *
 * @param int     $post_id      The post ID.
 * @param WP_Post $post         The post object.
 */
function snax_remove_placeholder_from_slug_on_create( $post_id, $post ) {
	// Allow statuses: publish, draft, future.
	if ( 'post' !== $post->post_type || 'auto-draft' === $post->post_status ) {
		return;
	}

	// Only change slug when the post is created (both dates are equal).
	if ( $post->post_date_gmt !== $post->post_modified_gmt ) {
		return;
	}

	// Use title, since $post->post_name might have unique numbers added.
	$title = $post->post_title;

	$title = str_replace( snax_get_post_title_item_count_placeholder(), '', $title );

	$new_slug = sanitize_title( $title, $post_id );

	remove_action( 'save_post', 'snax_remove_placeholder_from_slug_on_create', 10, 3 );

	// Update the post slug (WP handles unique post slug).
	wp_update_post( array(
		'ID'        => $post_id,
		'post_name' => $new_slug,
	) );

	add_action( 'save_post', 'snax_remove_placeholder_from_slug_on_create', 10, 3 );
}

/**
 * Replace 'items' placeholder from title
 *
 * @param string $title         The post title.
 *
 * @return string
 */
function snax_post_title_short_circuit( $title ) {
	// Plugins like Yoast SEO use this hook.
	if ( ! empty( $title ) && ( snax_is_format( 'list' ) || snax_is_format( 'gallery' ) ) ) {
		$title = snax_replace_title_placeholder( $title );
	}

	if ( snax_is_item() ) {
		$item_title = get_the_title();
		$sep = apply_filters( 'document_title_separator', '-' );

		// If title starts with separator, means item title is empty.
		if ( 0 === strpos( $title, $sep ) ) {
			$title = $item_title . '' . ' ' . $title;
		}
	}

	return $title;
}

/**
 * Replace 'items' placeholder from title
 *
 * @param array $title_parts        The post title parts.
 *
 * @return array
 */
function snax_post_title_parts( $title_parts ) {
	if ( snax_is_format( 'list' ) || snax_is_format( 'gallery' ) ) {
		$title_parts['title'] = snax_replace_title_placeholder( $title_parts['title'] );
	}

	return $title_parts;
}

/**
 * Replace 'items' placeholder from title
 *
 * @param string $title         Post title.
 * @param int    $post_id       Post id.
 *
 * @return string
 */
function snax_replace_title_placeholder( $title, $post_id = 0 ) {
	if ( ! snax_title_placeholder_exists( $title ) ) {
		return $title;
	}

	$submission_count = snax_get_post_submission_count( $post_id );

	// Replace the placeholder.
	$placeholder = snax_get_post_title_item_count_placeholder();

	$title = str_replace( $placeholder, $submission_count, $title );

	return $title;
}

/**
 * Check whether 'items' placeholder exists in a title
 *
 * @param string $title         Post title.
 *
 * @return bool
 */
function snax_title_placeholder_exists( $title ) {
	return ( false !== strpos( $title, snax_get_post_title_item_count_placeholder() ) );
}

/**
 * Clean up post attachments, items when removing post (from admin panel, not frontend)
 *
 * @param int $post_id          Post id.
 */
function snax_remove_post_dependencies( $post_id ) {
	if ( ! is_admin() ) {
		return;
	}

	$post = get_post( $post_id );

	if ( snax_is_item( $post ) ) {
		// Is orphan (temporary item)?
		if ( 0 === (int) $post->post_parent ) {
			return;
		}

		$format = snax_get_item_format( $post );
	} else {
		$format = snax_get_format( $post );
	}

	switch ( $format ) {
		// For snax item image (part of gallery or list) or post image.
		case 'image':
			$media_id     = get_post_thumbnail_id( $post->ID );
			$delete_media = apply_filters( 'snax_delete_media', true, $media_id );

			if ( $delete_media ) {
				// Delete permanently, not move it to the trash.
				$force_delete_media = apply_filters( 'snax_force_delete_media', true, $media_id );

				// Delete media.
				//wp_delete_attachment( $media_id, $force_delete_media );
			}
			break;

		case 'gallery':
		case 'list':
			$post_items = snax_get_items( $post );

			// Move items to Trash.
			// When user will remove them permanently the above rule (for image format) will be used to clean it up.
			foreach ( $post_items as $post_item ) {
				$post_arr = array(
					'ID'          => $post_item->ID,
					'post_status' => 'trash',
				);

				wp_update_post( $post_arr );
			}
			break;
	}
}

/**
 * Generate post pagination using built-in WP page links
 *
 * @param array    $posts           Array of posts.
 * @param WP_Query $wp_query        WP Query.
 *
 * @return array
 */
function snax_post_pagination( $posts, $wp_query ) {
	/**
	 * Check if query is an instance of WP_Query.
	 * Some plugins, like BuddyPress may change it.
	 */
	if ( ! ( $wp_query instanceof WP_Query ) ) {
		return $posts;
	}

	// Apply only for the_content on a single post.
	if ( ! ( $wp_query->is_main_query() && $wp_query->is_singular() ) ) {
		return $posts;
	}

	foreach ( $posts as $post ) {
		$post_format = snax_get_format( $post );

		if ( ! in_array( $post_format, array( 'list', 'gallery' ), true ) ) {
			continue;
		}

		$pages = snax_get_post_page_count( $post );

		if ( $pages < 2 ) {
			continue;
		}

		// WP skips <!--nextpage--> quick tag if it's placed at the beggining of a post.
		// So if post content is empty we need to add one extra quick tag as a workaround.
		if ( empty( $post->post_content ) ) {
			$post->post_content .= '<!--nextpage-->';
		}

		// The <!--nextpage--> tag is a divider between two pages. Number of dividers = pages - 1.
		$post->post_content .= str_repeat( '<!--nextpage-->', $pages - 1 );
	}

	return $posts;
}

/**
 * Return number of pages the post is divided into
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return int
 */
function snax_get_post_page_count( $post_id = 0 ) {
	$post = get_post( $post_id );

	$item_count     = snax_get_post_submission_count( $post );
	$items_per_page = snax_get_items_per_page( $post );

	// All items on a single page.
	$page_count = 0;

	if ( $items_per_page > 0 ) {
		$page_count = ceil( $item_count / $items_per_page );
	}

	return $page_count;
}

/**
 * Check whether a post is an open list
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_is_post_open_list( $post_id = 0 ) {
	$post = get_post( $post_id );

	$is_list 		= snax_is_format( 'list', $post );
	$is_open_list	= 'none' !== get_post_meta( $post->ID, '_snax_post_submission', true );

	return $is_list && $is_open_list;
}

/**
 * Return date after which the post is opened
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_get_post_submission_start_date( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (string) get_post_meta( $post->ID, '_snax_post_submission_start_date', true );
}

/**
 * Return date after which the post will be closed
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_get_post_submission_end_date( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (string) get_post_meta( $post->ID, '_snax_post_submission_end_date', true );
}

/**
 * Return list close limit
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_get_post_submission_close_limit( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (string) get_post_meta( $post->ID, '_snax_post_submission_close_limit', true );
}

/**
 * Check whether a post is a ranked list
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_is_post_ranked_list( $post_id = 0 ) {
	$post = get_post( $post_id );

	return 'none' !== get_post_meta( $post->ID, '_snax_post_voting', true );
}

/**
 * Return date after which the post is opened for voting
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_get_post_voting_start_date( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (string) get_post_meta( $post->ID, '_snax_post_voting_start_date', true );
}

/**
 * Return date after which the post will be closed for voting
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 *
 * @return string
 */
function snax_get_post_voting_end_date( $post_id = 0 ) {
	$post = get_post( $post_id );

	return (string) get_post_meta( $post->ID, '_snax_post_voting_end_date', true );
}

/**
 * Handle post actions
 */
function snax_handle_post_actions() {
	if ( ! current_user_can( 'administrator' ) ) {
		return;
	}

	$post_id = filter_input( INPUT_GET, 'snax_post', FILTER_SANITIZE_NUMBER_INT );
	$action  = filter_input( INPUT_GET, 'snax_action', FILTER_SANITIZE_STRING );

	if ( ! empty( $post_id ) && ! empty( $action ) ) {
		switch ( $action ) {
			case 'reload_meta':
				snax_post_reload_meta( $post_id );
				break;
		}
	}
}

/**
 * Reload post meta data
 *
 * @param int|WP_Post $post_id              Optional. Post ID or WP_Post object. Default is global `$post`.
 */
function snax_post_reload_meta( $post_id = 0 ) {
	$post = get_post( $post_id );

	$items = snax_get_items( $post->ID );

	// Update post modification date.
	wp_update_post( array(
		'ID'                => $post->ID,
		'post_modified'     => current_time( 'mysql' ),
		'post_modified_gtm' => current_time( 'mysql', 1 ),
	) );

	// Reload post items counter.
	update_post_meta( $post->ID, '_snax_submission_count', count( $items ) );

	// Update post item parent format.
	$post_format = snax_get_post_format( $post->ID );

	foreach ( $items as $item ) {
		update_post_meta( $item->ID, '_snax_parent_format', $post_format );
	}
}

/**
 * Allow contributing to the post
 *
 * @param int $post_id          Post id.
 * @param array $config             Config.
 */
function snax_open_post_for_contribution( $post_id = 0, $config = array() ) {
	$post = get_post( $post_id );

	snax_set_post_config( $post, $config );
}

/**
 * Close list automatically after reached X approved submissions
 *
 * @param int $submission_count             Number of post approved submissions.
 * @param int $post_id                      Post id.
 * @param int $difference                   Positive number for adding, negative for deleting.
 */
function snax_close_post_submission( $submission_count, $post_id, $difference ) {
	// Skip if item was removed.
	if ( $difference < 0 ) {
		return;
	}

	if ( ! snax_is_post_open_list( $post_id ) || snax_is_post_closed_for_submission( $post_id ) ) {
		return;
	}

	$close_limit = (int) snax_get_post_submission_close_limit( $post_id );

	// Skip if limit not set.
	if ( $close_limit <= 0 ) {
		return;
	}

	if ( $submission_count >= $close_limit ) {
		snax_close_post_for_contribution( $post_id );
	}
}

/**
 * Disallow contributing to the post
 *
 * @param int $post_id          Post id.
 */
function snax_close_post_for_contribution( $post_id = 0 ) {
	$post = get_post( $post_id );

	$config = snax_get_post_config( $post );

	$timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );

	$config['submission_end_date'] = date_i18n( $timezone_format );

	snax_set_post_config( $post, $config );
}

/**
 * Set post config
 *
 * @param int   $post_id            Post id.
 * @param array $config             Config.
 */
function snax_set_post_config( $post_id = 0, $config = array() ) {
	$post = get_post( $post_id );

	$defaults = snax_get_post_default_config( $post );
	$config   = wp_parse_args( $config, $defaults );

	$config_key = snax_get_post_config_key();

	update_post_meta( $post->ID, $config_key, $config );

	// Store in separate meta key, for easier access.
	update_post_meta( $post->ID, '_snax_post_submission', $config['submission'] );
	update_post_meta( $post->ID, '_snax_post_submission_start_date', $config['submission_start_date'] );
	update_post_meta( $post->ID, '_snax_post_submission_end_date', $config['submission_end_date'] );
	update_post_meta( $post->ID, '_snax_post_submission_close_limit', $config['submission_close_limit'] );
	update_post_meta( $post->ID, '_snax_post_voting', $config['voting'] );
	update_post_meta( $post->ID, '_snax_post_voting_start_date', $config['voting_start_date'] );
	update_post_meta( $post->ID, '_snax_post_voting_end_date', $config['voting_end_date'] );
	update_post_meta( $post->ID, '_snax_post_items_per_page', $config['items_per_page'] );

	// Set modified data, but only for the first call. Then this date will be modified only when post items changed.
	$modified_date = get_post_meta( $post->ID, '_snax_post_modified_date', true );

	if ( empty( $modified_date ) ) {
		update_post_meta( $post->ID, '_snax_post_modified_date', $post->post_modified );
	}
}

/**
 * Get post condig
 *
 * @param int $post_id          Post id.
 *
 * @return array
 */
function snax_get_post_config( $post_id = 0 ) {
	$post       = get_post( $post_id );
	$config_key = snax_get_post_config_key();

	$defaults = snax_get_post_default_config( $post );

	$values = get_post_meta( $post->ID, $config_key, true );
	$values = wp_parse_args( $values, $defaults );

	return $values;
}

/**
 * Get default post config
 *
 * @param int $post_id          Post id.
 *
 * @return array
 */
function snax_get_post_default_config( $post_id = 0 ) {
	$post = get_post( $post_id );

	return apply_filters( 'snax_post_default_config', array(
		'submission' 			=> 'standard',
		'submission_start_date' => $post->post_date,
		'submission_end_date'   => '',
		'submission_close_limit'=> '',
		'voting'     			=> 'standard',
		'voting_start_date'     => $post->post_date,
		'voting_end_date'       => '',
		'override_forms'        => 'none',
		'forms'                 => snax_get_active_item_forms_ids(),
		'items_per_page'        => '',
	) );
}

/**
 * Get post config option name
 *
 * @return string
 */
function snax_get_post_config_key() {
	return apply_filters( 'snax_post_config_key', '_snax_post_config' );
}

/**
 * Check whether post was submitted
 *
 * @return bool
 */
function snax_post_submitted() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$post = get_post();

	if ( null === $post ) {
		return false;
	}

	$post_author_id  = (int) $post->post_author;
	$current_user_id = (int) get_current_user_id();

	if ( $current_user_id !== $post_author_id ) {
		return false;
	}

	$url_var = snax_get_url_var( 'post_submission' );

	$submission = filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );

	return ! empty( $submission );
}

/**
 * Return approved post status name
 *
 * @return string
 */
function snax_get_post_approved_status() {
	return apply_filters( 'snax_post_approved_status', 'publish' );
}

/**
 * Return pending post status name
 *
 * @return string
 */
function snax_get_post_pending_status() {
	return apply_filters( 'snax_post_pending_status', 'pending' );
}

/**
 * Return draft post status name
 *
 * @return string
 */
function snax_get_post_draft_status() {
	return apply_filters( 'snax_post_draft_status', 'draft' );
}

/**
 * Check whether post is in 'pending' state
 *
 * @param int $post_id          Post id.
 *
 * @return bool
 */
function snax_is_post_pending_for_review( $post_id = 0 ) {
	$post = get_post( $post_id );

	return snax_get_post_pending_status() === $post->post_status;
}

/**
 * Check whether user has approved post
 *
 * @param int $user_id          User id.
 *
 * @return bool
 */
function snax_has_user_approved_posts( $user_id = 0 ) {
	$has = snax_has_user_posts( snax_get_post_approved_status(), $user_id );

	return apply_filters( 'snax_has_user_approved_posts', $has, $user_id );
}

/**
 * Check whether user has pending posts
 *
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_has_user_pending_posts( $user_id = 0 ) {
	$has = snax_has_user_posts( snax_get_post_pending_status(), $user_id );

	return apply_filters( 'snax_has_user_pending_posts', $has, $user_id );
}

/**
 * Check whether user has draft posts
 *
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_has_user_draft_posts( $user_id = 0 ) {
	$has = snax_has_user_posts( snax_get_post_draft_status(), $user_id );

	return apply_filters( 'snax_has_user_draft_posts', $has, $user_id );
}

/**
 * Check whether user has posts
 *
 * @param int $status           Post status.
 * @param int $user_id          User id.
 *
 * @return bool
 */
function snax_has_user_posts( $status, $user_id = 0 ) {
	$user_id = (int) $user_id;

	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	$query = snax_get_posts_query( array(
		'author'      => $user_id,
		'post_status' => $status,
	) );

	return apply_filters( 'snax_has_user_posts', $query->have_posts(), $user_id );
}

/**
 * Set up posts query
 *
 * @param array $args           WP Query args.
 *
 * @return WP_Query
 */
function snax_get_posts_query( $args = array() ) {
	global $wp_rewrite;

	// Posts query args.
	$r = snax_get_posts_query_args( array(
		'posts_per_page' => snax_get_posts_per_page(),
		'paged'          => snax_get_paged(),
		'max_num_pages'  => false,
	) );

	$r = wp_parse_args( $args, $r );

	// Make query.
	$query = new WP_Query( $r );

	// Limited the number of pages shown.
	if ( ! empty( $r['max_num_pages'] ) ) {
		$query->max_num_pages = $r['max_num_pages'];
	}

	// If no limit to posts per page, set it to the current post_count.
	if ( - 1 === $r['posts_per_page'] ) {
		$r['posts_per_page'] = $query->post_count;
	}

	// Add pagination values to query object.
	$query->posts_per_page = $r['posts_per_page'];
	$query->paged          = $r['paged'];

	// Only add pagination if query returned results.
	if ( ( (int) $query->post_count || (int) $query->found_posts ) && (int) $query->posts_per_page ) {

		// Limit the number of topics shown based on maximum allowed pages.
		if ( ( ! empty( $r['max_num_pages'] ) ) && $query->found_posts > $query->max_num_pages * $query->post_count ) {
			$query->found_posts = $query->max_num_pages * $query->post_count;
		}

		$base = add_query_arg( 'paged', '%#%' );

		$base = apply_filters( 'snax_posts_pagination_base', $base, $r );

		// Pagination settings with filter.
		$pagination = apply_filters( 'snax_posts_pagination', array(
			'base'      => $base,
			'format'    => '',
			'total'     => $r['posts_per_page'] === $query->found_posts ? 1 : ceil( (int) $query->found_posts / (int) $r['posts_per_page'] ),
			'current'   => (int) $query->paged,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'mid_size'  => 1,
		) );

		// Add pagination to query object.
		$query->pagination_links = paginate_links( $pagination );

		// Remove first page from pagination.
		$query->pagination_links = str_replace( $wp_rewrite->pagination_base . "/1/'", "'", $query->pagination_links );
	}

	snax()->posts_query = $query;

	return $query;
}

/**
 * Returns default posts query args
 *
 * @param array $args Optional.
 *
 * @return array
 */
function snax_get_posts_query_args( $args = array() ) {
	$defaults = array(
		'post_type'      => 'post',
		'tax_query'		 => array(
			array(
				'taxonomy' 	=> snax_get_snax_format_taxonomy_slug(),
				'field' 	=> 'slug',
				'operator' 	=> 'EXISTS',
			),
		),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'posts_per_page' => - 1,
	);

	$args = wp_parse_args( $args, $defaults );

	return apply_filters( 'snax_posts_query_args', $args );
}

/**
 * Return number of items to display on a single post page, per post setting.
 *
 * @param int $post_id              Post id.
 *
 * @return int
 */
function snax_get_items_per_page( $post_id = 0 ) {
	$post = get_post( $post_id );

	$items_per_page = get_post_meta( $post->ID, '_snax_post_items_per_page', true );

	// Not set?
	if ( empty( $items_per_page ) ) {
		$items_per_page = snax_get_global_items_per_page();
	}

	return (int) $items_per_page;
}

/**
 * Listen on post status change
 *
 * @param string  $new_status           New status.
 * @param string  $old_status           Old status.
 * @param WP_Post $post                 Affected post.
 */
function snax_post_status_changed( $new_status, $old_status, $post ) {
	if ( 'new' === $old_status ) {
		return;
	}

	$approved_status = snax_get_post_approved_status();
	$pending_status = snax_get_post_pending_status();

	if ( $approved_status !== $old_status && $approved_status === $new_status ) {
		snax_post_approved( $post );
	}

	if ( ( $approved_status === $old_status || $pending_status === $old_status ) && 'trash' === $new_status ) {
		snax_post_rejected( $post );
	}
}

/**
 * Fires when post is approved.
 *
 * @param WP_Post $post		Post object.
 */
function snax_post_approved( $post ) {
	// Store info about action.
	update_post_meta( $post->ID, 'snax_approved_by', get_current_user_id() );
	update_post_meta( $post->ID, 'snax_approval_data', current_time( 'mysql' ) );

	do_action( 'snax_post_approved', $post );
}

function snax_post_items_update_status( $post ) {
	$items = snax_get_items( $post );

	foreach( $items as $item ) {
		if ( $item->post_status !== $post->post_status ) {
			wp_update_post( array(
				'ID'            => $item->ID,
				'post_status'   => $post->post_status,
			) );
		}
	}
}

/**
 * Fires when post is rejected.
 *
 * @param WP_Post $post		Post object.
 */
function snax_post_rejected( $post ) {
	// Store info about action.
	update_post_meta( $post->ID, 'snax_rejected_by', get_current_user_id() );
	update_post_meta( $post->ID, 'snax_rejection_data', current_time( 'mysql' ) );

	do_action( 'snax_post_rejected', $post );
}

/**
 * Update post submission count when item belongs to that post was approved
 *
 * @param WP_Post $item             Snax item.
 */
function snax_post_update_submission_count( $item ) {
	$post = get_post( $item->post_parent );

	if ( snax_is_format( 'list', $post ) ) {
		snax_bump_post_submission_count( $post );
	}
}

/**
 * Sanitize post title
 *
 * @param string $val           Title value.
 *
 * @return string
 */
function snax_sanitize_post_title( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, snax_get_post_title_max_length() );

	return $val;
}

/**
 * Sanitize post description
 *
 * @param string $val           Description value.
 *
 * @return string
 */
function snax_sanitize_post_description( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, 4 * snax_get_post_description_max_length() ); // 4x - Froala doesn't count images/embed.

	return $val;
}

/**
 * Sanitize post content
 *
 * @param string $val           Content value.
 *
 * @return string
 */
function snax_sanitize_post_content( $val ) {
	$val = trim( $val );
	$val = mb_substr( $val, 0, 4 * snax_get_post_content_max_length() ); // 4x - Froala doesn't count images/embed.

	return $val;
}

/**
 * Return uploaded form field values
 *
 * @param string $field_name        Optional. Field name.
 *
 * @return array                    All field values or $field_name value.
 */
function snax_get_field_values( $field_name = '' ) {
	$field_values = array(
		'title'         	=> '',
		'source'        	=> '',
		'description'   	=> '',
		'category_id'   	=> 0,
		'tags'          	=> '',
		'list_voting'       => '',
		'list_submissions'	=> '',
		'legal'         	=> false,
		'meme-top-text' 	=> __( 'Top text...', 'snax' ),
		'meme-bottom-text' 	=> __( 'Bottom text...', 'snax' ),
	);

	$sanitized_field_values = get_query_var( 'snax_sanitized_field_values' );

	if ( ! empty( $sanitized_field_values ) ) {
		$field_values = wp_parse_args( $sanitized_field_values, $field_values );
	}

	if ( $field_name ) {
		return isset( $field_values[ $field_name ] ) ? $field_values[ $field_name ] : '';
	}

	return $field_values;
}

/**
 * Return list voting option value
 *
 * @return bool
 */
function snax_get_list_voting_value() {
	if ( '' === snax_get_field_values( 'list_voting' ) ) {
		$list_type      = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
		$list_type 		= apply_filters( 'snax_input_get_list_type', $list_type );
		$value = 'classic' !== $list_type;
	} else {
		$value = snax_get_field_values( 'list_voting' );
	}

	return $value;
}

/**
 * Return list submissions option value
 *
 * @return bool
 */
function snax_get_list_submission_value() {
	if ( '' === snax_get_field_values( 'list_submission' ) ) {
		$list_type      = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
		$list_type 		= apply_filters( 'snax_input_get_list_type', $list_type );
		$value = is_null( $list_type );
	} else {
		$value = snax_get_field_values( 'list_submission' );
	}

	return $value;
}

/**
 * Return uploaded form field errors
 *
 * @param string $field_name        Optional. Field name.
 *
 * @return array                    All field values or $field_name value.
 */
function snax_get_field_errors( $field_name = '' ) {
	$form_errors = get_query_var( 'snax_errors' );

	if ( $field_name ) {
		return isset( $form_errors[ $field_name ] ) ? $form_errors[ $field_name ]->get_error_message() : '';
	}

	return $form_errors;
}

/**
 * Check whether the $field_name has assigned error
 *
 * @param string $field_name        Field name.
 *
 * @return bool
 */
function snax_has_field_errors( $field_name ) {
	$errors = snax_get_field_errors( $field_name );

	return ! empty( $errors );
}

/**
 * Return list of allowed, during post submission, categories
 *
 * @param string $format        Snax post format.
 *
 * @return string
 */
function snax_get_post_included_categories( $format ) {
	$categories = '';

	$func_name = sprintf( 'snax_%s_get_category_whitelist', $format );

	$whitelist = array( '' => '' );

	if ( is_callable( $func_name ) ) {
		$whitelist = call_user_func( $func_name );
	}

	if ( ! in_array( '', $whitelist, true ) ) {
		foreach ( $whitelist as $slug ) {
			$category = get_category_by_slug( $slug );

			if ( false !== $category ) {
				$categories .= $category->term_id . ',';
			}
		}
	}

	return apply_filters( 'snax_post_included_categories', $categories );
}

/**
 * Return list of forbidden, during post submission, categories
 *
 * @return string
 */
function snax_get_post_excluded_categories() {
	$categories = '';

	// Exclude the Uncategorized category.
	$uncategorized = get_category_by_slug( 'uncategorized' );

	if ( false !== $uncategorized ) {
		$categories .= $uncategorized->term_id;
	}

	return apply_filters( 'snax_post_excluded_categories', $categories );
}


/**
 * Whether or not to show the post origin.
 *
 * @return bool
 *
 * @since 1.1.0
 */
function snax_show_post_origin() {
	$bool = snax_is_format();
	if ( ! snax_show_origin() ){
		$bool = false;
	}

	return apply_filters( 'snax_show_post_origin', $bool );
}

/**
 * Hide element if post is not published.
 *
 * @param bool $show		Current state.
 * @return bool
 */
function snax_hide_for_pending_posts( $show ) {
	if ( snax_is_post_pending_for_review() ) {
		$show = false;
	}

	return $show;
}

/**
 * Render post scripts
 *
 * @param string $format		Snax post format.
 */
function snax_post_scripts( $format ) {
	if ( ! in_array( $format, array( 'list', 'gallery' ), true ) ) {
		return;
	}

	?>
	<script type="text/javascript">
		(function () {
			if ( typeof window.snax === 'undefined' ) {
				window.snax = {};
			}

			var ctx = window.snax;

			ctx.currentUserId = <?php echo intval( get_current_user_id() ); ?>;

			ctx.newItemData = {
				'authorId': ctx.currentUserId,
				'postId': <?php echo intval( get_the_ID() ); ?>
			};
		})();
	</script>
	<?php
}

/**
 * Render referral link
 */
function snax_render_post_referral_link() {
	if ( snax_is_format() ) {
		echo snax_capture_post_referral_link();
	}
}

/**
 * Return referral link for a post
 *
 * @param int $post_id      Optional. Post id.
 *
 * @return string
 */
function snax_capture_post_referral_link( $post_id = null ) {
	$link = '';
	$post = get_post( $post_id );

	$ref_link = get_post_meta( $post->ID, '_snax_ref_link', true );

	if ( ! empty( $ref_link ) ) {
		$link = sprintf(
			'<form class="snax-post-referral-form" action="%s" method="get" onclick="window.open(this.action); return false;"><button type="submit">%s</button></form>',
			esc_url_raw( $ref_link ),
			esc_html__( 'Buy now', 'snax' )
		);
	}

	return apply_filters( 'snax_post_referral_link_html', $link, $post->ID );
}
