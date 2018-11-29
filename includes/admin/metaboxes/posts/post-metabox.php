<?php
/**
 * Snax Post Metabox
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
function snax_add_post_metabox( $post_type, $post ) {
	$format = snax_get_format( $post );
	$meta = get_post_meta( $post->ID, '_snax_ref_link' );

	if ( ! $meta && ! in_array( $format, array( 'image', 'embed', 'text' ), true ) ) {
		return;
	}

	add_meta_box(
		'snax_post',
		__( 'Snax', 'snax' ),
		'snax_post_metabox',
		$post_type,
		'normal',
		'default'
	);

	do_action( 'snax_register_post_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_post_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'snax_post',
		'snax_post_nonce'
	);

	$values = array(
		'ref_link'  => get_post_meta( $post->ID, '_snax_ref_link', true ),
	);

	// Field names.
	$field_names = array(
		'ref_link'  => '_snax_ref_link',
	);

	?>
	<div id="snax-metabox">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="_snax_ref_link">
						<?php esc_html_e( 'Referral link', 'snax' ); ?>
					</label>
				</th>
				<td>
					<input type="text"
					       class="code widefat"
					       id="<?php echo esc_attr( $field_names['ref_link'] ) ?>"
					       name="<?php echo esc_attr( $field_names['ref_link'] ) ?>"
					       value="<?php echo esc_attr( $values['ref_link'] ); ?>"
					/>
				</td>
			</tr>
			</tbody>
		</table>
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
function snax_save_post_metabox( $post_id ) {
	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'snax_post_nonce', FILTER_SANITIZE_STRING );

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
	$format = snax_get_format( $post_id );

	if ( ! in_array( $format, array( 'image', 'embed', 'text' ), true ) ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'snax_post', 'snax_post_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'snax' ) );
	}

	$post_data = array();

	$ref_link = filter_input( INPUT_POST, '_snax_ref_link', FILTER_SANITIZE_URL );

	if ( $ref_link ) {
		$post_data['_snax_ref_link'] = $ref_link;
	}

	$values = snax_sanitize_post_metabox_data( $post_data );

	foreach ( $values as $meta_key => $meta_value ) {
		update_post_meta( $post_id, $meta_key, $meta_value );
	}

	do_action( 'snax_save_post_metabox', $post_id );

	return $post_id;
}

/**
 * Sanitize metabox data
 *
 * @param array $data       Input data.
 *
 * @return array
 */
function snax_sanitize_post_metabox_data( $data ) {
	$sanitized = array();

	$sanitized['_snax_ref_link'] = ! empty( $data['_snax_ref_link'] ) ? esc_url_raw( $data['_snax_ref_link'] ) : '';

	return $sanitized;
}

