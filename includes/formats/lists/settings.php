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
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_lists' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_lists' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_lists( $sections ) {
	$sections['snax_settings_lists'] = array(
		'title'    => __( 'Lists', 'snax' ),
		'callback' => 'snax_admin_settings_lists_section_description',
		'page'      => 'snax-lists-settings',
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
function snax_admin_settings_fields_lists( $fields ) {
	$fields['snax_settings_lists'] = array(

		/* Frontend Form */

		'snax_list_frontend_form_header' => array(
			'title'             => '<h2>' . __( 'Frontend Form', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_list_featured_media_field' => array(
			'title'             => __( 'Featured Image', 'snax' ),
			'callback'          => 'snax_admin_setting_list_featured_media_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_list_category_field' => array(
			'title'             => __( 'Category', 'snax' ),
			'callback'          => 'snax_admin_setting_list_category_field',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_list_category_multi' => array(
			'title'             => __( 'Multiple categories selection?', 'snax' ),
			'callback'          => 'snax_admin_setting_list_category_multi',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_list_category_whitelist' => array(
			'title'             => __( 'Category whitelist', 'snax' ),
			'callback'          => 'snax_admin_setting_list_category_whitelist',
			'sanitize_callback' => 'snax_sanitize_category_whitelist',
			'args'              => array(),
		),
		'snax_list_category_auto_assign' => array(
			'title'             => __( 'Auto assign to categories', 'snax' ),
			'callback'          => 'snax_admin_setting_list_category_auto_assign',
			'sanitize_callback' => 'snax_sanitize_category_whitelist',
			'args'              => array(),
		),
		'snax_list_allow_snax_authors_to_add_referrals' => array(
			'title'             => __( 'Item referral link', 'snax' ),
			'callback'          => 'snax_admin_setting_list_allow_snax_authors_to_add_referrals',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_active_item_forms' => array(
			'title'             => __( 'Item forms', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_active_item_forms',
			'sanitize_callback' => 'snax_sanitize_text_array',
			'args'              => array(),
		),
		'snax_froala_for_list_items' => array(
			'title'             => __( 'Allow rich editor for items in open lists', 'snax' ),
			'callback'          => 'snax_admin_setting_froala_for_list_items',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),

		/* Single Post */

		'snax_list_single_post_header' => array(
			'title'             => '<h2>' . __( 'Single Post', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),

		'snax_list_show_featured_media' => array(
			'title'             => __( 'Show Featured Media', 'snax' ),
			'callback'          => 'snax_admin_setting_list_show_featured_media',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_show_open_list_in_title' => array(
			'title'             => __( 'Show list status in title', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_list_status_in_title',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_show_item_count_in_title' => array(
			'title'             => __( 'Show items count in title', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_item_count_in_title',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_display_comments_on_lists' => array(
			'title'             => __( 'Display items comments on list view ', 'snax' ),
			'callback'          => 'snax_admin_setting_display_comments_on_lists',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),

		/* Limits */

		'snax_limits_header' => array(
			'title'             => '<h2>' . __( 'Limits', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_items_per_page' => array(
			'title'             => __( 'List items per page', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_items_per_page',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_item_title_max_length' => array(
			'title'             => __( 'Title length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_item_title_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_item_content_max_length' => array(
			'title'             => __( 'Description length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_item_content_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_item_source_max_length' => array(
			'title'             => __( 'Source length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_item_source_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_item_ref_link_max_length' => array(
			'title'             => __( 'Referral link length', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_item_ref_link_max_length',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
	);

	if ( defined( 'BTP_DEV' ) && BTP_DEV ) {

		/* Demos */

		$fields['snax_settings_lists']['snax_demo_header'] = array(
			'title'             => '<h2>' . __( 'Demo', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		);

		$fields['snax_settings_lists']['snax_demo_list_post_id'] = array(
			'title'             => __( 'Example List', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_demo_post',
			'sanitize_callback' => 'intval',
			'args'              => array( 'format' => 'list' ),
		);
	}

	return $fields;
}

function snax_admin_lists_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ) ?></h1>
		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'Lists', 'snax' ) ); ?></h2>

		<form action="options.php" method="post">

			<?php settings_fields( 'snax-lists-settings' ); ?>
			<?php do_settings_sections( 'snax-lists-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>
		</form>
	</div>

	<?php
}

/**
 * Render Lists section description
 */
function snax_admin_settings_lists_section_description() {}

/**
 * Featured media field
 */
function snax_admin_setting_list_featured_media_field() {
	$field = snax_list_featured_media_field();
	?>

	<select name="snax_list_featured_media_field" id="snax_list_featured_media_field">
		<option value="disabled" <?php selected( $field, 'disabled' ) ?>><?php esc_html_e( 'disabled', 'snax' ) ?></option>
		<option value="required" <?php selected( $field, 'required' ) ?>><?php esc_html_e( 'required', 'snax' ) ?></option>
		<option value="optional" <?php selected( $field, 'optional' ) ?>><?php esc_html_e( 'optional', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Featured media on single post
 */
function snax_admin_setting_list_show_featured_media() {
	$checked = snax_list_show_featured_media();
	?>
	<input name="snax_list_show_featured_media" id="snax_list_show_featured_media" value="standard" type="checkbox" <?php checked( $checked ); ?> />
	<?php
}

/**
 * Category field
 */
function snax_admin_setting_list_category_field() {
	$field = snax_list_category_field();
	?>

	<select name="snax_list_category_field" id="snax_list_category_field">
		<option value="disabled" <?php selected( $field, 'disabled' ) ?>><?php esc_html_e( 'disabled', 'snax' ) ?></option>
		<option value="required" <?php selected( $field, 'required' ) ?>><?php esc_html_e( 'required', 'snax' ) ?></option>
		<option value="optional" <?php selected( $field, 'optional' ) ?>><?php esc_html_e( 'optional', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Multiple categories selection.
 */
function snax_admin_setting_list_category_multi() {
	$checked = snax_list_multiple_categories_selection();
	?>
	<input name="snax_list_category_multi" id="snax_list_category_multi" value="standard" type="checkbox" <?php checked( $checked ); ?> />
	<?php
}

/**
 * Category white-list
 */
function snax_admin_setting_list_category_whitelist() {
	$whitelist      = snax_list_get_category_whitelist();
	$all_categories = get_categories( 'hide_empty=0' );
	?>
	<select size="10" name="snax_list_category_whitelist[]" id="snax_list_category_whitelist" multiple="multiple">
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
function snax_admin_setting_list_category_auto_assign() {
	$auto_assign_list = snax_list_get_category_auto_assign();
	$all_categories = get_categories( 'hide_empty=0' );
	?>
	<select size="10" name="snax_list_category_auto_assign[]" id="snax_list_category_auto_assign" multiple="multiple">
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
function snax_admin_setting_list_allow_snax_authors_to_add_referrals() {
	$allow = snax_list_allow_snax_authors_to_add_referrals();
	?>

	<select name="snax_list_allow_snax_authors_to_add_referrals" id="snax_list_allow_snax_authors_to_add_referrals">
		<option value="standard" <?php selected( $allow, true ) ?>><?php esc_html_e( 'show', 'snax' ) ?></option>
		<option value="none" <?php selected( $allow, false ) ?>><?php esc_html_e( 'hide', 'snax' ) ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Applies only to Snax Authors', 'snax' ); ?></p>
	<?php
}

/**
 * New item forms
 */
function snax_admin_setting_callback_active_item_forms() {
	$forms = snax_get_registered_item_forms();
	$active_forms_ids = snax_get_active_item_forms_ids();

	foreach ( $forms as $form_id => $form_args ) {
		$checkbox_id = 'snax_active_item_form_' . $form_id;
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $checkbox_id ); ?>">
				<input name="snax_active_item_forms[]" id="<?php echo esc_attr( $checkbox_id ); ?>" type="checkbox" value="<?php echo esc_attr( $form_id ); ?>" <?php checked( in_array( $form_id, $active_forms_ids, true ) , true ); ?> /> <?php echo esc_html( $form_args['labels']['name'] ); ?>
			</label>
		</fieldset>
		<?php
	}
	?>
	<?php
}

/**
 * Show open list status in title
 */
function snax_admin_setting_callback_list_status_in_title() {
	?>
	<input name="snax_show_open_list_in_title" id="snax_show_open_list_in_title" type="checkbox" <?php checked( snax_show_open_list_in_title() ); ?> />
	<?php
}

/**
 * Anonymous posting
 */
function snax_admin_setting_callback_anonymous() {
	?>

	<input name="snax_allow_anonymous" id="snax_allow_anonymous" type="checkbox" value="1" <?php checked( snax_allow_anonymous( false ) ); ?> />
	<label for="snax_allow_anonymous"><?php esc_html_e( 'Allow guest users without accounts to submit new items.', 'snax' ); ?></label>

	<?php
}

/**
 * Item count in title
 */
function snax_admin_setting_callback_item_count_in_title() {
	?>
	<input name="snax_show_item_count_in_title" id="snax_show_item_count_in_title" type="checkbox" <?php checked( snax_show_item_count_in_title() ); ?> />
	<?php
}

/**
 * Wheter to allow Froala in open list items
 */
function snax_admin_setting_froala_for_list_items() {
	$froala_for_list_items = snax_froala_for_list_items();
	?>

	<select name="snax_froala_for_list_items" id="snax_froala_for_list_items">
		<option value="standard" <?php selected( $froala_for_list_items, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $froala_for_list_items, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Whether to allow comments for items
 */
function snax_admin_setting_display_comments_on_lists() {
	$display_comments_on_lists = snax_display_comments_on_lists();
	?>

	<select name="snax_display_comments_on_lists" id="snax_display_comments_on_lists">
		<option value="standard" <?php selected( $display_comments_on_lists, true ) ?>><?php esc_html_e( 'yes', 'snax' ) ?></option>
		<option value="none" <?php selected( $display_comments_on_lists, false ) ?>><?php esc_html_e( 'no', 'snax' ) ?></option>
	</select>
	<?php
}

/**
 * Item title max. length
 */
function snax_admin_setting_callback_item_title_max_length() {
	?>
	<input name="snax_item_title_max_length" id="snax_item_title_max_length" type="number" size="5" value="<?php echo esc_attr( snax_get_item_title_max_length() ); ?>" />
	<?php
}

/**
 * Item content max. length
 */
function snax_admin_setting_callback_item_content_max_length() {
	?>
	<input name="snax_item_content_max_length" id="snax_item_content_max_length" type="number" size="5"
	       value="<?php echo esc_attr( snax_get_item_content_max_length() ); ?>"/>
	<?php
}

/**
 * Item source max. length
 */
function snax_admin_setting_callback_item_source_max_length() {
	?>
	<input name="snax_item_source_max_length" id="snax_item_source_max_length" type="number" size="5" value="<?php echo esc_attr( snax_get_item_source_max_length() ); ?>" />
	<?php
}

/**
 * Item referral link max. length
 */
function snax_admin_setting_callback_item_ref_link_max_length() {
	?>
	<input name="snax_item_ref_link_max_length" id="snax_item_ref_link_max_length" type="number" size="5"
	       value="<?php echo esc_attr( snax_get_item_ref_link_max_length() ); ?>"/>
	<?php
}
