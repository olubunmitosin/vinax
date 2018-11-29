<?php
/**
 * Snax Template Functions
 *
 * @package snax
 * @subpackage TemplateFunctions
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

global $snax_in_custom_loop;

/**
 * Whether we are not in main query loop
 *
 * @return mixed
 */
function snax_in_custom_loop() {
	global $snax_in_custom_loop;

	return $snax_in_custom_loop;
}

/**
 * Set up the in_custom_loop flag at the start of a loop
 *
 * @param WP_Query $query WP query.
 */
function snax_custom_loop_start( $query ) {
	global $snax_in_custom_loop;
	$snax_in_custom_loop = true;

	/**
	 * Check if query is an instance of WP_Query.
	 * Some plugins, like BuddyPress may change it.
	 */
	if ( $query instanceof WP_Query && $query->is_main_query() ) {
		$snax_in_custom_loop = false;
	}
}

/**
 * Set up the in_custom_loop flag at the end of a loop
 */
function snax_custom_loop_end() {
	global $snax_in_custom_loop;
	$snax_in_custom_loop = true;
}

/**
 * Disable the post thumbnail
 *
 * @param WP_Query $query           Query object.
 */
function snax_disable_default_featured_media( $query ) {
	/**
	 * Check if query is an instance of WP_Query.
	 * Some plugins, like BuddyPress may change it.
	 */
	if ( ! ( $query instanceof WP_Query ) ) {
		return;
	}

	$is_main_query 				= $query->is_main_query();
	$is_snax_item_single_post	= is_singular( snax_get_item_post_type() );
	$is_snax_single_post		= is_single() && snax_is_format();

	$disable = $is_main_query && ( $is_snax_item_single_post || $is_snax_single_post );

	if ( $is_snax_single_post && snax_show_featured_media_on_single( snax_get_format() ) ) {
		$disable = false;
	}

	$disable = apply_filters( 'snax_disable_default_featured_media', $disable );

	if ( $disable ) {
		if ( has_post_thumbnail() ) {
			add_filter( 'get_post_metadata', 'snax_skip_post_thumbnail', 99, 4 );
		}
	}
}

/**
 * Short circuit to skip displaying post thumbnail
 *
 * @param null|array|string $value     The value get_metadata() should return - a single metadata value, or an array of values.
 * @param int               $object_id Object ID.
 * @param string            $meta_key  Meta key.
 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
 *
 * @return string
 */
function snax_skip_post_thumbnail( $value, $object_id, $meta_key, $single ) {
	if ( '_thumbnail_id' === $meta_key && true === $single ) {
		$value = '';
	}

	return $value;
}

/**
 * Enable the post thumbnail
 */
function snax_enable_default_featured_media() {
	remove_filter( 'get_post_metadata', 'snax_skip_post_thumbnail', 99, 4 );
}

/**
 * Enable the post thumbnail
 *
 * @param string $content           Post content.
 *
 * @return string
 */
function snax_enable_default_featured_media_in_content( $content ) {
	snax_enable_default_featured_media();

	return $content;
}

/**
 * Load a template part into a template
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */
function snax_get_template_part( $slug, $name = null ) {
	// Trim off any slashes from the slug.
	$slug = ltrim( $slug, '/' );

	if ( empty( $slug ) ) {
		return;
	}

	$parent_dir_path = trailingslashit( get_template_directory() );
	$child_dir_path  = trailingslashit( get_stylesheet_directory() );

	$files = array(
		$child_dir_path . 'snax/' . $slug . '.php',
		$parent_dir_path . 'snax/' . $slug . '.php',
		snax_get_plugin_dir() . 'templates/' . $slug . '.php',
	);

	if ( ! empty( $name ) ) {
		array_unshift(
			$files,
			$child_dir_path . 'snax/' . $slug . '-' . $name . '.php',
			$parent_dir_path . 'snax/' . $slug . '-' . $name . '.php',
			snax_get_plugin_dir() . 'templates/' . $slug . '-' . $name . '.php'
		);
	}

	$located = '';

	foreach ( $files as $file ) {
		if ( empty( $file ) ) {
			continue;
		}

		if ( file_exists( $file ) ) {
			$located = $file;
			break;
		}
	}

	if ( strlen( $located ) ) {
		load_template( $located, false );
	}
}
