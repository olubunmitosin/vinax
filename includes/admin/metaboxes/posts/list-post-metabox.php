<?php
/**
 * Snax List Post Metabox
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
function snax_add_list_post_metabox( $post_type, $post ) {
	if ( ! in_array( get_post_type( $post ), apply_filters( 'snax_open_list_metabox_supported_post_types', array( 'post' ) ), true ) ) {
		return;
	}

	// Skip if a post is one of Snax formats but not "List".
	if ( snax_is_format( null, $post )  && ! snax_is_format( 'text', $post ) && ! snax_is_format( 'list', $post ) ) {
		return;
	}

	add_meta_box(
		'snax_list_post',
		__( 'Snax', 'snax' ),
		'snax_list_post_metabox',
		$post_type,
		'side',
		'high'
	);

	do_action( 'snax_register_list_post_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_list_post_metabox( $post ) {
	?>
	<style>
		.snax-metabox-actions {
			text-align: right;
		}

		.snax-metabox-section {
			margin: 0 -12px 1em;
			padding: 0 12px;
			border-bottom: 1px solid #eee;
		}

		.snax-metabox-section > div {
			padding-left: 24px;
			padding-bottom: 1em;
		}
	</style>
	<?php if ( ! snax_is_format( 'list' ) ) : ?>
		<div class="snax-metabox-actions">
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'snax_action' => 'convert_to_list', 'snax_post' => $post->ID ) ) )?>"><?php echo esc_html( __( 'Make this post a list', 'snax' ) ); ?></a>
		</div>
		<?php return; ?>
	<?php endif; ?>
	<?php
	// Secure the form with nonce field.
	wp_nonce_field(
		'snax_list_post',
		'snax_list_post_nonce'
	);

	$config_key = snax_get_post_config_key();
	$values = snax_get_post_config( $post );

	// Field names.
	$field_names = array(
		'submission' 			=> sprintf( '%s[submission]', $config_key ),
		'submission_start_date' => sprintf( '%s[submission_start_date]', $config_key ),
		'submission_end_date'   => sprintf( '%s[submission_end_date]', $config_key ),
		'submission_close_limit'=> sprintf( '%s[submission_close_limit]', $config_key ),
		'voting'     			=> sprintf( '%s[voting]', $config_key ),
		'voting_start_date'     => sprintf( '%s[voting_start_date]', $config_key ),
		'voting_end_date'       => sprintf( '%s[voting_end_date]', $config_key ),
		'override_forms'        => sprintf( '%s[override_forms]', $config_key ),
		'forms'                 => sprintf( '%s[forms]', $config_key ),
		'items_per_page'        => sprintf( '%s[items_per_page]', $config_key ),
	);

	?>
	<div id="snax-metabox">
		<div id="snax-metabox-options">

			<div class="snax-metabox-section">
				<p>
					<label>
						<input type="checkbox" id="snax-open-list"
							   name="<?php echo esc_attr( $field_names['submission'] ) ?>"
							   value="standard" <?php checked( $values['submission'], 'standard' ); ?> />
						<?php esc_html_e( 'Open list', 'snax' ); ?>
					</label>
				</p>

				<div id="snax-open-list-options" class="<?php echo 'snax-forms-visibility-' . sanitize_html_class( $values['submission'] ); ?>">
					<p>
						<label>
							<?php esc_html_e( 'Submission Start Date', 'snax' ); ?>
							<input type="text" class="snax-datepicker"
								   id="<?php echo esc_attr( $field_names['submission_start_date'] ) ?>"
								   name="<?php echo esc_attr( $field_names['submission_start_date'] ) ?>"
								   value="<?php echo esc_attr( $values['submission_start_date'] ); ?>"/>
						</label>
					</p>

					<p>
						<label>
							<?php esc_html_e( 'Submission End Date', 'snax' ); ?>
							<input type="text" class="snax-datepicker" id="<?php echo esc_attr( $field_names['submission_end_date'] ) ?>"
								   name="<?php echo esc_attr( $field_names['submission_end_date'] ) ?>"
								   value="<?php echo esc_attr( $values['submission_end_date'] ); ?>"/>
							<a href="#" class="hide-if-no-js button button-small snax-set-current-date"><?php esc_html_e( 'Close Submission', 'snax' ); ?></a>
						</label>
					</p>

					<p>
						<label>
							<?php esc_html_e( 'Close submission automatically after', 'snax' ); ?>
							<input type="text" id="<?php echo esc_attr( $field_names['submission_close_limit'] ) ?>" size="5"
							       name="<?php echo esc_attr( $field_names['submission_close_limit'] ) ?>"
							       value="<?php echo esc_attr( $values['submission_close_limit'] ); ?>"/>
							<?php esc_html_e( 'published items.', 'snax' ); ?>
						</label>
					</p>

					<?php $forms = snax_get_registered_item_forms(); ?>

					<?php if ( ! empty( $forms ) ) : ?>
						<p>
							<label>
								<input type="checkbox" class="snax-forms-toogle" id="<?php echo esc_attr( $field_names['override_forms'] ); ?>"
									   name="<?php echo esc_attr( $field_names['override_forms'] ) ?>"
									   value="standard" <?php checked( $values['override_forms'], 'standard' ); ?> />
								<?php esc_html_e( 'Change default forms', 'snax' ); ?>
							</label>
						</p>
						<p id="snax-metabox-options-forms" class="<?php echo 'snax-forms-visibility-' . sanitize_html_class( $values['override_forms'] ); ?>">
							<?php foreach ( $forms as $form_id => $form_data ) : ?>
								<label>
									<input type="checkbox" id="<?php echo esc_attr( $field_names['forms'] ) . '_' . esc_attr( $form_id ); ?>"
										   name="<?php echo esc_attr( $field_names['forms'] ) ?>[]"
										   value="<?php echo esc_attr( $form_id ); ?>" <?php checked( in_array( $form_id, $values['forms'], true ), true ) ?> />
									<?php echo esc_html( $form_data['labels']['name'] ); ?>
								</label>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="snax-metabox-section">
				<p>
					<label>
						<input type="checkbox" id="snax-ranked-list"
							   name="<?php echo esc_attr( $field_names['voting'] ) ?>"
							   value="standard" <?php checked( $values['voting'], 'standard' ); ?> />
						<?php esc_html_e( 'Ranked list', 'snax' ); ?>
					</label>
				</p>

				<div id="snax-ranked-list-options" class="<?php echo 'snax-forms-visibility-' . sanitize_html_class( $values['voting'] ); ?>">
					<p>
						<label>
							<?php esc_html_e( 'Voting Start Date', 'snax' ); ?>
							<input type="text" class="snax-datepicker" id="<?php echo esc_attr( $field_names['voting_start_date'] ) ?>"
								   name="<?php echo esc_attr( $field_names['voting_start_date'] ) ?>"
								   value="<?php echo esc_attr( $values['voting_start_date'] ); ?>"/>
						</label>
					</p>

					<p>
						<label>
							<?php esc_html_e( 'Voting End Date', 'snax' ); ?>
							<input type="text" class="snax-datepicker" id="<?php echo esc_attr( $field_names['voting_end_date'] ) ?>"
								   name="<?php echo esc_attr( $field_names['voting_end_date'] ) ?>"
								   value="<?php echo esc_attr( $values['voting_end_date'] ); ?>"/>
							<a href="#" class="hide-if-no-js button button-small snax-set-current-date"><?php esc_html_e( 'Close Voting', 'snax' ); ?></a>
						</label>
					</p>
				</div>
			</div>

			<p>
				<label>
					<?php esc_html_e( 'Items per page', 'snax' ); ?>:
					<input type="text" id="<?php echo esc_attr( $field_names['items_per_page'] ) ?>"
					       name="<?php echo esc_attr( $field_names['items_per_page'] ) ?>"
					       value="<?php echo esc_attr( $values['items_per_page'] ); ?>"
					       size="5"
					/>
				</label>
				<br />

				<span class="description"><?php printf( wp_kses_post( __( 'Leave empty to use <a href="%s" target="_blank">global settings</a>.', 'snax' ) ), esc_url( admin_url( snax_admin()->settings_page . '?page=snax-general-settings' ) ) ); ?></span>
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
function snax_save_list_post_metabox( $post_id ) {
	// Nonce sent?
	$nonce = filter_input( INPUT_POST, 'snax_list_post_nonce', FILTER_SANITIZE_STRING );

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
	if ( ! snax_is_format( 'list', $post_id ) ) {
		return $post_id;
	}

	$post_type = filter_input( INPUT_POST, 'post_type', FILTER_SANITIZE_STRING );

	// Check permissions.
	$post_type_obj = get_post_type_object( $post_type );

	if ( ! current_user_can( $post_type_obj->cap->edit_post, $post_id ) ) {
		return $post_id;
	}

	// Verify nonce.
	if ( ! check_admin_referer( 'snax_list_post', 'snax_list_post_nonce' ) ) {
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
		$values = snax_sanitize_list_post_metabox_data( $post_data );

		// Fallback start dates to post created date, if not set.
		$post = get_post( $post_id );

		if ( empty( $values['submission_start_date'] ) ) {
			$values['submission_start_date'] = $post->post_date;
		}

		if ( empty( $values['voting_start_date'] ) ) {
			$values['voting_start_date'] = $post->post_date;
		}

		snax_set_post_config( $post_id, $values );
	}

	do_action( 'snax_save_list_post_metabox', $post_id );

	return $post_id;
}

/**
 * Sanitize metabox data
 *
 * @param array $data       Input data.
 *
 * @return array
 */
function snax_sanitize_list_post_metabox_data( $data ) {
	// Return new array with allowed values only, relaying on $data fields is not safe.
	$sanitized = array();

	$sanitized['submission'] 			= ! empty( $data['submission'] ) ? snax_sanitize_checkbox( $data['submission'] ) : 'none';
	$sanitized['submission_start_date'] = ! empty( $data['submission_start_date'] ) ? snax_sanitize_datetime( $data['submission_start_date'] ) : '';
	$sanitized['submission_end_date']   = ! empty( $data['submission_end_date'] ) ? snax_sanitize_datetime( $data['submission_end_date'] ) : '';
	$sanitized['submission_close_limit']= ! empty( $data['submission_close_limit'] ) ? absint( $data['submission_close_limit'] ) : '';
	$sanitized['voting'] 				= ! empty( $data['voting'] ) ? snax_sanitize_checkbox( $data['voting'] ) : 'none';
	$sanitized['voting_start_date']     = ! empty( $data['voting_start_date'] ) ? snax_sanitize_datetime( $data['voting_start_date'] ) : '';
	$sanitized['voting_end_date']       = ! empty( $data['voting_end_date'] ) ? snax_sanitize_datetime( $data['voting_end_date'] ) : '';
	$sanitized['override_forms']        = ! empty( $data['override_forms'] ) ? snax_sanitize_checkbox( $data['override_forms'] ) : 'none';
	$sanitized['forms']                 = ! empty( $data['forms'] ) ? snax_sanitize_new_item_forms( $data['forms'] ) : array();
	$sanitized['items_per_page']        = ! empty( $data['items_per_page'] ) ? absint( $data['items_per_page'] ) : '';

	return $sanitized;
}

