<?php
/**
 * Snax Frontend Submission Edit Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Edit data
 *
 * @param WP $request       Request object.
 */
function snax_set_edit_data( $request ) {
	$format	 = filter_input( INPUT_GET, snax_get_url_var( 'format' ), FILTER_SANITIZE_STRING );
	$post_id = filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );

	if ( snax_is_active_format( $format ) && $post_id ) {
		$post = get_post( $post_id );

		/** Common part */

		// Title.
		$title = $post->post_title;

		// Category.
		$category_id = '';
		$categories	 = wp_get_post_categories( $post_id );

		if ( ! empty( $categories ) ) {
			$category_id = (int) $categories[0];
		}

		// Tags.
		$tags 		= '';
		$term_ids 	= array();
		$terms 		= wp_get_post_tags( $post_id );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$term_ids[] = $term->name;
			}

			$tags = implode( ',', $term_ids );
		}

		// Content.
		$content = $post->post_content;

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
			$content = snax_strip_embed_url_from_content( $content, $post );
		}

		/** Text Format */

		if ( 'text' === $format ) {
			$content = snax_conver_captions_into_froala_images( $content );
			$content = snax_conver_urls_into_froala_embeds( $content );
		}

		$content = apply_filters( 'snax_content_to_edit', $content, $format );

		/** Lists */

		$list_submission = 'standard' === get_post_meta( $post->ID, '_snax_post_submission', true );
		$list_voting 	 = 'standard' === get_post_meta( $post->ID, '_snax_post_voting', true );
		$ref_link 	     = get_post_meta( $post->ID, '_snax_ref_link', true );

		$request->set_query_var( 'snax_sanitized_field_values', array(
			'title'         	=> $title,
			'source'        	=> $source,
			'ref_link'        	=> $ref_link,
			'description'   	=> $content,
			'format'        	=> $format,
			'category_id'   	=> $category_id,
			'tags'          	=> $tags,
			'legal'         	=> true,
			'list_voting'       => $list_voting,
			'list_submission'	=> $list_submission,
		) );
	}
}
