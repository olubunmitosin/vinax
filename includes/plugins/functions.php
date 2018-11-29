<?php
/**
 * Snax 3rd party plugins integration
 *
 * @package snax
 * @subpackage Plugins
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$plugins_path = trailingslashit( dirname( __FILE__ ) );

if ( snax_can_use_plugin( 'wordpress-social-login/wp-social-login.php' ) ) {
	require_once( $plugins_path . 'wordpress-social-login.php' );
}

if ( snax_can_use_plugin( 'buddypress/bp-loader.php' ) ) {
	require_once( $plugins_path . 'buddypress/loader.php' );
}

if ( snax_can_use_plugin( 'quick-adsense-reloaded/quick-adsense-reloaded.php' ) ) {
	require_once( $plugins_path . 'quick-adsense-reloaded.php' );
}

if ( snax_can_use_plugin( 'sitepress-multilingual-cms/sitepress.php' ) ) {
	require_once( $plugins_path . 'wpml.php' );
}

if ( snax_can_use_plugin( 'fb-instant-articles/facebook-instant-articles.php' ) ) {
	require_once( $plugins_path . 'facebook-instant-articles.php' );
}

if ( snax_can_use_plugin( 'woocommerce/woocommerce.php' ) ) {
	require_once( $plugins_path . 'woocommerce.php' );
}

if ( snax_can_use_plugin( 'media-ace/media-ace.php' ) ) {
	require_once( $plugins_path . 'media-ace.php' );
}

if ( snax_can_use_plugin( 'wp-subtitle/wp-subtitle.php' ) ) {
	require_once( $plugins_path . 'wp-subtitle.php' );
}

if ( snax_can_use_plugin( 'js_composer/js_composer.php' ) ) {
	require_once( $plugins_path . 'visual-composer.php' );
}

if ( snax_can_use_plugin( 'mashsharer/mashshare.php' ) ) {
	require_once( $plugins_path . 'mashshare.php' );
}

if ( snax_can_use_plugin( 'amp/amp.php' ) ) {
	require_once( $plugins_path . 'amp/amp.php' );
}

if ( snax_can_use_plugin( 'mycred/mycred.php' ) ) {
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . '/mycred/mycred.php' );
}
