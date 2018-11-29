<?php
/**
 * Snax Item Ajax Functions
 *
 * @package snax
 * @subpackage Ajax
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Add new image item to existing post
 */
function snax_ajax_add_media_item() {
	check_ajax_referer( 'snax-add-media-item', 'security' );

	/** Required fields */

	// Sanitize media id.
	$media_id = (int) filter_input( INPUT_POST, 'snax_media_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $media_id ) {
		snax_ajax_response_error( 'Item uploaded image id not set!' );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author (submitter) id not set!' );
		exit;
	}

	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
	}

	// Legal.
	$legal = filter_input( INPUT_POST, 'snax_legal', FILTER_SANITIZE_STRING );

	if ( empty( $legal ) && snax_legal_agreement_required() ) {
		snax_ajax_response_error( 'Legal agreement not accepted!' );
		exit;
	}

	/** Options fields */

	// Sanitize post id.
	$post_id = (int) filter_input( INPUT_POST, 'snax_post_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	// Sanitize title.
	$title = snax_sanitize_item_title( filter_input( INPUT_POST, 'snax_title', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.

	// Sanitize source.
	$source = snax_sanitize_item_source( filter_input( INPUT_POST, 'snax_source', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.

	// Sanitize source.
	$ref_link = snax_sanitize_item_ref_link( filter_input( INPUT_POST, 'snax_ref_link', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.

	// Sanitize description.
	if ( snax_froala_for_list_items() ) {
		$description = snax_sanitize_item_content( strip_tags( filter_input( INPUT_POST, 'snax_description' ), '<p><a><em><strong>' ) );
	} else {
		$description = snax_sanitize_item_content( filter_input( INPUT_POST, 'snax_description', FILTER_SANITIZE_STRING ) );
	}

	// Sanitize status.
	$status = filter_input( INPUT_POST, 'snax_status', FILTER_SANITIZE_STRING );

	// Sanitize parent format.
	$parent_format = filter_input( INPUT_POST, 'snax_parent_format', FILTER_SANITIZE_STRING );

	// Sanitize meme template.
	$meme_template = filter_input( INPUT_POST, 'snax_meme_template', FILTER_SANITIZE_STRING );

	// Sanitize origin.
	$origin = snax_sanitize_item_origin_value( filter_input( INPUT_POST, 'snax_origin', FILTER_SANITIZE_STRING ) );

	if ( empty( $origin ) ) {
		$origin = 'post';
	}

	$type = filter_input( INPUT_POST, 'snax_type', FILTER_SANITIZE_STRING );

	// Add item.
	$item_id = snax_add_media_item( $post_id, $type, array(
		'title'         => $title,
		'media_id'      => $media_id,
		'source'        => $source,
		'ref_link'      => $ref_link,
		'description'   => $description,
		'author_id'     => $author_id,
		'status'        => $status,
		'parent_format' => $parent_format,
		'origin'        => $origin,
		'meme_template' => $meme_template,
	) );

	if ( is_wp_error( $item_id ) ) {
		snax_ajax_response_error( 'Failed to create new item.', array(
			'error_code'    => $item_id->get_error_code(),
			'error_message' => $item_id->get_error_message(),
		) );
		exit;
	}

	$url_var = snax_get_url_var( 'item_submission' );

	$response_args = array(
		'item_id'      => $item_id,
		'redirect_url' => add_query_arg( $url_var, 'success', get_permalink( $item_id ) ),
	);

	snax_ajax_response_success( 'Item added successfully.', $response_args );
	exit;
}

/**
 * Add new embed item to existing post
 */
function snax_ajax_add_embed_item() {
	check_ajax_referer( 'snax-add-embed-item', 'security' );

	/** Required fields */

	// Read raw embed code, can be url or iframe.
	$embed_code = filter_input( INPUT_POST, 'snax_embed_code' ); // Use defaulf filter to keep raw code.

	// Sanitize the code, return value must be url to use with [embed] shortcode.
	$embed_meta = snax_get_embed_metadata( $embed_code );

	if ( false === $embed_meta ) {
		snax_ajax_response_error( __( 'Provided URL or embed code is not allowed!', 'snax' ) );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author (submitter) id not set!' );
		exit;
	}

	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
	}

	// Legal.
	$legal = filter_input( INPUT_POST, 'snax_legal', FILTER_SANITIZE_STRING );

	if ( empty( $legal ) && snax_legal_agreement_required() ) {
		snax_ajax_response_error( 'Legal agreement not accepted!' );
		exit;
	}

	/** Options fields */

	// Sanitize post id.
	$post_id = (int) filter_input( INPUT_POST, 'snax_post_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	// Sanitize title.
	$title = snax_sanitize_item_title( filter_input( INPUT_POST, 'snax_title', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.
	if ( empty( $title ) ) {
		$title = snax_sanitize_item_title( snax_get_embed_title( $embed_code ) ); // Remove all HTML tags from a string.
	}

	// Sanitize description.
	if ( snax_froala_for_list_items() ) {
		$description = snax_sanitize_item_content( strip_tags( filter_input( INPUT_POST, 'snax_description' ), '<p><a><em><strong>' ) );
	} else {
		$description = snax_sanitize_item_content( filter_input( INPUT_POST, 'snax_description', FILTER_SANITIZE_STRING ) );
	}

	// Sanitize status.
	$status = filter_input( INPUT_POST, 'snax_status', FILTER_SANITIZE_STRING );

	// Sanitize parent format.
	$parent_format = filter_input( INPUT_POST, 'snax_parent_format', FILTER_SANITIZE_STRING );

	// Sanitize origin.
	$origin = snax_sanitize_item_origin_value( filter_input( INPUT_POST, 'snax_origin', FILTER_SANITIZE_STRING ) );

	// Sanitize source.
	$source = snax_sanitize_item_source( filter_input( INPUT_POST, 'snax_source', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.

	// Sanitize referral link.
	$ref_link = snax_sanitize_item_ref_link( filter_input( INPUT_POST, 'snax_ref_link', FILTER_SANITIZE_STRING ) );

	if ( empty( $origin ) ) {
		$origin = 'post';
	}

	// Add item.
	$item_id = snax_add_embed_item( $post_id, array(
		'title'         => $title,
		'author_id'     => $author_id,
		'embed_meta'    => $embed_meta,
		'description'   => $description,
		'status'        => $status,
		'parent_format' => $parent_format,
		'origin'        => $origin,
		'source'        => $source,
		'ref_link'      => $ref_link,
	) );

	if ( is_wp_error( $item_id ) ) {
		snax_ajax_response_error( 'Failed to create new embed item.', array(
			'error_code'    => $item_id->get_error_code(),
			'error_message' => $item_id->get_error_message(),
		) );
		exit;
	}

	$thumbnail = false;
	$item_format = get_post_format( $item_id );
	if ( 'video' === $parent_format || 'video' === $item_format ) {
		snax_custom_download_embed_featured_media( $item_id );
		$thumbnail = get_post_thumbnail_id( $item_id );
	}

	$url_var = snax_get_url_var( 'item_submission' );

	$response_args = array(
		'item_id'      => $item_id,
		'redirect_url' => add_query_arg( $url_var, 'success', get_permalink( $item_id ) ),
		'thumbnail'    => $thumbnail,
	);

	snax_ajax_response_success( 'Item (embed) added successfully.', $response_args );
	exit;
}

/**
 * Add new text item to existing post
 */
function snax_ajax_add_text_item() {
	check_ajax_referer( 'snax-add-text-item', 'security' );

	/** Required fields */

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author (submitter) id not set!' );
		exit;
	}

	if ( ! user_can( $author_id, 'snax_add_items' ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
	}

	// Legal.
	$legal = filter_input( INPUT_POST, 'snax_legal', FILTER_SANITIZE_STRING );

	if ( empty( $legal ) && snax_legal_agreement_required() ) {
		snax_ajax_response_error( 'Legal agreement not accepted!' );
		exit;
	}

	/** Options fields */

	// Sanitize post id.
	$post_id = (int) filter_input( INPUT_POST, 'snax_post_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	// Sanitize title.
	$title = snax_sanitize_item_title( filter_input( INPUT_POST, 'snax_title', FILTER_SANITIZE_STRING ) ); // Remove all HTML tags from a string.

	// Sanitize description.
	if ( snax_froala_for_list_items() ) {
		$description = snax_sanitize_item_content( strip_tags( filter_input( INPUT_POST, 'snax_description' ), '<p><a><em><strong>' ) );
	} else {
		$description = snax_sanitize_item_content( filter_input( INPUT_POST, 'snax_description', FILTER_SANITIZE_STRING ) );
	}

	// Sanitize status.
	$status = filter_input( INPUT_POST, 'snax_status', FILTER_SANITIZE_STRING );

	// Sanitize parent format.
	$parent_format = filter_input( INPUT_POST, 'snax_parent_format', FILTER_SANITIZE_STRING );

	// Sanitize origin.
	$origin = snax_sanitize_item_origin_value( filter_input( INPUT_POST, 'snax_origin', FILTER_SANITIZE_STRING ) );

	// Sanitize referral link.
	$ref_link = snax_sanitize_item_ref_link( filter_input( INPUT_POST, 'snax_ref_link', FILTER_SANITIZE_STRING ) );

	if ( empty( $origin ) ) {
		$origin = 'post';
	}

	// Add item.
	$item_id = snax_add_text_item( $post_id, array(
		'title'         => $title,
		'author_id'     => $author_id,
		'description'   => $description,
		'status'        => $status,
		'parent_format' => $parent_format,
		'origin'        => $origin,
		'ref_link'      => $ref_link,
	) );

	if ( is_wp_error( $item_id ) ) {
		snax_ajax_response_error( 'Failed to create new text item.', array(
			'error_code'    => $item_id->get_error_code(),
			'error_message' => $item_id->get_error_message(),
		) );
		exit;
	}

	$url_var = snax_get_url_var( 'item_submission' );

	$response_args = array(
		'item_id'      => $item_id,
		'redirect_url' => add_query_arg( $url_var, 'success', get_permalink( $item_id ) ),
	);

	snax_ajax_response_success( 'Item (text) added successfully.', $response_args );
	exit;
}

/**
 * Delete item ajax handler
 */
function snax_ajax_delete_item() {
	// Sanitize item id.
	$item_id = (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $item_id ) {
		snax_ajax_response_error( 'Item id not set!' );
		exit;
	}

	$parent_id = wp_get_post_parent_id( $item_id );

	check_ajax_referer( 'snax-delete-item-' . $item_id, 'security' );

	// Sanitize user id.
	$user_id = (int) filter_input( INPUT_POST, 'snax_user_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $user_id ) {
		snax_ajax_response_error( 'User id not set!' );
		exit;
	}

	if ( ! user_can( $user_id, 'snax_delete_items', $item_id ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
	}

	$deleted = snax_delete_item( $item_id, $user_id );

	if ( is_wp_error( $deleted ) ) {
		snax_ajax_response_error( sprintf( 'Failed to delete item with id %d', $item_id ), array(
			'error_code'    => $deleted->get_error_code(),
			'error_message' => $deleted->get_error_message(),
		) );
		exit;
	}

	$response_args = array(
		'redirect_url' => add_query_arg( 'snax_item_deleted', 'success', get_permalink( $parent_id ) ),
	);

	snax_ajax_response_success( 'Item deleted successfully.', $response_args );
	exit;
}

/**
 * Set item as Featured ajax handler
 */
function snax_ajax_set_item_as_featured() {
	// Sanitize item id.
	$item_id = (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $item_id ) {
		snax_ajax_response_error( 'Item id not set!' );
		exit;
	}

	check_ajax_referer( 'snax-set-item-as-featured-' . $item_id, 'security' );

	// Sanitize user id.
	$user_id = (int) filter_input( INPUT_POST, 'snax_user_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $user_id ) {
		snax_ajax_response_error( 'User id not set!' );
		exit;
	}

	$item = get_post( $item_id );
	$item_author_id = (int) $item->post_author;

	$is_author = ( $item_author_id === $user_id );

	if ( ! $is_author && ! user_can( $user_id, 'administrator' ) ) {
		wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
	}

	$deleted = snax_set_item_as_featured( $item_id );

	if ( is_wp_error( $deleted ) ) {
		snax_ajax_response_error( sprintf( 'Failed to set item with id %d', $item_id ), array(
			'error_code'    => $deleted->get_error_code(),
			'error_message' => $deleted->get_error_message(),
		) );
		exit;
	}

	snax_ajax_response_success( 'Item set successfully.' );
	exit;
}

/**
 * Update items data (title, source, description)
 */
function snax_ajax_update_items() {
	check_ajax_referer( 'snax-frontend-submission', 'security' );

	$raw_data = filter_input_array( INPUT_POST, array(
		'snax_items' => array(
			'filter' => FILTER_DEFAULT,
			'flags'  => FILTER_REQUIRE_ARRAY,
		),
	) );

	$items = (array) $raw_data['snax_items'];

	$errors = array();

	foreach ( $items as $item_index => $item ) {
		$item_data = array();

		$item_id = (int) $item['id'];

		unset( $item['id'] );

		foreach( $item as $item_data_id => $item_data_value ) {
			$item_data[ $item_data_id ] = $item_data_value;
		}

		$item_data['order'] = $item_index;

		$ret = snax_update_item( $item_id, $item_data );

		if ( is_wp_error( $ret ) ) {
			$errors[] = $ret;
		}
	}

	if ( ! empty( $errors ) ) {
		snax_ajax_response_error( 'Failed to update items', $errors );
		exit;
	}

	snax_ajax_response_success( 'Items updated successfully.' );
	exit;
}

/**
 * Get more comments for snax item on list view
 */
function snax_ajax_load_more_item_comments() {

	$item_id 		= (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT );
	$loaded_pages 	= (int) filter_input( INPUT_POST, 'loaded_pages', FILTER_SANITIZE_NUMBER_INT );
	$per_page 		= apply_filters( 'snax_item_on_list_comments_per_page',3 );
	if ( 0 === $item_id ) {
		snax_ajax_response_error( 'Item id not set!' );
		exit;
	}
	 $args = array(
		'post_id' 	=> $item_id,
		'status' => 'approve',
	);
	$comments = get_comments( $args );
	$html = wp_list_comments( array(
		'type'     	=> 'comment',
		'per_page' 	=> $per_page,
		'echo'		=> false,
		'page'		=> $loaded_pages + 1,
	), $comments );

	$response_args = array(
		'html' => $html
	);

	snax_ajax_response_success( 'Result template generated successfully.', $response_args );
	exit;
}
