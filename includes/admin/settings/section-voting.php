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
add_filter( 'snax_admin_get_settings_sections', 'snax_admin_settings_sections_voting' );
add_filter( 'snax_admin_get_settings_fields',   'snax_admin_settings_fields_voting' );

/**
 * Register section
 *
 * @param array $sections       Sections.
 *
 * @return array
 */
function snax_admin_settings_sections_voting( $sections ) {
	$sections['snax_settings_voting'] = array(
		'title'    => __( 'Voting', 'snax' ),
		'callback' => 'snax_admin_settings_voting_section_description',
		'page'      => 'snax-voting-settings',
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
function snax_admin_settings_fields_voting( $fields ) {
	$fields['snax_settings_voting'] = array(
		'snax_voting_is_enabled' => array(
			'title'             => __( 'Enable voting?', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_voting_enabled',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_guest_voting_is_enabled' => array(
			'title'             => __( 'Guests can vote?', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_guest_voting_enabled',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
		'snax_voting_post_types' => array(
			'title'             => __( 'Allow users to vote on post types', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_voting_post_types',
			'sanitize_callback' => 'snax_sanitize_text_array',
			'args'              => array(),
		),
		'snax_fake_votes_header' => array(
			'title'             => '<h2>' . __( 'Fake votes', 'snax' ) . '</h2>',
			'callback'          => '__return_empty_string',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_fake_vote_count_base' => array(
			'title'             => __( 'Count base', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_fake_vote_count_base',
			'sanitize_callback' => 'intval',
			'args'              => array(),
		),
		'snax_fake_vote_for_new' => array(
			'title'             => __( 'Disable for new submissions', 'snax' ),
			'callback'          => 'snax_admin_setting_callback_fake_vote_for_new',
			'sanitize_callback' => 'sanitize_text_field',
			'args'              => array(),
		),
	);

	return $fields;
}

function snax_admin_voting_settings() {
	?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Snax Settings', 'snax' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php snax_admin_settings_tabs( __( 'Voting', 'snax' ) ); ?></h2>
		<form action="options.php" method="post">

			<?php settings_fields( 'snax-voting-settings' ); ?>
			<?php do_settings_sections( 'snax-voting-settings' ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'snax' ); ?>" />
			</p>

		</form>
	</div>

	<?php
}

/**
 * Voting section description
 */
function snax_admin_settings_voting_section_description() {}

/**
 * Voting enabled?
 */
function snax_admin_setting_callback_voting_enabled() {
	?>
	<input name="snax_voting_is_enabled" id="snax_voting_is_enabled" type="checkbox" <?php checked( snax_voting_is_enabled() ); ?> />
	<?php
}

/**
 * Guest Voting enabled?
 */
function snax_admin_setting_callback_guest_voting_enabled() {
	?>
	<input name="snax_guest_voting_is_enabled" id="snax_guest_voting_is_enabled" type="checkbox" <?php checked( snax_guest_voting_is_enabled() ); ?> />
	<?php
}


/**
 * Post types.
 */
function snax_admin_setting_callback_voting_post_types() {
	$post_types = get_post_types();
	$supported_post_types = snax_voting_get_post_types();

	foreach ( $post_types as $post_type ) {
		$skipped = array( 'attachment', 'revision', 'nav_menu_item', snax_get_item_post_type() );

		if ( in_array( $post_type, $skipped, true ) ) {
			continue;
		}

		$checkbox_id = 'snax_voting_post_type_' . $post_type;
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $checkbox_id ); ?>">
				<input name="snax_voting_post_types[]" id="<?php echo esc_attr( $checkbox_id ); ?>" type="checkbox" value="<?php echo esc_attr( $post_type ); ?>" <?php checked( in_array( $post_type, $supported_post_types, true ) , true ); ?> /> <?php echo esc_html( $post_type ); ?>
			</label>
		</fieldset>
		<?php
	}
	?>
	<?php
}

/**
 * Fake vote count base
 */
function snax_admin_setting_callback_fake_vote_count_base() {
	?>
	<input name="snax_fake_vote_count_base" id="snax_fake_vote_count_base" type="number" value="<?php echo esc_attr( snax_get_fake_vote_count_base() ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1000', 'snax' ); ?>" />
	<p class="description">
		<?php esc_html_e( 'Fake votes for a post are calculated based on this value and a post creation date (older posts\' votes are closer to the count base).', 'snax' ); ?></span><br />
		<?php esc_html_e( 'Leave empty to not use "Fake votes" feature.', 'snax' ); ?></span>
	</p>
	<?php
}

/**
 * Fake vote count base
 */
function snax_admin_setting_callback_fake_vote_for_new() {
	?>
	<input name="snax_fake_vote_for_new" id="snax_fake_vote_for_new" type="checkbox" <?php checked( snax_is_fake_vote_disabled_for_new() ); ?> />
	<p class="description">
		<?php esc_html_e( 'New users\' submitted posts won\'t be affected with fake votes', 'snax' ); ?></span>
	</p>
	<?php
}
