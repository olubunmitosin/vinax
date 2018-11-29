<?php
/**
 * Snax Frontend Submission Ajax Functions
 *
 * @package snax
 * @subpackage Ajax
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Return card template (ajax call)
 */
function snax_ajax_load_item_card_tpl() {
	$item_id = filter_input( INPUT_GET, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! $item_id ) {
		snax_ajax_response_error( 'Card item id not set!' );
		exit;
	}

	do_action( 'snax_before_ajax_load_item_card_tpl' );

	$query = new WP_Query( array(
		'p'                 => $item_id,
		'post_type'         => snax_get_item_post_type(),
		'posts_per_page'    => 1,
	) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			snax_get_template_part( 'content-card', snax_get_item_format( get_the_ID() ) );
		}

		wp_reset_postdata();
	}

	$tpl = ob_get_clean();

	do_action( 'snax_after_ajax_load_item_card_tpl' );

	$response_args = array(
		'html' => $tpl,
	);

	snax_ajax_response_success( 'Card template generated successfully.', $response_args );
	exit;
}

/**
 * Return image item template (ajax call)
 */
function snax_ajax_load_media_item_tpl() {
	$item_id = filter_input( INPUT_GET, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT );
	$type    = filter_input( INPUT_GET, 'snax_type', FILTER_SANITIZE_STRING );

	if ( ! $item_id ) {
		snax_ajax_response_error( 'Image item id not set!' );
		exit;
	}

	if ( ! $type ) {
		snax_ajax_response_error( 'Media type not set!' );
		exit;
	}

	$query = new WP_Query( array(
		'p'                 => $item_id,
		'post_type'         => snax_get_item_post_type(),
		'posts_per_page'    => 1,
	) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			snax_get_template_part( 'content-' . $type );
		}

		wp_reset_postdata();
	}

	$tpl = ob_get_clean();

	$response_args = array(
		'html' => $tpl,
	);

	snax_ajax_response_success( 'Media template generated successfully.', $response_args );
	exit;
}

/**
 * Return featured image template (ajax call)
 */
function snax_ajax_load_featured_image_tpl() {
	$media_id = filter_input( INPUT_GET, 'snax_media_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! $media_id ) {
		snax_ajax_response_error( 'Media id not set!' );
		exit;
	}

	$parent_format = filter_input( INPUT_GET, 'snax_parent_format', FILTER_SANITIZE_STRING );

	if ( ! $parent_format ) {
		snax_ajax_response_error( 'Parent format not set!' );
		exit;
	}

	if ( ! snax_is_active_format( $parent_format ) ) {
		snax_ajax_response_error( 'Parent format is not active!' );
		exit;
	}

	$post_id = (int) filter_input( INPUT_GET, 'snax_post_id', FILTER_SANITIZE_NUMBER_INT );

	// Attach to existing post.
	if ( $post_id ) {
		set_post_thumbnail( $post_id, $media_id );

	// Save as orphan.
	} else {
		add_post_meta( $media_id, '_snax_featured_image_format', $parent_format );
	}

	$query = new WP_Query( array(
		'p'                 => $media_id,
		'post_type'         => 'attachment',
		'posts_per_page'    => 1,
	) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			snax_get_template_part( 'featured-image' );
		}

		wp_reset_postdata();
	}

	$tpl = ob_get_clean();

	$response_args = array(
		'html' => $tpl,
	);

	snax_ajax_response_success( 'Featured image template generated successfully.', $response_args );
	exit;
}

/**
 * Return embed item template (ajax call)
 */
function snax_ajax_load_embed_item_tpl() {
	$item_id = filter_input( INPUT_GET, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT );

	if ( ! $item_id ) {
		snax_ajax_response_error( 'Embed item id not set!' );
		exit;
	}

	do_action( 'snax_before_ajax_load_embed_item_tpl' );

	$query = new WP_Query( array(
		'p'                 => $item_id,
		'post_type'         => snax_get_item_post_type(),
		'posts_per_page'    => 1,
	) );

	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			$url = get_post_meta( get_the_ID(), '_snax_embed_url',true );
			$title = snax_get_embed_title( $url );
			snax_get_template_part( 'content-embed' );
		}

		wp_reset_postdata();
	}

	$tpl = ob_get_clean();

	do_action( 'snax_after_ajax_load_embed_item_tpl' );

	$response_args = array(
		'html' 			=> $tpl,
		'embed_title'	=> $title,
	);

	snax_ajax_response_success( 'Embed template generated successfully.', $response_args );
	exit;
}

/**
 * Return content embed template (ajax call)
 */
function snax_ajax_load_content_embed_tpl() {
	// Response type. Optional.
	$res_type = filter_input( INPUT_POST, 'snax_res_type', FILTER_SANITIZE_STRING );

	// Type of embed: url or code. Optional.
	$embed_type = filter_input( INPUT_POST, 'snax_embed_type', FILTER_SANITIZE_STRING );

	// Read raw embed code, can be url or iframe.
	$embed_code = filter_input( INPUT_POST, 'snax_embed_code' ); // Use defaulf filter to keep raw code.

	// Sanitize the code, return value must be url to use with [embed] shortcode.
	$embed_meta = snax_get_embed_metadata( $embed_code );

	if ( false === $embed_meta ) {
		switch ( $embed_type ) {
			case 'url':
				snax_ajax_response_error( 'Provided URL is not allowed!' );
				break;

			case 'code':
				snax_ajax_response_error( 'Provided code is not allowed!' );
				break;

			default:
				snax_ajax_response_error( __( 'Provided URL or embed code is not allowed!', 'snax' ) );
		}
		exit;
	}

	do_action( 'snax_before_ajax_load_content_embed_tpl' );

	// Fake query, just to allow use of the $wp_embed.
	$query = new WP_Query( array(
		'posts_per_page'    => 1,
	) );
	ob_start();
	?>
	<?php
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) { $query->the_post();
			global $wp_embed;

			$shortcode_out = $wp_embed->run_shortcode( '[embed]' . $embed_meta['url'] . '[/embed]' );

			$key_suffix = md5( $embed_meta['url'] );
			$transient = '_oembed_' . $key_suffix;
			set_transient( $transient, $shortcode_out, 3600 * 24 );

			echo filter_var( $shortcode_out );
		}

		wp_reset_postdata();
	}

	$parsed_shortcode = ob_get_clean();

	do_action( 'snax_after_ajax_load_content_embed_tpl' );

	if ( 'shortcode' === $res_type ) {
		$tpl = $parsed_shortcode;
	} else {
		$tpl =
			'<span class="snax-embed-layer">
				<span class="snax-embed-url">' . esc_url( $embed_meta['url'] ) . '</span>' .
				$parsed_shortcode .
			'</span>';
	}
	$response_args = array(
		'html' => $tpl,
	);

	snax_ajax_response_success( 'Content embed template generated successfully.', $response_args );
	exit;
}

/**
 * Cancel submission
 */
function snax_ajax_cancel_submission() {

	if ( ! check_ajax_referer( 'snax-cancel', 'snax-cancel' ) ) {
		snax_ajax_response_error( 'Invalid nonce' );
		exit;
	}
	$user_id = get_current_user_id();
	$query_args = array(
		// Orphan.
		'post_parent'       => 0,
		'post_type'         => snax_get_item_post_type(),
		'post_status'       => array( 'publish', 'pending', 'draft' ),
		'posts_per_page'    => -1,
		'author'            => $user_id,
	);
	$orphan_items = get_posts( $query_args );
	foreach ( $orphan_items as $orphan_item ) {
		$media_id     = get_post_thumbnail_id( $orphan_item->ID );
		if ( $delete_media ) {
			wp_delete_attachment( $media_id, true );
		}
		wp_delete_post( $orphan_item->ID, true );
	};

	$query_args = array(
		'post_type'             => 'attachment',
		'post_status'           => 'inherit',
		'post_parent'           => 0,
		'posts_per_page'    	=> -1,
		'author'            	=> $user_id,
		'meta_key'              => '_snax_featured_image_format',
		'meta_compare'          => 'EXISTS',
	);
	$orphan_attachments = get_posts( $query_args );
	foreach ( $orphan_attachments as $orphan_attachment ) {
		wp_delete_attachment( $orphan_attachment->ID, true );
	}
	snax_ajax_response_success( 'Submission cancelled.', array() );
	exit;
}
