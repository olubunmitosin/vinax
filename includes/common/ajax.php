<?php
/**
 * Snax Common AJAX Functions
 *
 * @package snax
 * @subpackage Ajax
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Prints ajax response, json encoded
 *
 * @param string $status    Status of the response (success|error).
 * @param string $message   Text message describing response status code.
 * @param array  $args      Response extra arguments.
 *
 * @return void
 */
function snax_ajax_response( $status, $message, $args ) {
	$res = array(
		'status'  => $status,
		'message' => $message,
		'args'    => $args,
	);

	echo wp_json_encode( $res );
}

/**
 * Prints ajax success response, json encoded
 *
 * @param string $message       Text message describing response status code.
 * @param array  $args          Response extra arguments.
 *
 * @return void
 */
function snax_ajax_response_success( $message, $args = array() ) {
	snax_ajax_response( 'success', $message, $args );
}

/**
 * Prints ajax error response, json encoded
 *
 * @param string $message       Text message describing response status code.
 * @param array  $args          Response extra arguments.
 *
 * @return void
 */
function snax_ajax_response_error( $message, $args = array() ) {
	snax_ajax_response( 'error', $message, $args );
}

/**
 * Return HTML markup for media img tag
 */
function snax_ajax_load_media_tpl() {
	$media_id = filter_input( INPUT_GET, 'snax_media_id', FILTER_SANITIZE_NUMBER_INT );
	$post_id  = filter_input( INPUT_GET, 'snax_post_id', FILTER_SANITIZE_NUMBER_INT );
	$type     = filter_input( INPUT_GET, 'snax_type', FILTER_SANITIZE_STRING );

	if ( ! $media_id ) {
		snax_ajax_response_error( 'Media id not set!' );
		exit;
	}

	if ( ! $post_id ) {
		snax_ajax_response_error( 'Post id not set!' );
		exit;
	}

	if ( ! $type ) {
		snax_ajax_response_error( 'Media type not set!' );
		exit;
	}

	// Remove all other media. User can have only one uploaded media at once.
	$media = get_post( $media_id );
	$user_id = $media->post_author;

	snax_remove_user_uploaded_media( $user_id, array( 'post__not_in' => array( $media_id ) ) );

	// Mark as snax uploaded media.
	update_post_meta( $media_id, '_snax_media', 'standard' );
	update_post_meta( $media_id, '_snax_media_belongs_to', $post_id );
	update_post_meta( $media_id, '_snax_media_type', $type );

	$html = '';

	switch( $type ) {
		case 'image':
			$html = wp_get_attachment_image( $media_id, snax_get_item_image_size() );
			break;

		case 'audio':
			$html =  snax_get_audio_media( $media_id );
			break;

		case 'video':
			$html =  snax_get_video_media( $media_id );
			break;
	}

	$response_args = array(
		'html' => $html,
	);

	snax_ajax_response_success( 'Media HTML tag fetched successfully.', $response_args );
	exit;
}

/**
 * Return HTML markup for embed code/url
 */
function snax_ajax_load_embed_tpl() {
	// Read raw embed code, can be url or iframe.
	$embed_code = filter_input( INPUT_POST, 'snax_embed_code' ); // Use defaulf filter to keep raw code.

	if ( empty( $embed_code ) ) {
		snax_ajax_response_error( 'Embed url not set' );
		exit;
	}

	// Sanitize the code, return value must be url to use with [embed] shortcode.
	$embed_meta = snax_get_embed_metadata( $embed_code );

	if ( false === $embed_meta ) {
		snax_ajax_response_error( 'Provided url is not a valid url to any supported services (like YouTube)' );
		exit;
	}

	$html = wp_oembed_get( $embed_meta['url'] );
	$title = snax_get_embed_title( $embed_meta['url'] );

	if ( false === $html ) {
		snax_ajax_response_error( 'Failed to load oEmbed HTML' );
		exit;
	}

	$response_args = array(
		'html' => $html,
		'embed_title'	=> $title,
	);

	snax_ajax_response_success( 'Embed template generated successfully.', $response_args );
	exit;
}

/**
 * Return Open Graph meta tags for a url
 */
function snax_ajax_fetch_og_data() {
	// Read raw embed code, can be url or iframe.
	$url = filter_input( INPUT_POST, 'snax_url', FILTER_SANITIZE_URL );

	if ( empty( $url ) ) {
		snax_ajax_response_error( 'Url not set!' );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author id not set!' );
		exit;
	}

	$skip_image = 'standard' === filter_input( INPUT_POST, 'snax_skip_image', FILTER_SANITIZE_STRING );

	$og_data = snax_parse_opengraph( $url );

	if ( is_wp_error( $og_data ) ) {
		$message = $og_data->get_error_message();
		$message .= ' ';
		$message .= esc_html__( 'Meta data could not be processed. Please fill the form manually.', 'bimber' );

		snax_ajax_response_error( $message );
		exit;
	}

	$errors = array();
	$processed_fields = 0;

	// Title.
	$title = '';
	$processed_fields++;

	if ( empty( $og_data['og:title'] ) ) {
		$errors[] = esc_html__( 'Title not found.', 'snax' );
	} else {
		$title = is_array( $og_data['og:title'] ) ? implode( '. ', $og_data['og:title'] ) : $og_data['og:title'];
	}

	// Description.
	$description = '';
	$processed_fields++;

	if ( empty( $og_data['og:description'] ) ) {
		$errors[] = esc_html__( 'Description not found.', 'snax' );
	} else {
		$description = is_array( $og_data['og:description'] ) ? implode( '. ', $og_data['og:description'] ) : $og_data['og:description'];
	}

	// Image.
	$image_id    = '';

	if ( ! $skip_image ) {
		$processed_fields++;

		if ( empty( $og_data['og:image'] ) ) {
			$errors[] = esc_html__( 'Image not found.', 'snax' );
		} else {
			$image_url = is_array( $og_data['og:image'] ) ? $og_data['og:image'][0] : $og_data['og:image'];

			$attachment_id = snax_save_image_from_url( $image_url, $author_id );

			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = $attachment_id->get_error_message();
			} else {
				$image_id = $attachment_id;
			}
		}
	}

	// Response.
	$response_args = array(
		'title'         => $title,
		'description'   => $description,
		'image_id'      => $image_id,
	);

	if ( empty( $errors ) ) {
		snax_ajax_response_success( 'Data fetched successfully.', $response_args );
	} else {
		// All fields failed.
		if ( $processed_fields === count( $errors ) ) {
			$error_str = esc_html__( 'This page prevents access or doesn\'t support Open Graph protocol. We couldn\'t read any data from it. Please fill the form manually.', 'snax' );
		} else {
			$error_str = sprintf( __( 'We were not able to read all data.<br />%s<br /> Please fill the missing data manually.', 'snax' ), implode( '<br />', $errors ) );
		}

		snax_ajax_response_error( $error_str, $response_args );
	}

	exit;
}

/**
 * Delete media ajax handler
 */
function snax_ajax_delete_media() {
	check_ajax_referer( 'snax-delete-media', 'security' );

	// Sanitize media id.
	$media_id = (int) filter_input( INPUT_POST, 'snax_media_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $media_id ) {
		snax_ajax_response_error( 'Media id not set!' );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author id not set!' );
		exit;
	}

	$deleted = snax_delete_media( $media_id, $author_id );

	if ( is_wp_error( $deleted ) ) {
		snax_ajax_response_error( sprintf( 'Failed to delete media with id %d', $media_id ), array(
			'error_code'    => $deleted->get_error_code(),
			'error_message' => $deleted->get_error_message(),
		) );
		exit;
	}

	snax_ajax_response_success( 'Media deleted successfully.' );
	exit;
}

/**
 * Delete media ajax handler
 */
function snax_ajax_update_media_meta() {
	// @todo - use own security
	check_ajax_referer( 'snax-delete-media', 'security' );

	// Sanitize media id.
	$media_id = (int) filter_input( INPUT_POST, 'snax_media_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

	if ( 0 === $media_id ) {
		snax_ajax_response_error( 'Media id not set!' );
		exit;
	}

	// Sanitize format.
	$format = filter_input( INPUT_POST, 'snax_parent_format', FILTER_SANITIZE_STRING );

	if ( ! $format ) {
		snax_ajax_response_error( 'Parent format not set!' );
		exit;
	}

	$updated = snax_update_media_meta( $media_id, $format );

	if ( is_wp_error( $updated ) ) {
		snax_ajax_response_error( sprintf( 'Failed to update media with id %d', $media_id ), array(
			'error_code'    => $updated->get_error_code(),
			'error_message' => $updated->get_error_message(),
		) );
		exit;
	}

	snax_ajax_response_success( 'Media updated successfully.' );
	exit;
}

/**
 * Delete media ajax handler
 */
function snax_ajax_load_user_uploaded_images() {
	// @todo - use own security
	check_ajax_referer( 'snax-delete-media', 'security' );

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_GET, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author id not set!' );
		exit;
	}

	// Sanitize format.
	$format = filter_input( INPUT_GET, 'snax_format', FILTER_SANITIZE_STRING );

	if ( empty( $format ) ) {
		snax_ajax_response_error( 'Format not set!' );
		exit;
	}

	$media = snax_get_user_uploaded_media( $format, $author_id );

	$images = array();

	foreach ( $media as $image ) {
		$images[] = array(
			'url' 		=> wp_get_attachment_url( $image->ID ),
			'thumb'		=> wp_get_attachment_thumb_url( $image->ID ),
			'snax_id'   => $image->ID,
		);
	}

	echo wp_json_encode( $images );
	exit;
}

/**
 * Get list of all tags filtered by term
 */
function snax_ajax_get_tags() {
	$term = filter_input( INPUT_GET, 'snax_term', FILTER_SANITIZE_STRING );

	$args = apply_filters( 'snax_ajax_tags_query_args', array(
		'name__like'	=> $term,
		'number'		=> 10,
	) );

	$arr = snax_get_tags_array( -1, $args );

	snax_ajax_response_success( 'Tags loaded successfully.', array(
		'tags' => $arr,
	) );
	exit;
}

/**
 * Ajax login action
 */
function snax_ajax_login() {
	check_ajax_referer( 'snax-ajax-login-nonce', 'security', true );

	$credentials = array();
	$credentials['user_login'] 		= filter_input( INPUT_POST, 'log', FILTER_SANITIZE_STRING );
	$credentials['user_password']	= filter_input( INPUT_POST, 'pwd', FILTER_SANITIZE_STRING );
	$credentials['remember'] 		= filter_input( INPUT_POST, 'rememberme', FILTER_SANITIZE_STRING );

	// Verify reCaptcha.
	$use_recaptcha = snax_is_recatpcha_enabled_for_login_form();

	if ( $use_recaptcha ) {
		$recaptcha_token = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING );

		$recaptcha_valid = snax_verify_recaptcha( $recaptcha_token );

		if ( ! $recaptcha_valid ) {
			snax_ajax_response_error( snax_get_recaptcha_invalid_message() );
			exit;
		}
	}

	$secure_cookie = is_ssl();

	$user = wp_signon( $credentials, $secure_cookie );

	if ( is_wp_error( $user ) ) {
		$message = $user->get_error_message();
		snax_ajax_response_error( $message );
		exit;
	}

	$redirect_url = filter_input( INPUT_POST, 'redirect_to', FILTER_SANITIZE_STRING );
	$redirect_url = preg_replace( '/\?' . snax_get_login_popup_url_variable() . '.*/', '', $redirect_url );
	$redirect_url = preg_replace( '/' . snax_get_login_popup_url_variable() . '.*/', '', $redirect_url );

	$response_args = array(
		'redirect_url' => $redirect_url,
	);

	snax_ajax_response_success( 'Log in successfull', $response_args );
	exit;
}

/**
 * Ajax forgot pass action
 */
function snax_ajax_forgot_pass() {
	check_ajax_referer( 'snax-ajax-forgot-pass-nonce', 'security', true );

	$errors = snax_retrieve_password();

	if ( is_wp_error( $errors ) ) {
		$message = $errors->get_error_message();
		snax_ajax_response_error( $message );
		exit;
	}

	$response_args = array(
		'redirect_url' => filter_input( INPUT_POST, 'redirect_to', FILTER_SANITIZE_STRING ),
	);

	snax_ajax_response_success( __( 'New password sent. Please check your mailbox.', 'snax' ), $response_args );
	exit;
}

/**
 *
 */
function snax_ajax_save_image_from_url() {
	check_ajax_referer( 'snax-add-media-item', 'security' );

	// Sanitize image url.
	$image_url = filter_input( INPUT_POST, 'snax_image_url', FILTER_SANITIZE_URL );

	if ( 0 === $image_url ) {
		snax_ajax_response_error( 'Image url not set!' );
		exit;
	}

	// Sanitize author id.
	$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

	if ( 0 === $author_id ) {
		snax_ajax_response_error( 'Author id not set!' );
		exit;
	}

	$saved = snax_save_image_from_url( $image_url, $author_id );

	if ( is_wp_error( $saved ) ) {
		o( 'Failed to saved image.', array(
			'error_code'    => $saved->get_error_code(),
			'error_message' => $saved->get_error_message(),
		) );
		exit;
	}

	snax_ajax_response_success( 'Image saved successfully.', array(
		'image_id' => $saved,
	) );
	exit;
}

/**
 * Import meme
 */
function snax_ajax_import_meme() {
	check_ajax_referer( 'snax_meme_import_nonce', 'security' );

	$image_url 	= filter_input( INPUT_POST, 'snax_image_url', FILTER_SANITIZE_URL );
	$name 		= filter_input( INPUT_POST, 'snax_meme_name', FILTER_SANITIZE_STRING );
	$import_id	= filter_input( INPUT_POST, 'snax_import_id', FILTER_SANITIZE_STRING );

	$args = array(
		'posts_per_page' => 1,
		'meta_query'    => array(
			array(
				'key'       => '_snax_meme_import_id',
				'value'     => $import_id,
				'compare'   => '=',
			),
		),
		'post_type'	=> snax_get_meme_template_post_type(),
	);
	$posts = get_posts( $args );
	if ( ! empty( $posts ) ) {
		snax_ajax_response_success( 'skip', array() );
		exit;
	}

	$post = array(
		'post_title'    => wp_strip_all_tags( $name ),
		'post_status'   => 'publish',
		'post_type' 	=> snax_get_meme_template_post_type(),
	);
	$post_id = wp_insert_post( $post );

	$result = snax_custom_sideload_featured_media( $image_url, $post_id );

	if ( ! $result ) {
		wp_delete_post( $post_id, true );
		snax_ajax_response_error( 'Could not download the image' );
		exit;
	}

	update_post_meta( $post_id, '_snax_meme_import_id', $import_id );

	snax_ajax_response_success( 'imported', array() );
	exit;
}
