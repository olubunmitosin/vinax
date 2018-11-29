<?php
/**
 * Snax Menu Endpoints Metabox
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
 */
function snax_add_menu_endpoints_metabox() {
	add_meta_box(
		'snax_menu_endpoints',
		__( 'Snax', 'snax' ),
		'snax_menu_endpoints_metabox',
		'nav-menus',
		'side',
		'default'
	);

	do_action( 'snax_register_menu_endpoints_metabox' );
}

/**
 * Render metabox
 *
 * @param WP_Post $post         Post object.
 */
function snax_menu_endpoints_metabox( $post ) {
	?>
	<div id="posttype-snax" class="posttypediv">
		<h4><?php esc_html_e( 'Logged-In', 'snax' ); ?></h4>

		<p><?php esc_html_e( 'Links visible only for logged in users.', 'snax' ); ?></p>

		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php esc_html_e( 'Log Out', 'snax' ); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php esc_html_e( 'Log Out', 'snax' ); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php echo esc_url( wp_logout_url() ); ?>">
					<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="snax-logout-nav">
				</li>
			</ul>
		</div>

		<h4><?php esc_html_e( 'Logged-Out', 'snax' ); ?></h4>

		<p><?php esc_html_e( 'Links visible only for logged out users.', 'snax' ); ?></p>

		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-2][menu-item-object-id]" value="-2"> <?php esc_html_e( 'Log In', 'snax' ); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-2][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-2][menu-item-title]" value="<?php esc_html_e( 'Log In', 'snax' ); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-2][menu-item-url]" value="<?php echo esc_url( wp_login_url() ); ?>">
					<input type="hidden" class="menu-item-classes" name="menu-item[-2][menu-item-classes]" value="snax-login-nav">
				</li>
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-3][menu-item-object-id]" value="-3"> <?php esc_html_e( 'Register', 'snax' ); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-3][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-3][menu-item-title]" value="<?php esc_html_e( 'Register', 'snax' ); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-3][menu-item-url]" value="<?php echo esc_url( wp_registration_url() ); ?>">
					<input type="hidden" class="menu-item-classes" name="menu-item[-3][menu-item-classes]" value="snax-register-nav">
				</li>
			</ul>
		</div>


		<h4><?php esc_html_e( 'Waiting Room', 'snax' ); ?></h4>

		<p><?php esc_html_e( 'Show all pending posts.', 'snax' );
		$waiting_room_url = add_query_arg( array(
			snax_get_waiting_room_query_var() => '',
		), trailingslashit( get_home_url() ) );
		?></p>

		<div class="tabs-panel tabs-panel-active">
			<ul class="categorychecklist form-no-clear">
				<li>
					<label class="menu-item-title">
						<input type="checkbox" class="menu-item-checkbox" name="menu-item[-4][menu-item-object-id]" value="-4"> <?php esc_html_e( 'Waiting Room', 'snax' ); ?>
					</label>
					<input type="hidden" class="menu-item-type" name="menu-item[-4][menu-item-type]" value="custom">
					<input type="hidden" class="menu-item-title" name="menu-item[-4][menu-item-title]" value="<?php esc_html_e( 'Waiting Room', 'snax' ); ?>">
					<input type="hidden" class="menu-item-url" name="menu-item[-4][menu-item-url]" value="<?php echo esc_url( $waiting_room_url ); ?>">
					<input type="hidden" class="menu-item-classes" name="menu-item[-4][menu-item-classes]" value="snax-waiting-room-nav">
				</li>
			</ul>
		</div>


		<!-- Actions -->
		<p class="button-controls wp-clearfix">
			<span class="add-to-menu">
				<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="<?php echo esc_attr( 'submit-posttype-snax' ); ?>" />
				<span class="spinner"></span>
			</span>
		</p>
	</div>
<?php
}

