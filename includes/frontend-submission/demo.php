<?php
/**
 * Snax Frontend Submission Demo Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Demo data
 *
 * @param WP $request       Request object.
 */
function snax_set_demo_data( $request ) {
	$demo_mode_enabled  = snax_is_demo_mode();
	$url_var = snax_get_url_var( 'format' );
	$format	            = filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );
	$demo_post_id       = snax_get_demo_post_id( $format );

	if ( snax_is_active_format( $format ) && $demo_mode_enabled && $demo_post_id ) {
		$demo_post = get_post( $demo_post_id );

		/** Common part */

		// Title.
		$title = $demo_post->post_title;

		// Category.
		$category_id = '';
		$categories	 = wp_get_post_categories( $demo_post_id );

		if ( ! empty( $categories ) ) {
			$category_id = (int) $categories[0];
		}

		// Tags.
		$tags 		= '';
		$term_ids 	= array();
		$terms 		= wp_get_post_tags( $demo_post_id );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_ids[] = $term->name;
			}

			$tags = implode( ',', $term_ids );
		}

		// Content.
		$content = $demo_post->post_content;

		// Source.
		$source = '';

		/** Image Format */

		if ( 'image' === $format ) {
			$caption = snax_get_caption_from_content( $content );

			// Source.
			if ( $caption['text'] ) {
				$source = $caption['text'];
			}

			// Set content without caption.
			$content = $caption['content'];
		}

		/** Embed Format */

		if ( 'embed' === $format ) {
			// Set content without embed url.
			$content = snax_strip_embed_url_from_content( $content, $demo_post );
		}

		/** Meme Format */

		if ( 'meme' === $format ) {
			$caption = snax_get_caption_from_content( $content );

			// Source.
			if ( $caption['text'] ) {
				$source = $caption['text'];
			}

			// Set content without caption.
			$content = $caption['content'];
		}

		$request->set_query_var( 'snax_sanitized_field_values', array(
			'title'         => $title,
			'source'        => $source,
			'description'   => $content,
			'format'        => $format,
			'category_id'   => $category_id,
			'tags'          => $tags,
			'legal'         => true,
		) );
	}
}

/**
 * Set up cards query
 *
 * @param string $format           Snax format.
 */
function snax_demo_post( $format ) {
	if ( ! snax_is_demo_mode() ) {
		return;
	}

	if ( ! snax_has_user_cards( $format ) ) {
		snax_get_template_part( 'demo/form', $format );
	}
}

/**
 * Check whether post is defined as demo post
 *
 * @param int $post_id     Post id.
 *
 * @return bool
 */
function snax_is_demo_post( $post_id ) {
	$post 			= get_post( $post_id );
	$formats 		= snax_get_formats();
	$demo_post_ids 	= array();

	foreach ( $formats as $format => $format_data ) {
		$demo_post_id = snax_get_demo_post_id( $format );

		if ( $demo_post_id ) {
			$demo_post_ids[] = (int) $demo_post_id;
		}
	}

	$is = in_array( (int) $post->ID, $demo_post_ids, true );

	return apply_filters( 'snax_is_demo_post', $is, $post );
}
