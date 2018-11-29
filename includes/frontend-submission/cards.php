<?php
/**
 * Snax Frontend Submission Cards Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Check whether user uploaded cards that are not assigned to any list
 *
 * @param string $parent_format     Snax format.
 * @param int    $user_id           User id.
 * @param int    $parent_id         Id of a post cards belong to.
 *
 * @return bool
 */
function snax_has_user_cards( $parent_format, $user_id = 0, $parent_id = 0 ) {
	$user_id = (int) $user_id;

	// If not set, try to get current.
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) ) {
		return false;
	}

	// If not set, try to get from request.
	if ( 0 === $parent_id ) {
		$parent_id = (int) filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );
	}

	$args = array(
		'author'    => $user_id,
	);

	if ( $parent_id ) {
		 $args['post_parent'] = $parent_id;
	}

	$query = snax_get_user_cards_query( $parent_format, $args );

	return apply_filters( 'snax_has_user_cards', $query->have_posts(), $user_id );
}

/**
 * Set up cards query
 *
 * @param string $parent_format     Snax format.
 * @param array  $args              Query args.
 *
 * @return WP_Query
 */
function snax_get_user_cards_query( $parent_format, $args = array() ) {
	global $wp_rewrite;

	$query_args = array(
		'paged'         => snax_get_paged(),
		'max_num_pages' => false,
		'meta_query'    => array(
			array(
				'key'       => '_snax_parent_format',
				'value'     => $parent_format,
				'compare'   => '=',
			),
		),
	);


	// Posts query args.
	$r = snax_get_items_query_args( $query_args );

	$r = wp_parse_args( $args, $r );

	// Make query.
	$query = new WP_Query( $r );

	// Limited the number of pages shown.
	if ( ! empty( $r['max_num_pages'] ) ) {
		$query->max_num_pages = $r['max_num_pages'];
	}

	// If no limit to posts per page, set it to the current post_count.
	if ( -1 === $r['posts_per_page'] ) {
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

		// Unpretty pagination.
		$base = add_query_arg( 'paged', '%#%' );

		// Pagination settings with filter.
		$pagination = apply_filters( 'snax_cards_pagination', array(
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

	snax()->cards_query = $query;

	return $query;
}

/**
 * Return type of the current card in the loop
 *
 * @return string
 */
function snax_get_the_card_type() {
	return snax_get_item_format();
}

/**
 * Return current card title
 *
 * @return string
 */
function snax_get_the_card_title() {
	global $post;

	return $post->post_title;
}

/**
 * Return current card description
 *
 * @return string
 */
function snax_get_the_card_description() {
	if ( is_singular( snax_get_item_post_type() ) ) {
		return '%%SNAX_ITEM_DESCRIPTION%%';
	} else {
		$content = get_the_content();

		$content = snax_strip_embed_url_from_embed_content( $content );

		return $content;
	}
}

/**
 * Return current card embed code
 *
 * @return mixed        Embed code or false if not set.
 */
function snax_get_the_card_embed_code() {
	$p = get_post();

	$url = snax_get_first_url_in_content( $p );

	return $url;
}

/**
 * Return first url in post content
 *
 * @param int|WP_Post $p       Post id or WP_Post object.
 *
 * @return bool|string          False if not found.
 */
function snax_get_first_url_in_content( $p ) {
	$p = get_post( $p );

	if ( ! $p ) {
		return false;
	}

	if ( preg_match( '/https?:\/\/[^\n]+/i', $p->post_content, $matches ) ) {
		return trim( esc_url_raw( $matches[0] ) );
	}

	return false;
}

/**
 * Return current card source
 *
 * @return mixed        Source url or false if not set.
 */
function snax_get_the_card_source() {
	return get_post_meta( get_the_ID(), '_snax_source', true );
}

/**
 * Return current card referral link
 *
 * @return mixed        Url or false if not set.
 */
function snax_get_the_card_ref_link() {
	return get_post_meta( get_the_ID(), '_snax_ref_link', true );
}
