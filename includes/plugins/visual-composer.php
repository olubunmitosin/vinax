<?php
/**
 * Visual Composer plugin functions
 *
 * @package snax
 * @subpackage Plugins
 */

add_action( 'admin_menu', 'snax_hide_vc_for_snax_authors' );

/**
 * Hide VC Welcome Page for snax_authors
 *
 * @return void
 */
function snax_hide_vc_for_snax_authors() {
	$current_user = wp_get_current_user();
	if ( in_array( 'snax_author',$current_user->roles ) ) {
		remove_menu_page( 'vc-welcome' );
	}
}
