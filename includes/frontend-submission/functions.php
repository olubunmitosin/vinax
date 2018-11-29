<?php
/**
 * Snax Frontend Submission Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Submission handler
 *
 * @param WP $request       Request object.
 */
function snax_handle_new_submission( $request ) {

	$frontend_submission_nonce = filter_input( INPUT_POST, 'snax-frontend-submission-nonce', FILTER_SANITIZE_STRING );

	// Check whether process current request.
	if ( ! empty( $frontend_submission_nonce ) ) {
		// User has no access.
		if ( ! current_user_can( 'snax_add_posts' ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
		}

		// Limits check.
		if ( snax_user_reached_submitted_posts_limit() ) {
			wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
		}

		// Security check.
		if ( ! wp_verify_nonce( $frontend_submission_nonce, 'snax-frontend-submission' ) ) {
			wp_die( esc_html__( 'Cheatin&#8217; uh?', 'snax' ) );
		}

		// Sanitize input.
		$post_id        = (int) filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );
		$title          = snax_sanitize_post_title( filter_input( INPUT_POST, 'snax-post-title', FILTER_SANITIZE_STRING ) );    // Plain text (HTML not allowed).
		$format         = filter_input( INPUT_POST, 'snax-post-format', FILTER_SANITIZE_STRING );                               // Plain text (HTML not allowed).
		$list_type      = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
		$list_type 		= apply_filters( 'snax_input_get_list_type', $list_type );

		// Source.
		$has_source = (bool) filter_input( INPUT_POST, 'snax-post-has-source' );
		$source     = '';

		if ( $has_source ) {
			$source = snax_sanitize_item_source( filter_input( INPUT_POST, 'snax-post-source', FILTER_SANITIZE_URL ) );     // Valid url.
		}

		// Referral link.
		$has_ref_link   = (bool) filter_input( INPUT_POST, 'snax-post-has-ref-link' );
		$ref_link       = '';

		if ( $has_ref_link ) {
			$ref_link = snax_sanitize_item_ref_link( filter_input( INPUT_POST, 'snax-post-ref-link', FILTER_SANITIZE_URL ) ); // Valid url.
		}

		if ( 'text' === $format ) {
			$description    = snax_sanitize_post_content( filter_input( INPUT_POST, 'snax-post-description', FILTER_DEFAULT ) );    // HTML allowed.
		} else {
			$description    = snax_sanitize_post_description( filter_input( INPUT_POST, 'snax-post-description', FILTER_DEFAULT ) );    // HTML allowed.
		}

		// @todo - add filtering.
		$categories = isset( $_POST[ 'snax-post-category' ] ) ? $_POST[ 'snax-post-category' ] : array();

		$tags           = filter_input( INPUT_POST, 'snax-post-tags', FILTER_SANITIZE_STRING );                                 // String.

		// List submission.
		$list_submission = filter_input( INPUT_POST, 'snax-list-submission', FILTER_SANITIZE_STRING );

		// Normalize checkbox sent data.
		if ( ! $list_submission ) {
			$list_submission = 'none';
		}

		// List voting.
		$list_voting = filter_input( INPUT_POST, 'snax-list-voting', FILTER_SANITIZE_STRING );

		// Normalize checkbox sent data.
		if ( ! $list_voting ) {
			$list_voting = 'none';
		}

		// Legal.
		$legal          = (bool) filter_input( INPUT_POST, 'snax-post-legal', FILTER_SANITIZE_STRING );                         // Bool.

		// Author.
		$author_id = get_current_user_id();

		// Status.
		$save_as_draft = filter_input( INPUT_POST, 'snax-save-draft', FILTER_SANITIZE_STRING );

		$status = ! empty( $save_as_draft ) ? 'draft' : '';

		if ( empty( $status ) ) {
			$status = user_can( $author_id, 'snax_publish_posts' ) ? 'publish' : 'pending';
		}

		// Validate input.
		$errors = array();

		if ( empty( $title ) ) {
			$errors['title'] = new WP_Error( 'snax_post_title_empty', esc_html__( 'This field is required', 'snax' ) );
		}

		if ( empty( $legal ) && snax_legal_agreement_required() ) {
			$errors['legal'] = new WP_Error( 'snax_post_legal_not_accepted', esc_html__( 'This field is required', 'snax' ) );
		}

		$formats = snax_get_active_formats();

		$current_format = $format;

		if ( 'list' === $format && ! empty( $list_type ) ) {
			$current_format = $list_type . '_' . $format;
		}

		if ( ! isset( $formats[ $current_format ] ) ) {
			$errors['format'] = new WP_Error( 'snax_post_format_not_set', esc_html__( 'Choose format', 'snax' ) );
		}

		$category_selected = count( $categories ) > 1 || ( count( $categories ) === 1 && -1 !== (int) $categories[0] );

		if ( snax_is_category_field_required( $format ) && ! $category_selected ) {
			$errors['category_id'] = new WP_Error( 'snax_post_category_empty', esc_html__( 'This field is required', 'snax' ) );
			// Check category.
		} else if ( $category_selected ) {
			$allowed_categories = snax_get_category_whitelist( $format );

			// Not all categories allowed?
			if ( ! in_array( '', $allowed_categories, true ) ) {
				foreach ( $categories as $category_id ) {
					$category_obj = get_category( $category_id );

					// Category invalid?
					if ( ! $category_obj || ! in_array( $category_obj->slug, $allowed_categories, true ) ) {
						$errors['category_id'] = new WP_Error( 'snax_post_category_not_set', esc_html__( 'Choose valid category', 'snax' ) );
						break;
					}
				}
			}
		}

		// Auto-assign to category?
		$auto_assign = array_filter( snax_get_category_auto_assign( $format ) );

		if ( ! empty( $auto_assign ) ) {
			foreach( $auto_assign as $category_slug ) {
				$auto_cat = get_category_by_slug( $category_slug );

				if ( $auto_cat ) {
					$categories[] = $auto_cat->term_id;
				}
			}
		}

		$categories = array_map( 'intval', array_unique( $categories ) );

		if ( empty( $errors ) ) {
			// Process tags.
			$tags_arr = array();

			if ( ! empty( $tags ) ) {
				$tags_arr   = explode( ',', $tags );

				// Limit tags number.
				$tags_arr = array_slice( $tags_arr, 0, snax_get_tags_limit() );
			}

			$submission_data = array(
				'id'				=> $post_id,
				'title'         	=> $title,
				'source'        	=> $source,
				'ref_link'        	=> $ref_link,
				'description'   	=> $description,
				'category_id'   	=> $categories,
				'tags'          	=> $tags_arr,
				'list_voting'       => $list_voting,
				'list_submission'	=> $list_submission,
				'author'			=> $author_id,
				'status'			=> $status,
			);

			do_action( 'snax_before_processing_submission' );

			do_action( 'snax_handle_'. $format .'_submission', $submission_data, $request );

			snax_notify_admin_about_new_post( $post_id, $format );

			do_action( 'snax_after_processing_submission' );

		} else {
			$request->set_query_var( 'snax_errors', $errors );
			$request->set_query_var( 'snax_sanitized_field_values', array(
				'title'         	=> $title,
				'source'        	=> $source,
				'ref_link'        	=> $ref_link,
				'description'   	=> $description,
				'format'        	=> $format,
				'category_id'   	=> $categories,
				'tags'          	=> $tags,
				'list_voting'       => $list_voting,
				'list_submission'	=> $list_submission,
				'legal'         	=> $legal,
			) );
		}
	}
}

/**
 * Assign Snax items to parent post
 *
 * @param int $post_id          Post id.
 * @param int $author_id        Author id.
 */
function snax_attach_user_orphan_items_to_post( $post_id = 0, $author_id = 0 ) {
	$parent = get_post( $post_id );

	if ( ! $author_id ) {
		$author_id = get_current_user_id();
	}

	$parent_format = snax_get_post_format( $parent->ID );

	$posts = snax_get_user_orphan_items( $parent_format, $author_id );

	foreach ( $posts as $post ) {
		$my_post = array(
			'ID'            => $post->ID,
			'post_parent'   => $parent->ID,
			'post_status'   => $parent->post_status,
		);

		if ( ! empty( $post->post_title ) ) {
			$my_post['post_name'] = sanitize_title( $post->post_title );
		}

		wp_update_post( $my_post );
	}

	snax_bump_post_submission_count( $parent, count( $posts ) );
}

/**
 * Bump the total submission count of a post
 *
 * @param int|WP_Post $post Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $difference Optional. Default 1.
 *
 * @return int Post submission count
 */
function snax_bump_post_submission_count( $post = null, $difference = 1 ) {
	$post = get_post( $post );

	// Get current value.
	$submission_count = snax_get_post_submission_count( $post );

	// Update it.
	$submission_count += (int) $difference;

	// And store again.
	update_post_meta( $post->ID, '_snax_submission_count', $submission_count );

	return apply_filters( 'snax_bump_post_submission_count', $submission_count, $post->ID, (int) $difference );
}

/**
 * Use first assigned Snax image item as post featured image
 *
 * @param int $post_id      Post id.
 */
function snax_set_first_image_item_as_post_featured( $post_id = 0 ) {
	$parent = get_post( $post_id );

	// Get first image item.
	$image_items = snax_get_items( $parent->ID, array(
		'meta_key'          => '_snax_item_format',
		'meta_value'        => 'image',
		'posts_per_page'    => 1,
	) );

	foreach ( $image_items as $image_item ) {
		// Get item featured media.
		$image_id = get_post_thumbnail_id( $image_item->ID );

		// Set it as post featured media.
		set_post_thumbnail( $parent->ID, $image_id );

		// Attach featured media to item (Media Library, the "Uploded to" column).
		wp_update_post( array(
			'ID'            => $image_id,
			'post_parent'   => $parent->ID,
		) );
	}
}

/**
 * Check whether to show or not the Create button
 *
 * @return bool
 */
function snax_show_create_button() {
	return apply_filters( 'snax_show_create_button', true );
}

/**
 * Hide the Create button on a frontend submission page
 *
 * @param bool $show            Current state.
 *
 * @return bool
 */
function snax_hide_create_button( $show ) {
	if ( is_user_logged_in() && ! current_user_can( 'snax_add_posts' ) ) {
		$show = false;
	}

	if ( snax_is_frontend_submission_page() ) {
		$show = false;
	}

	return $show;
}

/**
 * Check whether curent page is the Frontedn Submission page
 *
 * @return bool
 */
function snax_is_frontend_submission_page() {
	$is = is_page( snax_get_frontend_submission_page_id() );

	return apply_filters( 'snax_is_frontend_submission_page', $is );
}

/**
 * Check whether curent page is the Submission page for the Format
 *
 * @param string $format		Format.
 *
 * @return bool
 */
function snax_is_format_submission_page( $format ) {
	if ( ! snax_is_frontend_submission_page() ) {
		return false;
	}

	$current_format = snax_get_submission_page_format();

	if ( ! $current_format ) {
		$active_formats = snax_get_active_formats();

		// It the $format the only active format?
		if ( isset( $active_formats[ $format ] ) && 1 === count( $active_formats ) ) {
			$current_format = $format;
		}
	}

	return $current_format === $format;
}

/**
 * Return current submission form format
 *
 * @return string
 */
function snax_get_submission_page_format() {
	$url_var = snax_get_url_var( 'format' );
	$format = (string) filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );

	return $format;
}

function snax_is_frontend_submission_edit_mode() {
	return (bool) filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );
}

/**
 * Add submission form to a page
 *
 * @param string $content      Current page content.
 *
 * @return string
 */
function snax_append_frontend_submission_form( $content ) {
	if ( snax_is_frontend_submission_page() && ! snax_is_item() ) {
		ob_start();
		snax_get_template_part( 'frontend-submission' );
		$content .= ob_get_clean();
	}

	return $content;
}

/**
 * Allow multiple file upload using Plupload UI
 *
 * @param array $config     Pluplod config.
 *
 * @return array
 */
function snax_plupload_allow_multi_selection( $config ) {
	if ( snax_is_frontend_submission_page() ) {
		$config['multi_selection'] = true;
	}

	return $config;
}

/**
 * Adjust the frontend submission page title
 *
 * @param string $title Post title.
 * @param int    $id Post ID.
 *
 * @return string
 */
function snax_frontend_submission_the_title( $title, $id = null ) {
	if ( snax_get_frontend_submission_page_id() === $id ) {
		$url_var = snax_get_url_var( 'format' );
		$format_id = (string) filter_input( INPUT_GET, $url_var, FILTER_SANITIZE_STRING );
		$formats = snax_get_active_formats();

		// Map different list types.
		if ( 'list' === $format_id ) {
			$list_type      = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
			$list_type 		= apply_filters( 'snax_input_get_list_type', $list_type );
			$format_type = (string) $list_type;

			if ( $format_type ) {
				$format_id = $format_type . '_' . $format_id;
			}
		}

		if ( isset( $formats[ $format_id ] ) ) {
			$title = $formats[ $format_id ]['labels']['add_new'];
		}
	}

	return $title;
}

/**
 * Add custom body class for Frontend Submission page
 *
 * @param array $classes        Current classes.
 *
 * @return array
 */
function snax_frontend_submission_body_class( $classes ) {
	if ( snax_is_frontend_submission_page() ) {
		$classes[] = 'snax-page-frontend-submission';
	}

	return $classes;
}

/**
 * Load upload form dependencies
 */
function snax_upload_form_load_resources() {
	if ( ! function_exists( 'media_handle_upload' ) ) {
		require_once ABSPATH . '/wp-admin/includes/media.php';
		require_once ABSPATH . '/wp-admin/includes/file.php';
		require_once ABSPATH . '/wp-admin/includes/image.php';
		require_once ABSPATH . '/wp-admin/includes/template.php';
	}
}

/**
 * Print upload form hidden fields
 */
function snax_upload_form_render_form_internals() {
?>
	<div id="media-items">
		<div id="media-upload-error"></div>
	</div>

	<input type="hidden" class="snax-uploaded-media-id" value="" autocomplete="off"/>
<?php
}

/*****************
 *
 * FROALA editor.
 *
 ****************/

/**
 * Return Froala formatted media link
 *
 * @param int $id		Media id.
 *
 * @return string		JSON encoded string or false if media doesn't exist.
 */
function snax_froala_image_uploaded_response( $id ) {
	$res = false;
	$url = wp_get_attachment_url( $id );

	if ( false !== $url ) {
		$res = wp_json_encode( array(
			'link' 			=> $url,
			'snax-id'	=> $id,
		) );
	}

	return $res;
}

function snax_upload_media_from_embed_code( $methods, $media_type ) {
	if ( ! in_array( $media_type, array( 'audio', 'video' ), true ) ) {
		return $methods;
	}

	$methods['from_embed'] = array(
		'name'          => sprintf( __( 'Embed %s code', 'snax' ), $media_type ),
		'form_class'    => 'snax-load-from-embed-area',
		'form_callback' => 'snax_render_new_embed_form_' . $media_type,
	);

	return $methods;
}

function snax_render_new_embed_form_audio() {
	snax_render_new_embed_form( 'audio' );
}

function snax_render_new_embed_form_video() {
	snax_render_new_embed_form( 'video' );
}


function snax_render_new_embed_form( $media_type ) {
	snax_get_template_part( 'posts/form-edit/new-embed', $media_type );
}

add_filter( 'snax_input_get_list_type', 'snax_fix_missing_list_types', 10, 1 );

/**
 * Fix list types for drafts.
 *
 * @param string $list_type  List type.
 * @return string
 */
function snax_fix_missing_list_types( $list_type ) {
	$post_id        = (int) filter_input( INPUT_GET, snax_get_url_var( 'post' ), FILTER_SANITIZE_NUMBER_INT );
	$format 		= filter_input( INPUT_GET, 'snax_format', FILTER_SANITIZE_STRING );
	if ( 'list' === $format && $post_id > 0 && empty( $list_type ) ) {
		$list_submission = 'standard' === get_post_meta( $post_id, '_snax_post_submission', true );
		$list_voting 	 = 'standard' === get_post_meta( $post_id, '_snax_post_voting', true );
		if ( false === $list_submission && $list_voting ) {
			$list_type = 'ranked';
		}
		if ( false === $list_submission && false === $list_voting ) {
			$list_type = 'classic';
		}
	}
	return $list_type;
}

add_action( 'transition_post_status',  'snax_handle_post_publish', 10, 3 );
/**
 * Handle post publish.
 *
 * @param string  $new_status	New status.
 * @param string  $old_status	Old status.
 * @param WP_Post $post			Post object.
 */
function snax_handle_post_publish( $new_status, $old_status, $post ) {
	if ( $new_status === $old_status || 'publish' !== $new_status ) {
		return;
	}
	$type = get_post_type( $post->ID);
	if ( snax_get_poll_post_type() === $type || snax_get_quiz_post_type() === $type ) {
		do_action( 'snax_post_published', $post->ID, $type );
	} else {
		$format = wp_get_post_terms( $post->ID, snax_get_snax_format_taxonomy_slug() );
		$frontend = apply_filters( 'snax_is_format_being_published', false );
		if ( $frontend || ! empty( $format ) ) {
			do_action( 'snax_post_published', $post->ID, $type );
		}
	}
}
