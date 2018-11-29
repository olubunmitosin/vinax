<?php
/**
 * Media Ace plugin integration
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'snax_image_post_content',                  'snax_mace_replace_gif_with_video', 10, 2 );
add_filter( 'snax_item_media_args',                     'snax_mace_dont_apply_link_on_media' );
add_action( 'snax_before_capture_item_media',           'snax_mace_allow_gif_conversion' );
add_action( 'snax_after_capture_item_media',            'snax_mace_disallow_gif_conversion' );
add_action( 'snax_post_added',			                'snax_mace_replace_gif_in_froala_content', 10 , 2 );
add_filter( 'snax_content_to_edit',                     'snax_mace_convert_mp4_videos_into_froala_images', 10 ,2 );
// Prevent lazy loading images/embeds during upload.
add_action( 'parse_query',                              'snax_mace_prevent_lazy_loading' );
add_filter( 'snax_before_ajax_load_embed_item_tpl',     'snax_mace_disable_lazy_load' );
add_filter( 'snax_after_ajax_load_embed_item_tpl',      'snax_mace_enabled_lazy_load' );
add_filter( 'snax_before_ajax_load_item_card_tpl',      'snax_mace_disable_lazy_load' );
add_filter( 'snax_after_ajax_load_item_card_tpl',       'snax_mace_enabled_lazy_load' );
add_filter( 'snax_before_ajax_load_content_embed_tpl',  'snax_mace_disable_lazy_load' );
add_filter( 'snax_after_ajax_load_content_embed_tpl',   'snax_mace_enabled_lazy_load' );

// Prevent lazy load during submission processing.
add_action( 'snax_before_processing_submission',        'snax_mace_disable_lazy_load' );
add_action( 'snax_after_processing_submission',         'snax_mace_enabled_lazy_load' );


/**
 * Replace GIF image with MP4 video shortcode
 *
 * @param string $html              Inpit html.
 * @param int    $media_id          Media id.
 *
 * @return string
 */
function snax_mace_replace_gif_with_video( $html, $media_id ) {
	$shortcode = mace_get_video_shortcode( $media_id );

	if ( $shortcode ) {
		$html = $shortcode;
	}

	return $html;
}

/**
 * Override default featured media arguments
 *
 * @param array $args   Arguments.
 *
 * @return array
 */
function snax_mace_dont_apply_link_on_media( $args ) {
	if ( $args['allow_video'] ) {
		$mp4_version = mace_get_gif_mp4_version( get_post_thumbnail_id() );

		if ( $mp4_version ) {
			$args['apply_link'] = false;
		}
	}

	return $args;
}

/**
 * Allow GIF to MP4 conversion
 *
 * @param array $args       Arguments.
 */
function snax_mace_allow_gif_conversion( $args ) {
	if ( $args['allow_video'] ) {
		add_filter( 'post_thumbnail_html', 'snax_mace_replace_gif_thumbnail_to_mp4_video' , 10, 4 );
	}
}

/**
 * Disallow GIF to MP4 conversion
 *
 * @param array $args       Arguments.
 */
function snax_mace_disallow_gif_conversion( $args ) {
	if ( $args['allow_video'] ) {
		remove_filter( 'post_thumbnail_html', 'snax_mace_replace_gif_thumbnail_to_mp4_video' , 10, 4 );
	}
}

/**
 * Replaces GIF images with mp4 version in post thumbnails
 *
 * @param string $html              HTML.
 * @param int    $post_id           Post id.
 *
 * @return string
 */
function snax_mace_replace_gif_thumbnail_to_mp4_video( $html, $post_id, $post_thumbnail_id, $size ) {
	$html = mace_replace_gif_with_shortcode( $html, $post_thumbnail_id );
	$html = do_shortcode( $html );

	return $html;
}

/**
 * Replaces GIF with MP4 in newly created Story
 *
 * @param int    $post_id           Post id.
 * @param string $post_type         Post type.
 */
function snax_mace_replace_gif_in_froala_content( $post_id, $post_type ) {
	if ( 'text' !== $post_type ) {
		return;
	}

	$post    = get_post( $post_id );
	$content = $post->post_content;
	$matches = array();
	preg_match( '/\[caption.*\[\/caption\]/', $content, $matches );

	foreach ( $matches as $match ) {
		$url = array();
		preg_match( '/src="([^"]*)"/i', $match, $url );

		if ( strpos( $url[0], '.gif' ) ) {
			$url           = str_ireplace( 'src=', '', $url[0] );
			$url           = str_ireplace( '"', '', $url );
			$attachment_id = mace_get_image_by_url( $url );

			$shortcode = mace_get_video_shortcode( $attachment_id );

			if ( $shortcode ) {
				$content   = str_replace( $match, $shortcode, $content );
			}
		}
	}

	$post->post_content = $content;
	wp_update_post( $post );
}

/**
 * Reverse conversion for drafts
 *
 * @param string $content           Content.
 *
 * @return string
 */
function snax_mace_convert_mp4_videos_into_froala_images( $content ) {

	if ( preg_match_all( '/\[video.*\]/', $content, $all_matches ) ) {
		foreach ( $all_matches[0] as $index => $video_shortcode ) {

			$media_src = '';

			if ( preg_match( '/src="([^"]*)"/i', $video_shortcode, $src ) ) {
				$media_src = $src[0];
			}

			$gif_url = mace_get_image_by_mp4_version( $media_src );
			if ( ! empty( $gif_url ) ) {
				$attachment_id = mace_get_image_by_url( $gif_url );
				$img_tag       = '<img data-snax-id="' . $attachment_id . '" alt="" class="fr-dib" style="width: 300px;" src="' . $gif_url . '">';
				$content       = str_replace( $video_shortcode, $img_tag, $content );
			}
		}
	}

	return $content;
}

function snax_mace_prevent_lazy_loading( $load ) {
	if ( snax_can_use_plugin( 'buddypress/bp-loader.php' ) ) {
		if ( is_buddypress() ) {
			return;
		}
	}

	$is_front_submission_page = snax_is_frontend_submission_page() || filter_input( INPUT_GET, snax_get_url_var( 'format' ) );

	if ( $is_front_submission_page ) {
		snax_mace_disable_lazy_load();
	}
}

function snax_mace_disable_lazy_load(  ) {
	add_filter( 'mace_lazy_load_embed',     '__return_false', 99 );
	add_filter( 'mace_lazy_load_image',     '__return_false', 99 );
}

function snax_mace_enabled_lazy_load(  ) {
	remove_filter( 'mace_lazy_load_embed',  '__return_false', 99 );
	remove_filter( 'mace_lazy_load_image',  '__return_false', 99 );
}
