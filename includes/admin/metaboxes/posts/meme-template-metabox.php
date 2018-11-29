<?php
/**
 * Snax Meme Template Metabox
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
function snax_add_meme_template_metabox( $post_type, $post ) {
	if ( snax_get_meme_template_post_type() !== $post_type ) {
		return;
	}

	add_meta_box(
		'meme_template',
		__( 'Meme Template', 'snax' ),
		'snax_meme_template_metabox',
		$post_type,
		'side',
		'high'
	);

	do_action( 'snax_register_meme_template_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_meme_template_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'meme_template',
		'meme_template_nonce'
	);

	$values = array(
		'top_text'  => get_post_meta( $post->ID, '_snax_meme_template_top_text', true ),
		'bottom_text'  => get_post_meta( $post->ID, '_snax_meme_template_bottom_text', true ),
	);

	// Field names.
	$field_names = array(
		'top_text'  => '_snax_meme_template_top_text',
		'bottom_text'  => '_snax_meme_template_bottom_text',
	);

	?>
	<div id="snax-metabox">
		<p>
			<label for="_snax_ref_link">
				<?php esc_html_e( 'Top Text', 'snax' ); ?>
				<input type="text"
			       class="code widefat"
			       id="<?php echo esc_attr( $field_names['top_text'] ) ?>"
			       name="<?php echo esc_attr( $field_names['top_text'] ) ?>"
			       value="<?php echo esc_attr( $values['top_text'] ); ?>"
				/>
			</label>
		</p>
		<p>
			<label for="_snax_ref_link">
				<?php esc_html_e( 'Bottom Text', 'snax' ); ?>
				<input type="text"
			       class="code widefat"
			       id="<?php echo esc_attr( $field_names['bottom_text'] ) ?>"
			       name="<?php echo esc_attr( $field_names['bottom_text'] ) ?>"
			       value="<?php echo esc_attr( $values['bottom_text'] ); ?>"
				/>
			</label>
		</p>
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
function snax_save_meme_template_metabox( $post_id ) {

	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'meme_template_nonce', FILTER_SANITIZE_STRING );
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

	// Verify nonce.
	if ( ! check_admin_referer( 'meme_template', 'meme_template_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'snax' ) );
	}

	$post_data = array();
	$post_data['_snax_meme_template_top_text'] = filter_input( INPUT_POST, '_snax_meme_template_top_text', FILTER_SANITIZE_STRING );
	$post_data['_snax_meme_template_bottom_text'] = filter_input( INPUT_POST, '_snax_meme_template_bottom_text', FILTER_SANITIZE_STRING );

	foreach ( $post_data as $meta_key => $meta_value ) {
		update_post_meta( $post_id, $meta_key, $meta_value );
	}

	do_action( 'snax_save_meme_template_metabox', $post_id );

	return $post_id;
}

