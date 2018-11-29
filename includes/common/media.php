<?php
/**
 * Snax Media Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Media upload form.
 *
 * @param array $args           Form arguments.
 */
function snax_media_upload_form( $args = array() ) {
	global $snax_form_index;

	if ( ! isset( $snax_form_index ) ) {
		$snax_form_index = 0;
	}

	if ( ! _device_can_upload() ) {
		echo '<p>' . sprintf( __( 'The web browser on your device cannot be used to upload files. You may be able to use the <a href="%s">native app for your device</a> instead.', 'snax' ), 'https://apps.wordpress.org/' ) . '</p>';

		return;
	}

	$defaults = array(
		'form_id'               => 'snax-media-upload-form-' . ( ++$snax_form_index ),
		'media_type'            => 'image',
		'image_upload_allowed'  => snax_is_image_upload_allowed(),
		'audio_upload_allowed'  => snax_is_audio_upload_allowed(),
		'video_upload_allowed'  => snax_is_video_upload_allowed(),
		'upload_by_url'         => true,
	);

	$args = wp_parse_args( $args, $defaults );

	$args = apply_filters( 'snax_media_upload_form_args', $args );

	$form_id            = $args['form_id'];
	$upload_action_url  = admin_url( 'async-upload.php' );
	$post_id            = isset( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : 0;

	// Filters.
	$filters = array(
		'mime_types' => array(),
	);

	// Filters > Image upload.
	$max_image_upload_size = 0;

	if ( $args['image_upload_allowed'] ) {
		$filters['image_max_file_size'] = snax_get_image_max_upload_size();
		$filters['mime_types'][] = array(
			'title'      => __( 'Image files', 'snax' ),
			'extensions' => implode( ',', snax_get_image_allowed_types() ),
		);

		$max_image_upload_size = snax_get_image_max_upload_size();
	}

	// Filters > Audio upload.
	$max_audio_upload_size = 0;

	if ( $args['audio_upload_allowed'] ) {
		$filters['audio_max_file_size'] = snax_get_audio_max_upload_size();
		$filters['mime_types'][] = array(
			'title'      => __( 'Audio files', 'snax' ),
			'extensions' => implode( ',', snax_get_audio_allowed_types() ),
		);

		$max_audio_upload_size = snax_get_audio_max_upload_size();
	}

	// Filters > Video upload.
	$max_video_upload_size = 0;

	if ( $args['video_upload_allowed'] ) {
		$filters['video_max_file_size'] = snax_get_video_max_upload_size();
		$filters['mime_types'][] = array(
			'title'      => __( 'Video files', 'snax' ),
			'extensions' => implode( ',', snax_get_video_allowed_types() ),
		);

		$max_video_upload_size = snax_get_video_max_upload_size();
	}

	$max_upload_size = max( $max_image_upload_size, $max_audio_upload_size, $max_video_upload_size );

	if ( is_multisite() && ! is_upload_space_available() ) {
		/**
		 * Fires when an upload will exceed the defined upload space quota for a network site.
		 */
		do_action( 'snax_upload_over_quota' );
		return;
	}

	// async-upload.php params.
	$post_params = array(
		'post_id'  => $post_id,
		'_wpnonce' => wp_create_nonce( 'media-form' ),  // Necessary for referrer check in async-upload.php.
		'short'    => true,                             // Return just uploaded media id.
	);

	$post_params = apply_filters( 'snax_upload_post_params', $post_params );

	// Plupload default config.
	$plupload_config = array(
		'runtimes'            => 'html5,flash,silverlight,html4',
		'file_data_name'      => 'async-upload',
		'url'                 => $upload_action_url,
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'             => $filters,
		'multipart_params'    => $post_params,
		'multi_selection'     => false,
	);

	// Currently only iOS Safari supports multiple files uploading but iOS 7.x has a bug that prevents uploading of videos
	// when enabled. See #29602.
	if ( wp_is_mobile() && strpos( $_SERVER['HTTP_USER_AGENT'], 'OS 7_' ) !== false &&
	     strpos( $_SERVER['HTTP_USER_AGENT'], 'like Mac OS X' ) !== false
	) {

		$plupload_config['multi_selection'] = false;
	}

	$plupload_config = apply_filters( 'snax_plupload_config', $plupload_config );

	do_action( 'snax_before_media_upload_form' );?>

	<div class="snax-media-upload-form" id="<?php echo esc_attr( $form_id ); ?>">
	<script type="text/javascript">
		(function(ctx) {
			if (!ctx.snaxPluploadConfig) {
				ctx.snaxPluploadConfig = {};
			}
			ctx.snaxPluploadConfig['<?php echo $form_id; ?>'] = <?php echo wp_json_encode( $plupload_config ); ?>;
		})(window);
	</script>

	<?php
	global $snax_form_file_upload_args;
	$snax_form_file_upload_args = array(
		'media_type'        => $args['media_type'],
		'upload_by_url'     => $args['upload_by_url'],
		'max_upload_size'   => $max_upload_size,
	);

	snax_get_template_part( 'form-file-upload' );

	unset( $GLOBALS['snax_form_file_upload_args'] );
	?>

	</div>

	<?php
	do_action( 'snax_after_media_upload_form' );
}

/**
 * Append snax_media_upload_action param to upload params
 *
 * @param array $post_params Upload params.
 *
 * @return array
 */
function snax_add_media_action_param( $post_params ) {
	if ( snax_is_frontend_submission_page() ) {
		$post_params['snax_media_upload_action'] = 'new_post_upload';
		$url_var                                 = snax_get_url_var( 'format' );
		$format                                  = (string) filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );

		$post_params['snax_media_upload_format'] = $format;

	} else {
		$post_params['snax_media_upload_action'] = 'contribution_upload';
	}

	return $post_params;
}

/**
 * Check whether media was uploaded via snax media upload form
 *
 * @param string $action Performed action type.
 *
 * @return bool
 */
function snax_is_media_upload_action( $action = null ) {
	$post_action = filter_input( INPUT_POST, 'snax_media_upload_action', FILTER_SANITIZE_STRING );

	// Compare if action set.
	if ( $action ) {
		$bool = $post_action === $action;

		// If action to compare not set, return true if param was sent.
	} else {
		$bool = (bool) $post_action;
	}

	return apply_filters( 'snax_media_upload_action', $bool );
}

/**
 * Return post format to which media is uploaded
 *
 * @return string           Empty if format not set.
 */
function snax_get_media_upload_format() {
	$format = filter_input( INPUT_POST, 'snax_media_upload_format', FILTER_SANITIZE_STRING );

	if ( ! snax_is_active_format( $format ) ) {
		$format = '';
	}

	return $format;
}

/**
 * Remove the item
 *
 * @param int $media_id Media ID.
 * @param int $author_id Author ID.
 *
 * @return bool|WP_Error
 */
function snax_delete_media( $media_id, $author_id ) {
	$media = get_post( $media_id );

	// Check type.
	if ( 'attachment' !== $media->post_type ) {
		return new WP_Error( 'post_type_test_failed', sprintf( 'Media %d is not an attachment post type.', $media_id ) );
	}

	// Check owner.
	if ( (int) $author_id !== (int) $media->post_author ) {
		return new WP_Error( 'ownership_test_failed', sprintf( 'Author %d is not an owner of the media %d.', $author_id, $media_id ) );
	}

	// Delete permanently, not move it to the trash.
	$force_delete_media = apply_filters( 'snax_force_delete_media', true, $media_id );

	// Delete media.
	$ret = wp_delete_attachment( $media_id, $force_delete_media );

	// On failure, return error.
	if ( false === $ret ) {
		return new WP_Error( 'wp_delete_attachment_failed', sprintf( 'Media %d could not be deleted.', $media_id ) );
	}

	return true;
}

/**
 * Update media meta
 *
 * @param int $media_id Media ID.
 * @param string $format Snax format.
 *
 * @return bool|WP_Error
 */
function snax_update_media_meta( $media_id, $format ) {
	$media = get_post( $media_id );

	// Check type.
	if ( 'attachment' !== $media->post_type ) {
		return new WP_Error( 'post_type_test_failed', sprintf( 'Media %d is not an attachment post type.', $media_id ) );
	}

	$ret = add_post_meta( $media_id, '_snax_parent_format', $format );

	// On failure, return error.
	if ( false === $ret ) {
		return new WP_Error( 'snax_update_media_meta_failed', sprintf( 'Media %d could not be updated.', $media_id ) );
	}

	return true;
}

/**
 * Get the image size, that should be used when rendering a single item.
 *
 * @return mixed|void
 */
function snax_get_item_image_size() {
	return apply_filters( 'snax_get_item_image_size', 'large' );
}

/**
 * Return collection item default image size
 *
 * @return string
 */
function snax_get_collection_item_image_size() {
	return apply_filters( 'snax_get_collection_item_image_size', 'thumbnail' );
}

/**
 * Register fix for animated Gif
 */
function snax_before_media_hooks() {
	add_filter( 'wp_get_attachment_image_src', 'snax_fix_animated_gif_image', 10, 4 );
}

/**
 * Deregister fix for animated Gif
 */
function snax_after_media_hooks() {
	remove_filter( 'wp_get_attachment_image_src', 'snax_fix_animated_gif_image', 10, 4 );
}

/**
 * Return the 'full' image size, instead of a thumbnail of a GIF file
 *
 * WordPress can't scale animated GIFs properly without dropping frames.
 * Thus we hijack every image size and return the original one.
 *
 * @param array $image Image.
 * @param int $attachment_id Attachment ID.
 * @param string $size Image size.
 * @param bool $icon Icon.
 *
 * @return array|false
 */
function snax_fix_animated_gif_image( $image, $attachment_id, $size, $icon ) {
	if ( 'full' !== $size ) {
		$is_intermediate = $image[3];

		if ( preg_match( '/\.gif$/', $image[0] ) > 0 && $is_intermediate ) {
			$image = wp_get_attachment_image_src( $attachment_id, 'full', $icon );
		}
	}

	return $image;
}

/**
 * Override WP limit to get more control over uploaded images
 */
function snax_set_new_upload_size_limit() {
	add_filter( 'upload_size_limit', 'snax_get_image_max_upload_size', 10, 3 );
}

/**
 * Revert to original WP limits
 */
function snax_reset_upload_size_limit() {
	remove_filter( 'upload_size_limit', 'snax_get_image_max_upload_size', 10, 3 );
}

/**
 * Allow Snax Author to upload image
 */
function snax_allow_snax_author_to_upload() {
	add_filter( 'snax_upload_post_params', 'snax_add_media_action_param' );
}

/**
 * Deny Snax Author to upload image
 */
function snax_deny_snax_author_to_upload() {
	remove_filter( 'snax_upload_post_params', 'snax_add_media_action_param' );
}

/**
 * Save image from url into the Media Library.
 *
 * @param string $image_url Image url.
 * @param int $author_id Author id.
 *
 * @return int|WP_Error
 */
function snax_save_image_from_url( $image_url, $author_id = 0 ) {
	if ( ! $author_id ) {
		$author_id = get_current_user_id();
	}

	// Strip off query string from image url to correctly match filetype.
	$image_url = strtok( $image_url, '?' );

	// Check mime type.
	$filetype = wp_check_filetype( $image_url );

	if ( ! in_array( $filetype['ext'], snax_get_image_allowed_types(), true ) ) {
		return new WP_Error( 'snax_save_image_from_url_failed', __( 'Upload failed. Image type is not allowed.', 'snax' ) );
	}

	// Check size (download only file headers).
	$headers = get_headers( $image_url, true );

	if ( false !== $headers && isset( $headers['Content-Length'] ) ) {
		$max_allowed_size_in_bytes = snax_get_image_max_upload_size();
		$size_in_bytes             = $headers['Content-Length'];

		if ( $size_in_bytes > $max_allowed_size_in_bytes ) {
			return new WP_Error( 'snax_save_image_from_url_failed', __( 'Upload failed. Image file is too big.', 'snax' ) );
		}
	} else {
		return new WP_Error( 'snax_save_image_from_url_failed', __( 'Upload failed. Image file is too big or its size couldn\'t be verified.', 'snax' ) );
	}

	// Download file content.
	$response = wp_remote_get( esc_url_raw( $image_url ), array( 'timeout' => 10 ) );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'snax_save_image_from_url_failed', __( 'Upload failed. Image couldn\'t be downloaded from the url.', 'snax' ) );
	}

	$body            = wp_remote_retrieve_body( $response );
	$upload_dir      = wp_upload_dir();
	$upload_dest_dir = trailingslashit( $upload_dir['path'] );
	$upload_dest_url = trailingslashit( $upload_dir['url'] );
	$filename        = basename( $image_url );

	// Strip all non-alpha numeric characters and spaces.
	$filename        = preg_replace( "/[^a-z0-9 ]/i", "", $filename );

	if ( file_exists( $upload_dest_dir . $filename ) ) {
		$filename = uniqid() . '-' . $filename;
	}
	$path            = $upload_dest_dir . $filename;

	// Save in uploads dir.
	@file_put_contents( $path, $body );

	$attachment = array(
		'guid'           => $upload_dest_url . $filename,
		'post_title'     => '',
		'post_content'   => '',
		'post_status'    => 'inherit',
		'post_mime_type' => $filetype['type'],
		'post_author'    => $author_id,
	);

	$post_id = wp_insert_attachment( $attachment, $path );

	// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$metadata = wp_generate_attachment_metadata( $post_id, $path );
	wp_update_attachment_metadata( $post_id, $metadata );

	return $post_id;
}
