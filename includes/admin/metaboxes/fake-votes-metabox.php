<?php
/**
 * Snax Fake Votes Metabox
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
function snax_add_fake_votes_metabox( $post_type, $post ) {
	$allowed_post_types	= snax_voting_get_post_types();

	$bool = snax_voting_is_enabled() && in_array( $post_type, $allowed_post_types, true ) && 'snax_item' !== $post_type;

	if ( ! apply_filters( 'snax_show_fake_votes_metabox', $bool, $post_type ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	add_meta_box(
		'snax_fake_votes',
		__( 'Fake Votes', 'snax' ),
		'snax_fake_votes_metabox',
		$post_type,
		'normal'
	);

	do_action( 'snax_register_fake_votes_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_fake_votes_metabox( $post ) {
	// Secure the form with nonce field.
	wp_nonce_field(
		'snax_fake_votes',
		'snax_fake_votes_nonce'
	);

	$value = get_post_meta( $post->ID, '_snax_fake_vote_count', true );
	?>
	<div id="snax-metabox">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row">
					<label for="_snax_fake_vote_count">
						<?php esc_html_e( 'Fake vote count', 'snax' ); ?>
					</label>
				</th>
				<td>
					<input type="number" id="_snax_fake_vote_count" name="_snax_fake_vote_count" value="<?php echo esc_attr( $value ) ?>" size="5" />
					<span class="description"><?php printf( wp_kses_post( __( 'Leave empty to use <a href="%s" target="_blank">global settings</a> or use a positive number (inclusive 0) to override them.', 'snax' ) ), esc_url( admin_url( snax_admin()->settings_page . '?page=snax-voting-settings' ) ) ); ?></span>
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
function snax_save_fake_votes_metabox( $post_id ) {
	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'snax_fake_votes_nonce', FILTER_SANITIZE_STRING );

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

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'snax_fake_votes', 'snax_fake_votes_nonce' ) ) {
		wp_die( esc_html__( 'Nonce incorrect!', 'snax' ) );
	}

	$vote_count = filter_input( INPUT_POST, '_snax_fake_vote_count', FILTER_SANITIZE_STRING );

	// Sanitize if not empty.
	if ( ! empty( $vote_count ) ) {
		$vote_count = absint( $vote_count );
	}

	update_post_meta( $post_id, '_snax_fake_vote_count', $vote_count );

	do_action( 'snax_save_list_post_metabox', $post_id );

	return $post_id;
}
