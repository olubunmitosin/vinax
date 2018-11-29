<?php
/**
 * Snax Embeds Functions
 *
 * @package snax
 * @subpackage Functions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Parse embed code and retrieve its url, format etc
 *
 * @param string $code          Url or iframe code.
 *
 * @return bool|array           Metadata array or false if code not valid.
 */
function snax_get_embed_metadata( $code ) {
	$meta = false;

	$is_url = ( false !== filter_var( $code, FILTER_VALIDATE_URL ) );

	if ( $is_url ) {
		$url = $code;
	} else {
		$url = snax_extract_oembed_url( $code );
	}

	if ( ! empty( $url ) && snax_is_oembed_url( $url ) ) {
		$provider_name = snax_get_oembed_provider_name( $url );

		$meta = array(
			'url'               => $url,
			'provider_name'     => $provider_name,
			'post_format'       => snax_get_oembed_post_format( $provider_name, $url ),
		);
	}

	return apply_filters( 'snax_get_embed_metadata', $meta, $code );
}

/**
 * Return oembed provider name based on its url
 *
 * @param string $url               oEmbed url.
 *
 * @return bool|string              False if not found.
 */
function snax_get_oembed_provider_name( $url ) {
	$map = snax_get_oembed_url_to_provider_name_map();
	$provider_name = false;

	foreach ( $map as $matchmask => $oembed_name ) {
		if ( preg_match( $matchmask, $url ) ) {
			$provider_name = $oembed_name;
			break;
		}
	}

	return $provider_name;
}

/**
 * Return oembed post format based on its url
 *
 * @param string $provider_name             oEmbed provider name.
 * @param string $url             			oEmbed url.
 *
 * @return bool|string                      False if not found.
 */
function snax_get_oembed_post_format( $provider_name, $url = '' ) {
	$map = snax_get_oembed_provider_name_to_post_format_map();
	$post_format = false;

	if ( isset( $map[ $provider_name ] ) ) {
		$post_format = $map[ $provider_name ];
	}

	// Facebook video post.
	if ( 'facebook' === $provider_name ) {
		if ( false !== strpos( $url, 'video.php' ) ) {
			$post_format = 'video';
		}
	}

	return $post_format;
}

/**
 * Return embed provider "url regex" => "unique name" map
 *
 * @return array
 */
function snax_get_oembed_url_to_provider_name_map() {
	$map = array(
		'#http://((m|www)\.)?youtube\.com/watch.*#i'          => 'youtube',
		'#https://((m|www)\.)?youtube\.com/watch.*#i'         => 'youtube',
		'#http://((m|www)\.)?youtube\.com/playlist.*#i'       => 'youtube',
		'#https://((m|www)\.)?youtube\.com/playlist.*#i'      => 'youtube',
		'#http://youtu\.be/.*#i'                              => 'youtube',
		'#https://youtu\.be/.*#i'                             => 'youtube',
		'#https?://(.+\.)?vimeo\.com/.*#i'                    => 'vimeo',
		'#https?://(www\.)?dailymotion\.com/.*#i'             => 'dailymotion',
		'#https?://dai.ly/.*#i'                               => 'dailymotion',
		'#https?://(www\.)?flickr\.com/.*#i'                  => 'flickr',
		'#https?://flic\.kr/.*#i'                             => 'flickr',
		'#https?://(.+\.)?smugmug\.com/.*#i'                  => 'smugmug',
		'#https?://(www\.)?hulu\.com/watch/.*#i'              => 'hulu',
		'#https?://(.+\.)?photobucket.com/albums/.*#i'        => 'photobucket',
		'#https?://(.+\.)?photobucket.com/groups/.*#i'        => 'photobucket',
		'#https?://(www\.)?scribd\.com/doc/.*#i'              => 'scribd',
		'#https?://wordpress.tv/.*#i'                         => 'wordpresstv',
		'#https?://(.+\.)?polldaddy\.com/.*#i'                => 'polldaddy',
		'#https?://poll\.fm/.*#i'                             => 'polldaddy',
		'#https?://(www\.)?funnyordie\.com/videos/.*#i'       => 'funnyordie',
		'#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i' => 'twitter',
		'#https?://(www\.)?twitter\.com/.+?/timelines/.*#i'   => 'twitter',
		'#https?://(www\.)?twitter\.com/i/moments/.*#i'       => 'twitter',
		'#https?://vine.co/v/.*#i'                            => 'vine',
		'#https?://(www\.)?soundcloud\.com/.*#i'              => 'soundcloud',
		'#https?://(.+?\.)?slideshare\.net/.*#i'              => 'slideshare',
		'#https?://(www\.)?instagr(\.am|am\.com)/p/.*#i'      => 'instagram',
		'#https?://(open|play)\.spotify\.com/.*#i'            => 'spotify',
		'#https?://(.+\.)?imgur\.com/.*#i'                    => 'imgur',
		'#https?://(www\.)?meetu(\.ps|p\.com)/.*#i'           => 'meetup',
		'#https?://(www\.)?issuu\.com/.+/docs/.+#i'           => 'issuu',
		'#https?://(www\.)?collegehumor\.com/video/.*#i'      => 'collegehumor',
		'#https?://(www\.)?mixcloud\.com/.*#i'                => 'mixcloud',
		'#https?://(www\.|embed\.)?ted\.com/talks/.*#i'       => 'ted',
		'#https?://(www\.)?(animoto|video214)\.com/play/.*#i' => 'animoto',
		'#https?://(.+)\.tumblr\.com/post/.*#i'               => 'tumblr',
		'#https?://(www\.)?kickstarter\.com/projects/.*#i'    => 'kickstarter',
		'#https?://kck\.st/.*#i'                              => 'kickstarter',
		'#https?://cloudup\.com/.*#i'                         => 'cloudup',
		'#https?://(www\.)?reverbnation\.com/.*#i'            => 'reverbnation',
		'#https?://videopress.com/v/.*#'                      => 'wordpresscom',
		'#https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i' => 'reddit',
		'#https?://(www\.)?speakerdeck\.com/.*#i'             => 'speakerdeck',
		'#https?://(www\.)?facebook\.com/.*#i'             	  => 'facebook',
	);

	return apply_filters( 'snax_oembed_url_to_provider_name_map', $map );
}

/**
 * Return embed provider "unique name" => "WP post format" map
 *
 * @return array
 */
function snax_get_oembed_provider_name_to_post_format_map() {
	$map = array(
		'youtube'       => 'video',
		'vimeo'         => 'video',
		'dailymotion'   => 'video',
		'flickr'        => 'image',
		'smugmug'       => 'image',
		'hulu'          => 'video',
		'photobucket'   => 'image',
		'scribd'        => '',
		'wordpresstv'   => 'video',
		'polldaddy'     => '',
		'funnyordie'    => 'video',
		'twitter'       => 'status',
		'vine'          => 'video',
		'soundcloud'    => 'audio',
		'slideshare'    => '',
		'instagram'     => 'image',
		'spotify'       => 'audio',
		'imgur'         => 'image',
		'meetup'        => '',
		'issuu'         => 'image',
		'collegehumor'  => 'image',
		'mixcloud'      => 'audio',
		'ted'           => 'video',
		'animoto'       => 'video',
		'tumblr'        => 'image',
		'kickstarter'   => '',
		'cloudup'       => 'video',
		'reverbnation'  => 'audio',
		'wordpresscom'  => '',
		'reddit'        => '',
		'speakerdeck'   => '',
		'facebook'      => '',
	);

	return apply_filters( 'snax_oembed_provider_name_to_post_format_map', $map );
}

/**
 * Return predefined embed providers config
 *
 * @return array
 */
function snax_get_embed_code_providers() {
	$providers = array(

		/*
	    * YOUTUBE
	    *
	    * Url to share: https://youtu.be/SyKSWlIioD0
	    *
	    * Code to embed:
	    * <iframe width="560" height="315" src="https://www.youtube.com/embed/SyKSWlIioD0" frameborder="0" allowfullscreen></iframe>
	    */
		'youtube' => array(
			'url_pattern'       => '/https?:\/\/www\.youtube\.com\/embed\/([^"]+)/',    // Reguired. Pattern to find embed url in embed code.
			'url_callback'      => 'snax_convert_to_youtube_oembed_url',                // Optional. Convert found embed url to be oEmbed valid url. Default: embed url.
		),

		/*
		 * TWITTER
		 *
		 * Url to share: https://twitter.com/sanderwagner/status/722002889498062848
		 *
		 * Code to embed:
		 *  <blockquote class="twitter-tweet" data-lang="en">
		 *      <p lang="en" dir="ltr">
		 *          <a href="https://twitter.com/GavinHJackson">@GavinHJackson</a>
		 *          <a href="https://twitter.com/grodaeu">@grodaeu</a> i have a 100 Q forecasting spreadsheet w subspreadsheets for every question, searchterms and platform links inc
		 *      </p>
				* &mdash; Sander Wagner (@sanderwagner)
		 *      <a href="https://twitter.com/sanderwagner/status/722002889498062848">April 18, 2016</a>
		 *  </blockquote>
		 *  <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
		 */
		'twitter' => array(
			'url_pattern'       => '/https?:\/\/twitter\.com\/[^\/]+\/status\/\d+/',
		),

		/*
		 * INSTAGRAM
		 *
		 * Url to share: https://www.instagram.com/p/BEappqDuS3M/?taken-by=kimkardashian
		 *
		 * Code to embed:
		 * <blockquote class="instagram-media" data-instgrm-captioned data-instgrm-version="6" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:658px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);">
		 *  <div style="padding:8px;">
		 *      <div style=" background:#F8F8F8; line-height:0; margin-top:40px; padding:40.9259259259% 0; text-align:center; width:100%;">
		 *          <div style=" background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACwAAAAsCAMAAAApWqozAAAAGFBMVEUiIiI9PT0eHh4gIB4hIBkcHBwcHBwcHBydr+JQAAAACHRSTlMABA4YHyQsM5jtaMwAAADfSURBVDjL7ZVBEgMhCAQBAf//42xcNbpAqakcM0ftUmFAAIBE81IqBJdS3lS6zs3bIpB9WED3YYXFPmHRfT8sgyrCP1x8uEUxLMzNWElFOYCV6mHWWwMzdPEKHlhLw7NWJqkHc4uIZphavDzA2JPzUDsBZziNae2S6owH8xPmX8G7zzgKEOPUoYHvGz1TBCxMkd3kwNVbU0gKHkx+iZILf77IofhrY1nYFnB/lQPb79drWOyJVa/DAvg9B/rLB4cC+Nqgdz/TvBbBnr6GBReqn/nRmDgaQEej7WhonozjF+Y2I/fZou/qAAAAAElFTkSuQmCC); display:block; height:44px; margin:0 auto -44px; position:relative; top:-22px; width:44px;">
		 *          </div>
		 *      </div>
		 *      <p style=" margin:8px 0 0 0; padding:0 4px;">
		 *          <a href="https://www.instagram.com/p/BEappqDuS3M/" style=" color:#000; font-family:Arial,sans-serif; font-size:14px; font-style:normal; font-weight:normal; line-height:17px; text-decoration:none; word-wrap:break-word;" target="_blank">blue lagoon!</a>
		 *      </p>
		 *      <p style=" color:#c9c8cd; font-family:Arial,sans-serif; font-size:14px; line-height:17px; margin-bottom:0; margin-top:8px; overflow:hidden; padding:8px 0 7px; text-align:center; text-overflow:ellipsis; white-space:nowrap;">
		 *          Zdjęcie zamieszczone przez użytkownika Kim Kardashian West (@kimkardashian) <time style=" font-family:Arial,sans-serif; font-size:14px; line-height:17px;" datetime="2016-04-20T09:06:01+00:00">20 Kwi, 2016 o 2:06 PDT</time>
		 *      </p>
		 *  </div>
		 * </blockquote>
		 * <script async defer src="//platform.instagram.com/en_US/embeds.js"></script>
		 */
		'instagram' => array(
			'url_pattern'       => '/https?:\/\/www\.instagram\.com\/p\/[^\/]+\//',
		),

		/*
		 * VIMEO
		 *
		 * Url to share: https://vimeo.com/73252373
		 *
		 * Code to embed:
		 *  <iframe src="https://player.vimeo.com/video/73252373" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
		 *  <p>
		 *      <a href="https://vimeo.com/73252373">Day edit at Lower Big Sandy, West Virginia</a>
		 *      from
		 *      <a href="https://vimeo.com/user13429414">FATTY BROWN</a>
		 *      on
		 *      <a href="https://vimeo.com">Vimeo</a>.
		 *  </p>
		 */
		'vimeo' => array(
			'url_pattern'       => '/https?:\/\/vimeo\.com\/\d+/',
		),

		/*
		 * VINE
		 *
		 * Url to share: https://vine.co/v/iUrEF7eQmdb
		 *
		 * Code to embed:
		 * <iframe src="https://vine.co/v/iUrEF7eQmdb/embed/simple" width="600" height="600" frameborder="0"></iframe><script src="https://platform.vine.co/static/scripts/embed.js"></script>
		 *
		 */
		'vine' => array(
			'url_pattern'       => '/https?:\/\/vine\.co\/v\/[^\/]+/',
		),

		/*
		 * DAILYMOTION
		 *
		 * Url to share: http://dai.ly/x47155f
		 *
		 * Code to embed:
		 * <iframe frameborder="0" width="480" height="270" src="//www.dailymotion.com/embed/video/x47155f" allowfullscreen></iframe>
		 * <br />
		 * <a href="http://www.dailymotion.com/video/x47155f_captain-america-vs-grey-hulk-epic-battle_videogames" target="_blank">CAPTAIN AMERICA VS GREY HULK - EPIC BATTLE</a>
		 * <i> przez <a href="http://www.dailymotion.com/KjraGaming" target="_blank">KjraGaming</a></i>
		 */
		'dailymotion' => array(
			'url_pattern'       => '/dailymotion\.com\/embed\/video\/([^\/"]+)/',
			'url_callback'      => 'snax_convert_to_dailymotion_oembed_url',
		),

		/*
		 * FACEBOOK
		 *
		 * Url to share: WP doesn't support (1.08.16) FaceBook oEmbed
		 *
		 * Code to embed (Post):
		 * <iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2Fzuck%2Fposts%2F10102997098716941&width=500" width="500" height="536" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>
		 *
		 * Code to embed (Video):
		 * <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fzuck%2Fvideos%2F10102979862144171%2F&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>
		 */
		'facebook' => array(
			'url_pattern'       => '/(https?:\/\/www\.facebook\.com\/plugins\/[a-z]+\.php\?href=[^&]+)[^"]+" width="(\d+)" height="(\d+)"/',
			'url_callback'      => 'snax_convert_to_facebook_oembed_url',
		),
	);

	return apply_filters( 'snax_get_embed_code_providers', $providers );
}

/**
 * Try to extract url from passed embed code
 *
 * @param string $code      Iframe.
 *
 * @return string           Valid url or empty string.
 */
function snax_extract_oembed_url( $code ) {
	$url        = '';
	$providers  = snax_get_embed_code_providers();

	foreach ( $providers as $provider_id => $provider ) {
		if ( preg_match( $provider['url_pattern'], $code, $matches ) ) {
			$url = $matches[0];

			// Run extra parser to conver url if needed.
			if ( ! empty( $provider['url_callback'] ) && is_callable( $provider['url_callback'] ) ) {
				$url = call_user_func( $provider['url_callback'], $matches );
			}

			break;
		}
	}

	return apply_filters( 'snax_extract_oembed_url', $url, $code );
}

/**
 * Check whether url points to valid oEmbed service
 *
 * @param string $url       oEmbed url.
 *
 * @return bool
 */
function snax_is_oembed_url( $url ) {
	require_once( ABSPATH . WPINC . '/class-oembed.php' );

	$oembed = _wp_oembed_get_object();
	$html   = $oembed->get_html( $url );

	return false !== $html;
}

/**
 * Return oEmbed provider
 *
 * @param string $url           oEmbed url.
 *
 * @return false|string
 */
function snax_get_oembed_provider( $url ) {
	require_once( ABSPATH . WPINC . '/class-oembed.php' );
	$oembed = _wp_oembed_get_object();

	return $oembed->get_provider( $url );
}

/**
 * Build oEmbed YouTube url
 *
 * @param array $matches        Regex found parts.
 *
 * @return mixed|void
 */
function snax_convert_to_youtube_oembed_url( $matches ) {
	$youtube_movie_id = $matches[1];

	$url = 'https://youtu.be/' . $youtube_movie_id;

	return apply_filters( 'snax_convert_to_youtube_oembed_url', $url, $matches );
}

/**
 * Build oEmbed Dailymotion url
 *
 * @param array $matches        Regex found parts.
 *
 * @return mixed|void
 */
function snax_convert_to_dailymotion_oembed_url( $matches ) {
	$movie_id = $matches[1];

	$url = 'http://dai.ly/' . $movie_id;

	return apply_filters( 'snax_convert_to_dailymotion_oembed_url', $url, $matches );
}

/**
 * Build oEmbed FaceBook url
 *
 * @param array $matches        Regex found parts.
 *
 * @return mixed|void
 */
function snax_convert_to_facebook_oembed_url( $matches ) {
	$url 	= $matches[1];
	$width 	= $matches[2];
	$height = $matches[3];

	$width_height = sprintf( '&width=%d&height=%d', $width, $height );

	$url .= $width_height;

	return apply_filters( 'snax_convert_to_facebook_oembed_url', $url, $matches );
}

/**
 * Use short-circuit to compose Facebook oEmbed HTML, until FB native support will be added.
 *
 * @param null|string $result 		The UNSANITIZED (and potentially unsafe) HTML that should be used to embed. Default null.
 * @param string      $url    		The URL to the content that should be attempted to be embedded.
 *
 * @return mixed		HTML or null if not allowed.
 */
function snax_facebook_oembed_result( $result, $url ) {
	// FB regular post.
	if ( false !== strpos( $url, 'facebook.com/plugins/post.php' ) ) {
		// Grab the post width and height from url query string.
		if ( preg_match( '/width=(\d+)&height=(\d+)/', $url, $matches ) ) {
			$attr = array(
				'width'  => $matches[1],
				'height' => $matches[2],
			);
		// If failed, fall back to embed defaults.
		} else {
			$attr = wp_embed_defaults( $url );
		}

		$result = sprintf(
			'<iframe src="%s" width="%d" height="%d" class="facebook-post" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe>',
			$url,
			$attr['width'],
			$attr['height']
		);
	}

	// FB video post.
	if ( false !== strpos( $url, 'facebook.com/plugins/video.php' ) ) {
		$attr = wp_embed_defaults( $url );

		$result = sprintf(
			'<iframe src="%s" width="%d" height="%d" class="facebook-video" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe>',
			$url,
			$attr['width'],
			$attr['height']
		);
	}

	return $result;
}

/**
 * Get the embed title TODO
 *
 * @param str $embed_code  Url or iframe code.
 * @return str
 */
function snax_get_embed_title( $embed_code ) {

	$embed_meta = snax_get_embed_metadata( $embed_code );

	$title = false;
	remove_filter( 'oembed_providers', '__return_empty_array' );
	$wp_oembed = new WP_oEmbed();
	add_filter( 'oembed_providers', '__return_empty_array' );
	$provider_url = $wp_oembed->get_provider( $embed_meta['url'] );

	if ( ! empty( $provider_url ) ) {
		$json = file_get_contents( $provider_url . '?url=' . $embed_meta['url']); 
		$json = json_decode( $json );
		$title = $json->title;
	}
	return (string) $title;
}

/**
 * Set Embedly as the only oEmbed provider
 */
function snax_add_embedly_providers() {
	if ( ! snax_is_embedly_enabled() ) {
		return;
	}
	add_filter( 'oembed_providers', '__return_empty_array' );
	$provider_uri = snax_embedly_uri();
	wp_oembed_add_provider( '#https?://[^\s]+#i', $provider_uri, true );
}

/**
 * Build Embedly provider URL
 *
 * @return str
 */
function snax_embedly_uri() {

	$embedly_api_key = snax_get_embedly_api_key();

	$cards_controls = snax_is_embedly_buttons() ? '1' : '0';
	$cards_theme 	= snax_is_embedly_dark_skin() ? '1' : '0';
	$cards_width 	= snax_get_embedly_width();
	$cards_align 	= snax_get_embedly_alignment();

	if ( $cards_theme) {
		$cards_theme = 'dark';
	} else {
		$cards_theme = 'light';
	}

	$option_params 	= array(
		'cards_controls=' . $cards_controls,
		'&cards_theme=' . $cards_theme,
		'&cards_align=' . $cards_align,
	);

	if ( $cards_width> 0 ) {
		$option_params[] = '&cards_width=' . $cards_width;
	}

	$base = apply_filters( 'snax_embedly_base_uri', 'https://api.embedly.com/2/card' );
	$param_str = '?';
	foreach ( $option_params as $key => $value ) {
		$param_str .= $value;
	}

	$key = apply_filters( 'snax_embedly_base_key', 'cedd0120dd674379ab8c9689f2cfe588' );
	if ( snax_embedly_verify_cards_key( $embedly_api_key ) ) {
		$param_str .= '&cards_key=' . $embedly_api_key;
	}
	$key_param = '&key=' . $key;
	$param_str .= $key_param;
	return $base . $param_str;
}

/**
 * Get and save the cards API key using the Embedly API key set in options
 *
 * @param str $api_key  Embedly API key.
 * @return bool
 */
function snax_embedly_verify_cards_key( $api_key ) {
	if ( ! $api_key ) {
		return false;
	}
	$transient = '_snax_verification_embedly_api_key_' . $api_key;
	if ( '1' === get_transient( $transient ) ){
		return true;
	}
	$url = apply_filters( 'snax_embedly_obtain_key_url', 'http://api.embed.ly/1/feature?feature=card_details&key=' );
	$url .= $api_key;
	$result = wp_remote_retrieve_body( wp_remote_get( $url ) );
	$result = json_decode( $result );
	if ( isset( $result->error_code ) ) {
		 return false;
	}
	if ( $result ) {
		$verification = $result->card_details;
		set_transient( $transient, $verification, 4 * 60 * 60 );
		return true;
	} else {
		return false;
	}
}

/**
 * Ensure that the Embedly script is there if needed
 *
 * @param str $content  The post content.
 * @return str
 */
function snax_add_embedly_script( $content ) {
	$embedly_script = apply_filters( 'snax_embedly_script_code', '<script async src="//cdn.embedly.com/widgets/platform.js" charset="UTF-8"></script>' );
	preg_match_all( '/<blockquote.*embedly-card/U', $content, $matches );
	if ( count( $matches[0] ) > 0 && substr_count( $content, $embedly_script ) === 0 ) {
		$content .= $embedly_script;
	}
	return $content;
}
