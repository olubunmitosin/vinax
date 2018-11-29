<?php
/**
 * Template for displaying single item title
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<h3>
	<?php
	if ( snax_show_item_position() ) {
		echo intval( snax_get_item_position() ) . ' ';
	}

	$snax_item = get_post();

	if ( $post->post_title ) {
		the_title();
	}
	?>
</h3>
