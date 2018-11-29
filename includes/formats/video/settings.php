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
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_videos' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_videos' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_videos( $sections ) {
	$sections['snax_settings_videos'] = array(
		'title'    => __( 'Videos', 'snax' ),
		'callback' => 'snax_admin_settings_videos_section_description',
		'page'      => 'snax-videos-settings',
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
function snax_admin_settings_fields_videos( $fields ) {
	$fields['snax_settings_videos'] = array(

		/* Frontend Form */

		'snax_video_frontend_form_header' => array(
			'title'             => '<h2>' . __( 'Frontend Form', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_video_featured_media_field' => array(
			'title'             => __( 'Featured Image', 'snax' ),
			'callback'          => 'snax_admin_setting_video_featured_media_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_video_category_field' => array(
			'title'             => __( 'Category', 'snax' ),
			'callback'          => 'snax_admin_setting_video_category_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_video_category_multi' => array(
			'title'             => __( 'Multiple categories selection?', 'snax' ),
			'callback'          => 'snax_admin_setting_video_category_multi',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_video_category_whitelist' => array(
			'title'             => __( 'Category whitelist', 'snax' ),
			'callback'          => 'snax_admin_setting_video_category_whitelist',
			'sanitize_callback' => 'snax_sanitize_category_whitelist',
			'args'              => array(),
		),
		'snax_video_category_auto_assign' => array(
			'title'             => __( 'Auto assign to categories', 'snax' ),
			'callback'          => 'snax_admin_setting_video_category_auto_assign',
			'sanitize_callback' => 'snax_sanitize_category_whitelist',
			'args'              => array(),
		),
		'snax_video_allow_snax_authors_to_add_referrals' => array(
			'title'             => __( 'Referral link', 'snax' ),
			'callback'          => 'snax_admin_setting_video_allow_snax_authors_to_add_referrals',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),

		/* Single Post */

		'snax_video_single_post_header' => array(
			'title'             => '<h2>' . __( 'Single Post', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),

		'snax_video_show_featured_media' => array(
			'title'             => __( 'Show Featured Media', 'snax' ),
			'callback'          => 'snax_admin_setting_video_show_featured_media',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),

		/* Limits */

		'snax_limits_video_header' => array(
			'title'             => '<h2>' . __( 'Limits', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_video_upload_allowed' => array(
			'title'             => __( 'Allowed?', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_upload_allowed',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array( 'type' => 'video' ),
		),
		'snax_video_max_upload_size' => array(
			'title'             => __( 'Maximum file size', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_upload_max_size',
			'sanitize_callback' => 'intval',
			'args'              => array( 'type' => 'video' ),
		),
		'snax_video_allowed_types' => array(
			'title'             => __( 'Allowed types', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_upload_allowed_types',
			'sanitize_callback' => 'snax_sanitize_text_array',
			'args'              => array( 'type' => 'video' ),
		),

	);

	return $fields;
}

function snax_admin_videos_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ) ?></h1>
		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'Videos', 'snax' ) ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'snax-videos-settings' ); ?>
			<?php do_settings_sections( 'snax-videos-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>
		</form>
	</div>

	<?php
}

/**
 * Render section description
 */
function snax_admin_settings_videos_section_description() {}

/**
 * Featured media field
 */
function snax_admin_setting_video_featured_media_field() {
	$field = snax_video_featured_media_field();
	?>

	<select name="snax_video_featured_media_field" id="snax_video_featured_media_field">
		<option value="disabled" <?php selected( $field, 'disabled' ) ?>><?php esc_html_e( 'disabled', 'snax' ) ?></option>
		<option value="required" <?php selected( $field, 'required' ) ?>><?php esc_html_e( 'required', 'snax' ) ?></option>
		<option value="optional" <?php selected( $field, 'optional' ) ?>><?php esc_html_e( 'optional', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Featured media on single post
 */
function snax_admin_setting_video_show_featured_media() {
	$checked = snax_video_show_featured_media();
	?>
	<input name="snax_video_show_featured_media" id="snax_video_show_featured_media" value="standard" type="checkbox" <?php checked( $checked ); ?> />
	<?php
}

/**
 * Category field
 */
function snax_admin_setting_video_category_field() {
	$field = snax_video_category_field();
	?>

	<select name="snax_video_category_field" id="snax_video_category_field">
		<option value="disabled" <?php selected( $field, 'disabled' ) ?>><?php esc_html_e( 'disabled', 'snax' ) ?></option>
		<option value="required" <?php selected( $field, 'required' ) ?>><?php esc_html_e( 'required', 'snax' ) ?></option>
		<option value="optional" <?php selected( $field, 'optional' ) ?>><?php esc_html_e( 'optional', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Multiple categories selection.
 */
function snax_admin_setting_video_category_multi() {
	$checked = snax_video_multiple_categories_selection();
	?>
	<input name="snax_video_category_multi" id="snax_video_category_multi" value="standard" type="checkbox" <?php checked( $checked ); ?> />
	<?php
}

/**
 * Category white-list
 */
function snax_admin_setting_video_category_whitelist() {
	$whitelist      = snax_video_get_category_whitelist();
	$all_categories = get_categories( 'hide_empty=0' );
	?>
	<select size="10" name="snax_video_category_whitelist[]" id="snax_video_category_whitelist" multiple="multiple">
		<option value="" <?php selected( in_array( '', $whitelist, true ) ); ?>><?php esc_html_e( '- Allow all -', 'snax' ) ?></option>
		<?php foreach ( $all_categories as $category_obj ) : ?>
			<?php
			// Exclude the Uncategorized option.
			if ( 'uncategorized' === $category_obj->slug ) {
				continue;
			}
			?>

			<option value="<?php echo esc_attr( $category_obj->slug ); ?>" <?php selected( in_array( $category_obj->slug, $whitelist, true ) ); ?>><?php echo esc_html( $category_obj->name ) ?></option>
		<?php endforeach; ?>
	</select>
	<p class="description"><?php esc_html_e( 'Categories allowed for user while creating a new post.', 'snax' ); ?></p>
	<?php
}

/**
 * Auto assign to category.
 */
function snax_admin_setting_video_category_auto_assign() {
	$auto_assign_list = snax_video_get_category_auto_assign();
	$all_categories = get_categories( 'hide_empty=0' );
	?>
	<select size="10" name="snax_video_category_auto_assign[]" id="snax_video_category_auto_assign" multiple="multiple">
		<option value="" <?php selected( in_array( '', $auto_assign_list, true ) ); ?>><?php esc_html_e( '- Not assign -', 'snax' ) ?></option>
		<?php foreach ( $all_categories as $category_obj ) : ?>
			<?php
			// Exclude the Uncategorized option.
			if ( 'uncategorized' === $category_obj->slug ) {
				continue;
			}
			?>

			<option value="<?php echo esc_attr( $category_obj->slug ); ?>" <?php selected( in_array( $category_obj->slug, $auto_assign_list, true ) ); ?>><?php echo esc_html( $category_obj->name ) ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Whether to allow the Snax Author to add referral links to posts and items
 */
function snax_admin_setting_video_allow_snax_authors_to_add_referrals() {
	$allow = snax_video_allow_snax_authors_to_add_referrals();
	?>

	<select name="snax_video_allow_snax_authors_to_add_referrals" id="snax_video_allow_snax_authors_to_add_referrals">
		<option value="standard" <?php selected( $allow, true ) ?>><?php esc_html_e( 'show', 'snax' ) ?></option>
		<option value="none" <?php selected( $allow, false ) ?>><?php esc_html_e( 'hide', 'snax' ) ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Applies only to Snax Authors', 'snax' ); ?></p>
	<?php
}

