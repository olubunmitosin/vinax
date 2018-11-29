<?php
/**
 * Snax Gallery Post Metabox
 *
 * @package snax
 * @subpackage Metaboxes
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Register metabox
 *
 * @param string  $post_type    Post type.
 * @param WP_Post $post         Post object.
 */
function snax_add_gallery_post_metabox( $post_type, $post ) {
	if ( ! snax_is_format( 'gallery', $post ) ) {
		return;
	}

	add_meta_box(
		'snax_gallery_post',
		__( 'Snax', 'snax' ),
		'snax_gallery_post_metabox',
		$post_type,
		'side',
		'high'
	);

	do_action( 'snax_register_gallery_post_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_gallery_post_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'snax_gallery_post',
		'snax_gallery_post_nonce'
	);

	$config_key = snax_get_post_config_key();
	$values = snax_get_post_config( $post );

	// Field names.
	$field_names = array(
		'items_per_page'        => sprintf( '%s[items_per_page]', $config_key ),
	);

	?>
	<div id="snax-metabox">
		<div id="snax-metabox-options">
			<p>
				<label>
					<?php esc_html_e( 'Items Per Page', 'snax' ); ?>
					<input type="text" id="<?php echo esc_attr( $field_names['items_per_page'] ) ?>"
					       name="<?php echo esc_attr( $field_names['items_per_page'] ) ?>"
					       value="<?php echo esc_attr( $values['items_per_page'] ); ?>"/>
				</label>
				<br />

				<span class="description"><?php printf( wp_kses_post( __( 'Leave empty to use <a href="%s">global settings</a>.', 'snax' ) ), esc_url( admin_url( snax_admin()->settings_page . '?page=snax-general-settings' ) ) ); ?></span>
			</p>
		</div>
	</div>
<?php
}

/**
 * Save metabox data
 *
 * @param int $post_id      Post id.
 *
 * @return mixed
 */
function snax_save_gallery_post_metabox( $post_id ) {
	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'snax_gallery_post_nonce', FILTER_SANITIZE_STRING );

	if ( ! $nonce ) {
		return $post_id;
	}

	// Don't save data automatically via autosave feature.
	if ( snax_is_doing_autosave() ) {
		return $post_id;
	}

	// Don't save data when doing preview.
	if ( snax_is_doing_preview() ) {
		return $post_id;
	}

	// Don't save data when using Quick Edit.
	if ( snax_is_inline_edit() ) {
		return $post_id;
	}

	// Update options only if they are applicable.
	if ( ! snax_is_format( 'gallery', $post_id ) ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'snax_gallery_post', 'snax_gallery_post_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'snax' ) );
	}

	$config_key = snax_get_post_config_key();

	$post_data_arr = filter_input_array( INPUT_POST, array(
		$config_key => array(
			'filter'    => FILTER_SANITIZE_STRING,
			'flags'     => FILTER_REQUIRE_ARRAY,
		),
	) );

	$post_data = $post_data_arr[ $config_key ];

	if ( ! is_null( $post_data ) ) {
		$values = snax_sanitize_gallery_post_metabox_data( $post_data );

		snax_set_post_config( $post_id, $values );
	}

	do_action( 'snax_save_gallery_post_metabox', $post_id );

	return $post_id;
}

/**
 * Sanitize metabox data
 *
 * @param array $data       Input data.
 *
 * @return array
 */
function snax_sanitize_gallery_post_metabox_data( $data ) {
	// Return new array with allowed values only, relaying on $data fields is not safe.
	$sanitized = array();

	$sanitized['items_per_page'] = ! empty( $data['items_per_page'] ) ? absint( $data['items_per_page'] ) : '';

	return $sanitized;
}

