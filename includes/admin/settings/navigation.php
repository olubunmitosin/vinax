<?php
/**
 * Snax Settings Navigation
 *
 * @package snax
 * @subpackage Settings
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

/**
 * Highlight the Settings > Snax main menu item regardless of which actual tab we are on.
 */
function snax_admin_settings_menu_highlight() {
	global $plugin_page, $submenu_file;

	$settings_pages = apply_filters( 'snax_settings_menu_highlight', array(
		'snax-general-settings',
		'snax-pages-settings',
		'snax-lists-settings',
		'snax-quizzes-settings',
		'snax-polls-settings',
		'snax-stories-settings',
		'snax-memes-settings',
		'snax-audios-settings',
		'snax-videos-settings',
		'snax-images-settings',
		'snax-galleries-settings',
		'snax-embeds-settings',
		'snax-voting-settings',
		'snax-auth-settings',
		'snax-demo-settings'
	) );

	if ( in_array( $plugin_page, $settings_pages, true ) ) {
		// We want to map all subpages to one settings page (in main menu).
		$submenu_file = 'snax-general-settings';
	}
}

/**
 * Get tabs in the admin settings area.
 *
 * @param string $active_tab        Name of the tab that is active. Optional.
 *
 * @return string
 */
function snax_get_admin_settings_tabs( $active_tab = '' ) {
	$tabs = array();

	$tabs['general'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-general-settings' ), 'admin.php' ) ),
		'name' => __( 'General', 'snax' ),
	);

	$tabs['pages'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-pages-settings' ), 'admin.php' ) ),
		'name' => __( 'Pages', 'snax' ),
	);

	$tabs['lists'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-lists-settings' ), 'admin.php' ) ),
		'name' => __( 'Lists', 'snax' ),
	);


	$tabs['quizzes'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-quizzes-settings' ), 'admin.php' ) ),
		'name' => __( 'Quizzes', 'snax' ),
	);

	$tabs['polls'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-polls-settings' ), 'admin.php' ) ),
		'name' => __( 'Polls', 'snax' ),
	);

	$tabs['stories'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-stories-settings' ), 'admin.php' ) ),
		'name' => __( 'Stories', 'snax' ),
	);

	$tabs['memes'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-memes-settings' ), 'admin.php' ) ),
		'name' => __( 'Memes', 'snax' ),
	);

	$tabs['audios'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-audios-settings' ), 'admin.php' ) ),
		'name' => __( 'Audios', 'snax' ),
	);

	$tabs['videos'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-videos-settings' ), 'admin.php' ) ),
		'name' => __( 'Videos', 'snax' ),
	);

	$tabs['images'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-images-settings' ), 'admin.php' ) ),
		'name' => __( 'Images', 'snax' ),
	);

	$tabs['galleries'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-galleries-settings' ), 'admin.php' ) ),
		'name' => __( 'Galleries', 'snax' ),
	);

	$tabs['embeds'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-embeds-settings' ), 'admin.php' ) ),
		'name' => __( 'Embeds', 'snax' ),
	);

	$tabs['links'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-links-settings' ), 'admin.php' ) ),
		'name' => __( 'Links', 'snax' ),
	);

	$tabs['voting'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-voting-settings' ), 'admin.php' ) ),
		'name' => __( 'Voting', 'snax' ),
	);

	$tabs['auth'] = array(
		'href' => snax_admin_url( add_query_arg( array( 'page' => 'snax-auth-settings' ), 'admin.php' ) ),
		'name' => __( 'Auth', 'snax' ),
	);

	return apply_filters( 'snax_get_admin_settings_tabs', $tabs, $active_tab );
}

/**
 * Output the tabs in the admin area.
 *
 * @param string $active_tab        Name of the tab that is active. Optional.
 */
function snax_admin_settings_tabs( $active_tab = '' ) {
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	/**
	 * Filters the admin tabs to be displayed.
	 *
	 * @param array $value      Array of tabs to output to the admin area.
	 */
	$tabs = apply_filters( 'snax_admin_settings_tabs', snax_get_admin_settings_tabs( $active_tab ) );

	// Loop through tabs and build navigation.
	foreach ( array_values( $tabs ) as $tab_data ) {
		$is_current = (bool) ( $tab_data['name'] === $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tabs_html .= '<a href="' . esc_url( $tab_data['href'] ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( $tab_data['name'] ) . '</a>';
	}

	echo filter_var( $tabs_html );

	do_action( 'snax_admin_tabs' );
}
