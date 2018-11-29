<?php
/**
 * Snax Template Tags
 *
 * @package snax
 * @subpackage TemplateTags
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Hook into the_content to display post elements.
 *
 * @param string $content Post content.
 *
 * @return string
 */
function snax_post_content( $content ) {
	if ( snax_in_custom_loop() || ! is_single() ) {
		return $content;
	}

	if ( false === strpos( $content, '[snax_content]' ) ) {
		$content .= '[snax_content]';
	}

	return $content;
}

/**
 * Return snax content
 *
 * @return string
 */
function snax_content_shortcode() {
	$content = '';

	if ( snax_is_format() ) {
		ob_start();
		snax_get_template_part( 'posts/content', snax_get_format() );
		$content = ob_get_clean();
	}

	return apply_filters( 'snax_content_shortcode_output', $content );
}

/**
 * Render HTML for post items.
 */
function snax_render_post_items() {
	snax_get_template_part( 'posts/items' );
}

/**
 * Render HTML for gallery items.
 */
function snax_render_gallery_items() {
	snax_get_template_part( 'posts/gallery-items' );
}

/**
 * Render HTML for post voiting box
 */
function snax_render_post_voting_box() {
	if ( apply_filters( 'snax_render_post_voting_box', snax_voting_is_enabled() ) ) {
		snax_get_template_part( 'posts/voting-box' );
	}
}

/**
 * Render new item form.
 */
function snax_render_new_item_form() {
	$show = false;

	if ( snax_is_post_open_list() ) {
		$show = true;
	}

	if ( apply_filters( 'snax_render_new_item_form', $show ) ) {
		snax_get_template_part( 'items/form-new' );
	}
}

/**
 * Output post notes
 */
function snax_post_render_notes() {
	snax_get_template_part( 'posts/note' );
}

/**
 * Add post notes at the beginning of post content
 *
 * @param string $content 		Post content.
 *
 * @return string
 */
function snax_post_prepend_notes( $content ) {
	ob_start();
	snax_post_render_notes();
	$note = ob_get_clean();

	$content = $note . $content;

	return $content;
}

/**
 * Whether there are more posts available in the loop
 *
 * @return bool
 */
function snax_user_posts() {

	$have_posts = snax()->posts_query->have_posts();

	// Reset the post data when finished.
	if ( empty( $have_posts ) ) {
		wp_reset_postdata();
	}

	return $have_posts;
}

/**
 * Loads up the current post in the loop
 */
function snax_the_post() {
	snax()->posts_query->the_post();
}

/**
 * Output the pagination count
 */
function snax_posts_pagination_count() {
	echo esc_html( snax_get_posts_pagination_count() );
}

/**
 * Return the pagination count
 *
 * @return string
 */
function snax_get_posts_pagination_count() {
	$query = snax()->posts_query;

	if ( empty( $query ) ) {
		return false;
	}

	// Set pagination values.
	$start_num = intval( ( $query->paged - 1 ) * $query->posts_per_page ) + 1;
	$from_num  = snax_number_format( $start_num );
	$to_num    = snax_number_format( ( $start_num + ( $query->posts_per_page - 1 ) > $query->found_posts ) ? $query->found_posts : $start_num + ( $query->posts_per_page - 1 ) );
	$total_int = (int) ! empty( $query->found_posts ) ? $query->found_posts : $query->post_count;
	$total     = snax_number_format( $total_int );

	// Several topics in a forum with a single page.
	if ( empty( $to_num ) ) {
		$retstr = sprintf( _n( 'Viewing %1$s post', 'Viewing %1$s posts', $total_int, 'snax' ), $total );

		// Several topics in a forum with several pages.
	} else {
		$retstr = sprintf( _n( 'Viewing post %2$s (of %4$s total)', 'Viewing %1$s posts - %2$s through %3$s (of %4$s total)', $total_int, 'snax' ), $query->post_count, $from_num, $to_num, $total );
	}

	// Filter and return.
	return apply_filters( 'snax_get_posts_pagination_count', esc_html( $retstr ) );
}

/**
 * Output pagination links
 */
function snax_posts_pagination_links() {
	echo filter_var( snax_get_posts_pagination_links() );
}

/**
 * Return pagination links
 *
 * @return string
 */
function snax_get_posts_pagination_links() {
	$query = snax()->posts_query;

	if ( empty( $query ) ) {
		return false;
	}

	return apply_filters( 'snax_get_posts_pagination_links', $query->pagination_links );
}



/**
 * Render post origin
 *
 * @since 1.1.0
 */
function snax_render_post_origin() {
	if ( snax_show_post_origin() ) {
		snax_get_template_part( 'posts/origin' );
	}
}

/**
 * Render 'recaption this meme'
 */
function snax_render_meme_recaption() {
	if ( has_term( 'meme', snax_get_snax_format_taxonomy_slug() ) && snax_is_memes_recaption_enabled() ) {
		snax_get_template_part( 'posts/meme-recaption' );
	}

	if ( snax_get_meme_template_post_type() === get_post_type() ) {
		snax_get_template_part( 'posts/meme-recaption' );
	}
}

/**
 * Render 'Show similar memes'
 */
function snax_render_meme_see_similar() {
	if ( has_term( 'meme', snax_get_snax_format_taxonomy_slug() ) ) {
		snax_get_template_part( 'posts/meme-similar' );
	}
	if ( snax_get_meme_template_post_type() === get_post_type() ) {
		snax_get_template_part( 'posts/meme-similar' );
	}
}
