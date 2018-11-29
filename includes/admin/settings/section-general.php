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
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_general' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_general' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_general( $sections ) {
	$sections['snax_settings_general'] = array(
		'title'    => __( 'Frontend Submission', 'snax' ),
		'callback' => 'snax_admin_settings_general_section_description',
		'page'      => 'snax-general-settings',
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
function snax_admin_settings_fields_general( $fields ) {
	$fields['snax_settings_general'] = array(
		'snax_active_formats' => array(
			'title'             => __( 'Active formats', 'snax' ) . '<br /><span style="font-weight: normal;">' . __( '(drag to reorder)', 'snax' ) . '</span>',
			'callback'          => 'snax_admin_setting_callback_active_formats',
			'sanitize_callback' => 'snax_sanitize_text_array',
			'args'              => array(),
		),
		'snax_formats_order' => array(
			'sanitize_callback' => 'sanitize_text_field',
		),
		'snax_misc_header' => array(
			'title'             => '<h2>' . __( 'Misc', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_show_origin' => array(
			'title'             => __( 'Show the "This post was created with our nice and easy submission form." text', 'snax' ),
			'callback'          => 'snax_admin_setting_show_origin',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_disable_admin_bar' => array(
			'title'             => __( 'Disable admin bar for non-administrators', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_disable_admin_bar',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_disable_dashboard_access' => array(
			'title'             => __( 'Disable Dashboard access for non-administrators', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_disable_dashboard_access',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_disable_wp_login' => array(
			'title'             => __( 'Disable WP login form', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_disable_wp_login',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_enable_login_popup' => array(
			'title'             => __( 'Enable the login popup ', 'snax' ),
			'callback'          => 'snax_admin_setting_enable_login_popup',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_skip_verification' => array(
			'title'             => __( 'Moderate new posts?', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_skip_verification',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_mail_notifications' => array(
			'title'             => __( 'Send mail to admin when new post/item was added?', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_mail_notifications',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_froala_for_items' => array(
			'title'             => __( 'Allow rich editor for description fields', 'snax' ),
			'callback'          => 'snax_admin_setting_froala_for_items',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_limits_header' => array(
			'title'             => '<h2>' . __( 'Limits', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_user_posts_per_day' => array(
			'title'             => __( 'User can submit', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_new_posts_limit',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_new_post_items_limit' => array(
			'title'             => __( 'User can submit', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_new_post_items_limit',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_user_submission_limit' => array(
			'title'             => __( 'User can submit', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_user_submission_limit',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_tags_limit' => array(
			'title'             => __( 'Tags', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_tags_limit',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_post_title_max_length' => array(
			'title'             => __( 'Title length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_post_title_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_post_description_max_length' => array(
			'title'             => __( 'Description length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_post_description_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
	);

	return $fields;
}

function snax_admin_general_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ) ?></h1>
		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'General', 'snax' ) ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'snax-general-settings' ); ?>
			<?php do_settings_sections( 'snax-general-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>
		</form>
	</div>

	<?php
}

/**
 * Render general section description
 */
function snax_admin_settings_general_section_description() {}

/**
 * Formats
 */
function snax_admin_setting_callback_active_formats() {
	$formats = snax_get_formats();
	$active_formats_ids = snax_get_active_formats_ids();
	?>
	<div id="snax-settings-active-formats">
	<?php
	foreach ( $formats as $format_id => $format_args ) {
		$checkbox_id = 'snax_active_format_' . $format_id;
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $checkbox_id ); ?>">
				<input name="snax_active_formats[]" id="<?php echo esc_attr( $checkbox_id ); ?>" type="checkbox" value="<?php echo esc_attr( $format_id ); ?>" <?php checked( in_array( $format_id, $active_formats_ids, true ) , true ); ?> /> <?php echo esc_html( $format_args['labels']['name'] ); ?>
			</label>
		</fieldset>
		<?php
	}
	?>
	</div>
	<input name="snax_formats_order" id="snax_formats_order" type="hidden" value="<?php echo esc_attr( implode( ',', snax_get_formats_order() ) ); ?>">
	<?php
}

/**
 * Items per page
 */
function snax_admin_setting_callback_items_per_page() {
	?>
	<input name="snax_items_per_page" id="snax_items_per_page" type="number" size="5" value="<?php echo esc_attr( snax_get_global_items_per_page() ); ?>" />
	<?php
}

/**
 * Upload allowed
 *
 * @param array $args           Arguments.
 */
function snax_admin_setting_callback_upload_allowed( $args ) {
	$media_type = $args['type'];

	$setting_id = 'snax_' . $media_type . '_upload_allowed';
	$is_checked = call_user_func( 'snax_is_' . $media_type . '_upload_allowed' );

	$rel_settings = array(
		'image' === $media_type ? '#snax_max_upload_size' : '#snax_' . $media_type . '_max_upload_size',
		'[name^=snax_' . $media_type . '_allowed_types]',
	);
	?>
	<input class="snax-hide-rel-settings" data-snax-rel-settings="<?php echo esc_attr( implode( ',', $rel_settings ) ); ?>" name="<?php echo esc_attr( $setting_id ); ?>" id="<?php echo esc_attr( $setting_id ); ?>" type="checkbox" <?php checked( $is_checked ); ?> value="standard" />
	<?php
}

/**
 * Max. image upload size
 *
 * @param array $args          Arguments.
 */
function snax_admin_setting_callback_upload_max_size( $args ) {
	$media_type = $args['type'];

	$bytes_1mb = 1024 * 1024;

	$max_upload_size = call_user_func( 'snax_get_' . $media_type . '_max_upload_size' );
	$limit = wp_max_upload_size();

	$choices = array(
		$bytes_1mb => '1MB',
	);

	if ( $limit > $bytes_1mb  ) {
		// Iterate each 2MB.
		for ( $i = 2 * $bytes_1mb; $i <= $limit; $i += 2 * $bytes_1mb ) {
			$choices[ $i ] = ( $i / $bytes_1mb ) . 'MB';
		}
	}

	// Max limit not included?
	if ( ! isset( $choices[ $limit ] ) ) {
		$choices[ $limit ] = ( $limit / $bytes_1mb ) . 'MB';
	}

	$choices = apply_filters( 'snax_max_upload_size_choices', $choices, $media_type );

	$setting_id = 'image' === $media_type ? 'snax_max_upload_size' : 'snax_' . $media_type . '_max_upload_size';
	?>
	<select name="<?php echo esc_attr( $setting_id ); ?>" id="<?php echo esc_attr( $setting_id ); ?>">
		<?php foreach ( $choices as $value => $label ) : ?>
		<option value="<?php echo esc_attr( $value ); ?>"<?php selected( $max_upload_size, $value ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<span><?php printf( esc_html__( 'Maximum size can be set to %dMB, which is your server\'s upload limit (set in php.ini).', 'snax' ), absint( $limit / $bytes_1mb ) ); ?></span>
	<?php
}

/**
 * Allowed upload types
 *
* @param array $args                Arguments.
 */
function snax_admin_setting_callback_upload_allowed_types( $args ) {
	$media_type = $args['type'];

	$setting_id = 'snax_' . $media_type . '_allowed_types';
	$all_types  = call_user_func( 'snax_get_all_' . $media_type . '_allowed_types' );
	$checked    = call_user_func( 'snax_get_' . $media_type . '_allowed_types' );

	foreach ( $all_types as $type ) {
		$field_id = $setting_id . '_' . $type;
		?>
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<input name="<?php echo esc_attr( $setting_id ); ?>[]" id="<?php echo esc_attr( $field_id ); ?>" type="checkbox" value="<?php echo esc_attr( $type ); ?>"<?php checked( in_array( $type, $checked ) ); ?> /> <?php echo esc_html( $type ); ?>
		</label>
		<?php
	}
}

/**
 * How many new posts user can submit, per day.
 */
function snax_admin_setting_callback_new_posts_limit() {
	$limit = snax_get_user_posts_per_day();
	?>

	<select name="snax_user_posts_per_day" id="snax_user_posts_per_day">
		<option value="1" <?php selected( 1, $limit ) ?>><?php esc_html_e( 'only 1 post', 'snax' ) ?></option>
		<option value="10" <?php selected( 10, $limit ) ?>><?php esc_html_e( '10 posts', 'snax' ) ?></option>
		<option value="-1" <?php selected( -1, $limit ) ?>><?php esc_html_e( 'unlimited posts', 'snax' ) ?></option>
	</select>
	<span><?php esc_html_e( 'per day.', 'snax' ); ?></span>
	<?php
}

/**
 * How many new items user can submit to a new post (during creation).
 */
function snax_admin_setting_callback_new_post_items_limit() {
	$limit = snax_get_new_post_items_limit();
	?>

	<select name="snax_new_post_items_limit" id="snax_new_post_items_limit">
		<option value="10" <?php selected( 10, $limit ) ?>><?php esc_html_e( '10 items', 'snax' ) ?></option>
		<option value="20" <?php selected( 20, $limit ) ?>><?php esc_html_e( '20 items', 'snax' ) ?></option>
		<option value="-1" <?php selected( -1, $limit ) ?>><?php esc_html_e( 'unlimited items', 'snax' ) ?></option>
	</select>
	<span><?php esc_html_e( 'while creating a new list/gallery. Applies also to Story format images.', 'snax' ); ?></span>
	<?php
}

/**
 * Disable admin bar
 */
function snax_admin_setting_callback_disable_admin_bar() {
	?>
	<input name="snax_disable_admin_bar" id="snax_disable_admin_bar" type="checkbox" <?php checked( snax_disable_admin_bar() ); ?> />
	<?php
}

/**
 * Disable dashboard access
 */
function snax_admin_setting_callback_disable_dashboard_access() {
	?>
	<input name="snax_disable_dashboard_access" id="snax_disable_dashborad_access" type="checkbox" <?php checked( snax_disable_dashboard_access() ); ?> />
	<?php
}

/**
 * Disable WP login form
 */
function snax_admin_setting_callback_disable_wp_login() {
	?>
	<input name="snax_disable_wp_login" id="snax_disable_wp_login" type="checkbox" <?php checked( snax_disable_wp_login() ); ?> />
	<?php
}

/**
 * Facebook App Id
 */
function snax_admin_setting_callback_facebook_app_id() {
	?>
	<input name="snax_facebook_app_id" id="snax_facebook_app_id" class="regular-text" type="number" size="5" value="<?php echo esc_attr( snax_get_facebook_app_id() ); ?>" />
	<p class="description">
	<?php echo wp_kses_post( sprintf( __( 'How do I get my <strong>App ID</strong>? Use the <a href="%s" target="_blank">Register and Configure an App</a> guide for help.', 'snax' ), esc_url( 'https://developers.facebook.com/docs/apps/register' ) ) ); ?>
	</p>
	<?php
}

/**
 * Whether to allow user direct publishing
 */
function snax_admin_setting_callback_skip_verification() {
	// The label of the option was changed to Moderate new post? from Skip verification, so "yes" and "no" were inverted in labels here.
	$skip = snax_skip_verification();
	?>

	<select name="snax_skip_verification" id="snax_skip_verification">
		<option value="standard" <?php selected( $skip, true ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
		<option value="none" <?php selected( $skip, false ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Whether to send mail to admin when new post/item was added
 */
function snax_admin_setting_callback_mail_notifications() {
	$mail = snax_mail_notifications();
	?>

	<select name="snax_mail_notifications" id="snax_mail_notifications">
		<option value="standard" <?php selected( $mail, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $mail, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Wheter to show "This post was created with our nice and easy submission form."
 */
function snax_admin_setting_show_origin() {
	$origin = snax_show_origin();
	?>

	<select name="snax_show_origin" id="snax_show_origin">
		<option value="standard" <?php selected( $origin, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $origin, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Wheter to allow Froala in items
 */
function snax_admin_setting_froala_for_items() {
	$froala_for_items = snax_froala_for_items();
	?>

	<select name="snax_froala_for_items" id="snax_froala_for_items">
		<option value="standard" <?php selected( $froala_for_items, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $froala_for_items, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
			<?php
}

/**
 * Wheter to enable the login popup
 */
function snax_admin_setting_enable_login_popup() {
	$enable_login_popup = snax_enable_login_popup();
	?>

	<select name="snax_enable_login_popup" id="snax_enable_login_popup">
		<option value="standard" <?php selected( $enable_login_popup, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $enable_login_popup, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Demo post
 *
 * @param array $args			Renderer config.
 */
function snax_admin_setting_callback_demo_post( $args ) {
	$format = $args['format'];
	$selected_post_id = snax_get_demo_post_id( $format );
	$select_name = sprintf( 'snax_demo_%s_post_id', $format );

	$posts = get_posts( array(
		'posts_per_page'   => -1,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'post_status'      => 'any',
		'tax_query'		 => array(
			array(
				'taxonomy' 	=> snax_get_snax_format_taxonomy_slug(),
				'field' 	=> 'slug',
				'terms' 	=> 'meme' === $format ? 'image' : $format,
			),
		),
	) );
	?>
	<select name="<?php echo esc_attr( $select_name ) ?>" id="<?php echo esc_attr( $select_name ); ?>">
		<option value=""><?php esc_html_e( '- None -', 'snax' ) ?></option>

		<?php foreach( $posts as $post ) : ?>
			<option class="level-0" value="<?php echo intval( $post->ID ) ?>" <?php selected( $post->ID, $selected_post_id ); ?>><?php echo esc_html( get_the_title( $post ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php

	if ( ! empty( $selected_post_id ) ) :
		?>
		<a href="<?php echo esc_url( get_permalink( $selected_post_id ) ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'View', 'snax' ); ?></a>
		<?php
	endif;

	if ( 'meme' === $format ) {
		esc_html_e( 'Choose an Image post', 'snax' );
	}
}

/**
 * Demo posts
 *
 * @param array $args			Renderer config.
 */
function snax_admin_setting_callback_demo_posts( $args ) {
	$format             = $args['format'];
	$selected_post_ids  = snax_get_demo_post_ids( $format );
	$select_name        = sprintf( 'snax_demo_%s_post_ids', $format );

	$posts = get_posts( array(
		'posts_per_page'   => -1,
		'orderby'          => 'title',
		'order'            => 'ASC',
		'post_status'      => 'any',
		'tax_query'		 => array(
			array(
				'taxonomy' 	=> snax_get_snax_format_taxonomy_slug(),
				'field' 	=> 'slug',
				'terms' 	=> 'meme' === $format ? 'image' : $format,
			),
		),
	) );
	?>
	<select size="10" name="<?php echo esc_attr( $select_name ) ?>[]" id="<?php echo esc_attr( $select_name ); ?>" multiple="multiple">
		<option value="" <?php selected( in_array( '', $selected_post_ids, true ) ); ?>><?php esc_html_e( '- None -', 'snax' ) ?></option>

		<?php foreach( $posts as $post ) : ?>
			<option class="level-0" value="<?php echo intval( $post->ID ) ?>" <?php selected( in_array( $post->ID, $selected_post_ids ) ); ?>><?php echo esc_html( get_the_title( $post ) ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

	/**
 * Tags limit
 */
function snax_admin_setting_callback_tags_limit() {
	?>
	<input name="snax_tags_limit" id="snax_tags_limit" type="number" size="5" value="<?php echo esc_attr( snax_get_tags_limit() ); ?>" />
	<p class="description"><?php esc_html_e( 'Maximum number of tags user can assign to a post during new submission.', 'snax' ); ?></p>
	<?php
}

/**
 * Post title max. length
 */
function snax_admin_setting_callback_post_title_max_length() {
	?>
	<input name="snax_post_title_max_length" id="snax_post_title_max_length" type="number" size="5" value="<?php echo esc_attr( snax_get_post_title_max_length() ); ?>" />
	<?php
}

/**
 * Post description max. length
 */
function snax_admin_setting_callback_post_description_max_length() {
	?>
	<input name="snax_post_description_max_length" id="snax_post_description_max_length" type="number" size="5" value="<?php echo esc_attr( snax_get_post_description_max_length() ); ?>" />
	<?php
}

/**
 * User can submit (limit)
 */
function snax_admin_setting_callback_user_submission_limit() {
	$limit = snax_get_user_submission_limit();
	?>

	<select name="snax_user_submission_limit" id="snax_user_submission_limit">
		<option value="1" <?php selected( 1, $limit ) ?>><?php esc_html_e( 'only 1 item', 'snax' ) ?></option>
		<option value="-1" <?php selected( -1, $limit ) ?>><?php esc_html_e( 'unlimited items', 'snax' ) ?></option>
	</select>
	<span><?php esc_html_e( 'to an existing list.', 'snax' ); ?></span>
	<?php
}