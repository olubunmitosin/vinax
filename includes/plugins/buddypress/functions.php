<?php
/**
 * BuddyPress Template Functions
 *
 * @package snax
 * @subpackage BuddyPress
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

add_filter( 'bp_core_admin_get_components',		'snax_bp_register_custom_components', 10, 2 );
add_filter( 'snax_get_item_author_url',        	'snax_bp_alter_item_author', 10, 2 );
add_filter( 'snax_user_profile_page',           'snax_bp_user_profile_page', 10, 2 );
add_action( 'bp_member_plugin_options_nav',		'snax_bp_member_plugin_options_nav' );
add_filter( 'snax_posts_query_args', 			'snax_filter_post_by_format' );
add_action( 'bp_before_registration_submit_buttons', 'snax_bp_render_recaptcha' );
add_filter( 'bp_core_validate_user_signup', 'snax_bp_validate_recaptcha', 10, 1 );
add_action( 'bp_before_account_details_fields', 'snax_render_recaptcha_errors' );

/**
 * Return posts component unique id
 *
 * @return string
 */
function snax_posts_bp_component_id() {
	return snax_get_url_var( 'posts' );
}

/**
 * Return items component unique id
 *
 * @return string
 */
function snax_items_bp_component_id() {
	return snax_get_url_var( 'items' );
}

/**
 * Return votew component unique id
 *
 * @return string
 */
function snax_votes_bp_component_id() {
	return snax_get_url_var( 'votes' );
}

/**
 * Init our custom components states
 *
 * @param bool $force           Skip checking components setup flag.
 */
function snax_bp_activate_components( $force = false ) {
	$snax_bp_components = get_option( 'snax_bp_components' );

	if ( $force || 'loaded' !== $snax_bp_components ) {
		$bp_active_components = bp_get_option( 'bp-active-components', array() );

		$bp_active_components[ snax_posts_bp_component_id() ] = 1;
		$bp_active_components[ snax_items_bp_component_id() ] = 1;
		$bp_active_components[ snax_votes_bp_component_id() ] = 1;

		bp_update_option( 'bp-active-components', $bp_active_components );
		add_option( 'snax_bp_components', 'loaded' );
	}
}

/**
 * Register Snax custom components
 *
 * @param array  $components        Registered components.
 * @param string $type              Component type.
 *
 * @return array
 */
function snax_bp_register_custom_components( $components, $type ) {
	if ( in_array( $type, array( 'all', 'optional' ), true ) ) {

		$posts_id = snax_posts_bp_component_id();
		$items_id = snax_items_bp_component_id();
		$votes_id = snax_votes_bp_component_id();

		// Posts.
		$components[ $posts_id ] = array(
			'title'       => __( 'Snax Posts', 'snax' ),
			'description' => __( 'Manage your posts direclty in your profile.', 'snax' ),
		);

		// Items.
		$components[ $items_id ] = array(
			'title'       => __( 'Snax Submissions', 'snax' ),
			'description' => __( 'Manage your submissions direclty in your profile.', 'snax' ),
		);

		// Votes.
		$components[ $votes_id ] = array(
			'title'       => __( 'Snax Votes', 'snax' ),
			'description' => __( 'Manage your votes direclty in your profile.', 'snax' ),
		);
	}

	return $components;
}

/**
 * Use BP user url as item author page
 *
 * @param string $url           Page url.
 * @param int    $author_id     User id.
 *
 * @return bool|string
 */
function snax_bp_alter_item_author( $url, $author_id ) {
	$url = bp_core_get_userlink( $author_id, false, true );

	return $url;
}

/**
 * Use BP user profile as user profile page
 *
 * @param string $url               Page url.
 * @param int    $user_id           User id.
 *
 * @return string
 */
function snax_bp_user_profile_page( $url, $user_id ) {
	$url = bp_core_get_user_domain( $user_id );

	return $url;
}

/**
 * Add "Snax Format" filter to Posts tab sub navigation.
 */
function snax_bp_member_plugin_options_nav() {
	if ( snax_posts_bp_component_id() !== bp_current_component() ) {
		return;
	}

	?>
	<li id="members-filter-select" class="last filter">
		<?php snax_bp_posts_format_filter_form(); ?>
	</li>
	<?php
}

/**
 * Comparision function for the filters below
 *
 * @param array $a  A filter.
 * @param array $b	A filter.
 * @return int
 */
function snax_buddypress_activity_filter_sort( $a, $b ) {
	$a = $a['labels']['name'];
	$b = $b['labels']['name'];
	if ( $a === $b ) {
		return 0;
	}
	return ($a < $b) ? -1 : 1;
}

/**
 * Output the form for filtering the posts formats.
 */
function snax_bp_posts_format_filter_form() {

	$filters 	= snax_get_formats();
	$selected 	= '';

	$var = snax_get_url_var( 'filter_by' );

	// Check for a custom sort_order.
	if ( ! empty( $_REQUEST[ $var ] ) ) {
		$selected = $_REQUEST[ $var ];
	} ?>

	<form action="" method="get" id="posts-filter-by">
		<label for="posts-format-list"><?php esc_html_e( 'Show:', 'snax' ); ?></label>

		<select id="posts-format-list" name="<?php echo esc_attr( $var ); ?>" onchange="this.form.submit();">
			<option value="" <?php selected( $selected, '' ); ?>><?php esc_html_e( 'All', 'snax' ); ?></option>

			<?php 
			usort($filters, 'snax_buddypress_activity_filter_sort');
			foreach( $filters as $filter_id => $filter_config ): ?>
				<?php
				if ( in_array( $filter_id, array( 'ranked_list', 'classic_list' ), true ) ) {
					continue;
				}

				if ( 'list' === $filter_id ) {
					$label = __( 'List', 'snax' );
				} else {
					$label = $filter_config['labels']['name'];
				}
				?>

				<option value="<?php echo esc_attr( $filter_id ); ?>" <?php selected( $selected, $filter_id ); ?>><?php echo esc_html( $label ); ?></option>

			<?php endforeach; ?>
		</select>

		<noscript>
			<input id="submit" type="submit" name="form-submit" class="submit" value="<?php esc_attr_e( 'Go', 'snax' ); ?>" />
		</noscript>
	</form>

	<?php
}

/**
 * Filter user posts by request param
 *
 * @param array $query_args		Posts query args.
 *
 * @return array
 */
function snax_filter_post_by_format( $query_args ) {
	$filter_by = filter_input( INPUT_GET, snax_get_url_var( 'filter_by' ), FILTER_SANITIZE_STRING );

	if ( $filter_by ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' 	=> snax_get_snax_format_taxonomy_slug(),
				'field' 	=> 'slug',
				'terms' => $filter_by,
			),
		);
	}

	return $query_args;
}

/**
 * Render recaptcha for registry form.
 *
 * @return void
 */
function snax_bp_render_recaptcha() {
	echo '<div id="snax-register-recaptcha"></div>';
}

/**
 * Validate recaptcha
 *
 * @param  array $result Result.
 * @return array
 */
function snax_bp_validate_recaptcha( $result ) {
	$use_recaptcha = snax_is_recatpcha_enabled_for_login_form();

	if ( $use_recaptcha ) {
		$recaptcha_token = filter_input( INPUT_POST, 'g-recaptcha-response', FILTER_SANITIZE_STRING );

		$recaptcha_valid = snax_verify_recaptcha( $recaptcha_token );

		if ( ! $recaptcha_valid ) {
			$result['errors']->errors['recaptcha'] = __( 'Please fill the recaptcha', 'snax' );
			$bp = buddypress();
			$bp->signup->errors['recaptcha'] = __( 'Please fill the recaptcha', 'snax' );
		}
	}

	return $result;
}

/**
 * Render recaptcha errors in register screen.
 */
function snax_render_recaptcha_errors() {
	do_action( 'bp_recaptcha_errors' );
}
