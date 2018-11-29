<?php
/**
 * Single Item Content Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<article <?php post_class( 'snax-item' ); ?>>
	<header class="snax-item-header">
		<?php snax_render_item_title(); ?>
		<?php snax_render_item_parent(); ?>
	</header>

	<?php snax_get_template_part( 'items/media' ); ?>

	<p class="snax-item-meta">
		<?php snax_render_item_author(); ?>
		<?php snax_render_item_date(); ?>
	</p>

	<div class="snax-item-actions">
		<?php snax_render_voting_box(); ?>
		<?php snax_render_item_share(); ?>
		<?php snax_render_item_referral_link(); ?>
		<?php snax_render_item_action_links(); ?>
	</div>

	<?php snax_render_comments_box(); ?>
</article>
