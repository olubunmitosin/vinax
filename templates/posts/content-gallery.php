<?php
/**
 * Snax Gallery Content Template Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax snax-post-container">
	<?php
	do_action( 'snax_before_content_single_post', 'gallery' );

	snax_render_gallery_items();

	do_action( 'snax_post_voting_box' );

	do_action( 'snax_after_content_single_post', 'gallery' );
	?>
</div>

