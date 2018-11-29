<?php
/**
 * Snax Item Metabox
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
function snax_add_item_metabox( $post_type, $post ) {
	if ( ! snax_is_item() ) {
		return;
	}

	add_meta_box(
		'snax_item',
		__( 'Snax', 'snax' ),
		'snax_item_metabox',
		$post_type,
		'normal',
		'default'
	);

	do_action( 'snax_register_item_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_item_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'snax_item',
		'snax_item_nonce'
	);

	$values = array(
		'source'    => get_post_meta( $post->ID, '_snax_source', true ),
		'ref_link'  => get_post_meta( $post->ID, '_snax_ref_link', true ),
	);

	// Field names.
	$field_names = array(
		'source'    => '_snax_source',
		'ref_link'  => '_snax_ref_link',
	);

	?>
	<div id="snax-metabox">
		<table class="form-table">
			<tbody>
			<?php if ( in_array( snax_get_item_format( $post ), array( 'image', 'audio', 'video' ), true ) ) : ?>
			<tr>
				<th scope="row">
					<label for="_snax_source">
						<?php esc_html_e( 'Source', 'snax' ); ?>
					</label>
				</th>
				<td>
					<input type="text"
					       class="code widefat"
					       id="<?php echo esc_attr( $field_names['source'] ) ?>"
					       name="<?php echo esc_attr( $field_names['source'] ) ?>"
					       value="<?php echo esc_attr( $values['source'] ); ?>"
					/>
				</td>
			</tr>
			<?php endif; ?>
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
function snax_save_item_metabox( $post_id ) {
	$nonce = filter_input( INPUT_POST, 'snax_item_nonce', FILTER_SANITIZE_STRING );

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
	if ( ! snax_is_item() ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'snax_item', 'snax_item_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'snax' ) );
	}

	$post_data = array();

	$source     = filter_input( INPUT_POST, '_snax_source', FILTER_SANITIZE_URL );
	$ref_link   = filter_input( INPUT_POST, '_snax_ref_link', FILTER_SANITIZE_URL );

	if ( $source ) {
		$post_data['_snax_source'] = $source;
	}

	if ( $ref_link ) {
		$post_data['_snax_ref_link'] = $ref_link;
	}

	$values = snax_sanitize_item_metabox_data( $post_data );

	foreach ( $values as $meta_key => $meta_value ) {
		update_post_meta( $post_id, $meta_key, $meta_value );
	}

	do_action( 'snax_save_item_metabox', $post_id );

	return $post_id;
}

/**
 * Sanitize metabox data
 *
 * @param array $data       Input data.
 *
 * @return array
 */
function snax_sanitize_item_metabox_data( $data ) {
	$sanitized = array();

	$sanitized['_snax_source']   = ! empty( $data['_snax_source'] ) ? esc_url_raw( $data['_snax_source'] ) : '';
	$sanitized['_snax_ref_link'] = ! empty( $data['_snax_ref_link'] ) ? esc_url_raw( $data['_snax_ref_link'] ) : '';

	return $sanitized;
}

