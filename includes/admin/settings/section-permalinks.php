<?php
/**
 * Snax Settings Section
 *
 * @package snax
 * @subpackage Settings
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

// Register section and fields.
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_permalinks' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_permalinks' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_permalinks( $sections ) {
	$sections['snax_permalinks'] = array(
		'title'    => _x( 'Snax', 'Permalink Settings', 'snax' ),
		'callback' => 'snax_permalinks_section_description',
		'page'      => 'permalink',
	);

	return $sections;
}

/**
 * Register section fields
 *
 * @param array $fields     Fields.
 *
 * @return array
 */
function snax_admin_settings_fields_permalinks( $fields ) {
	$fields['snax_permalinks'] = array(
		'snax_item_slug' => array(
			'title'             => __( 'Item url', 'snax' ),
			'callback'          => 'snax_permalink_callback_item_slug',
			'sanitize_callback' => 'sanitize_text',
			'args'              => array(),
		),
		'snax_url_var_prefix' => array(
			'title'             => __( 'URL variable', 'snax' ),
			'callback'          => 'snax_permalink_callback_url_var_prefix',
			'sanitize_callback' => 'sanitize_text',
			'args'              => array(),
		),
	);

	return $fields;
}

/**
 * Permalinks section description
 */
function snax_permalinks_section_description() {}

/**
 * Item post type slug
 */
function snax_permalink_callback_item_slug() {
	?>
	<code><?php echo esc_url( trailingslashit( home_url() ) ); ?></code>
	<input name="snax_item_slug" id="snax_item_slug" maxlength="20" type="text" value="<?php echo esc_attr( snax_get_item_slug() ) ?>" />
	<code>/sample-post/</code>
	<?php
}

/**
 * Prefix for all Snax url variables
 */
function snax_permalink_callback_url_var_prefix() {
	?>
	<input name="snax_url_var_prefix" id="snax_url_var_prefix" type="text" value="<?php echo esc_attr( snax_get_url_var_prefix() ) ?>" />
	<?php
}
