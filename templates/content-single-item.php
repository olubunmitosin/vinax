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
<div class="snax">
	<?php do_action( 'snax_before_content_single_item' ); ?>

	<?php snax_get_template_part( 'items/media' ); ?>

	<div class="snax-item-actions">
		<?php snax_render_item_referral_link(); ?>
		<?php snax_render_item_action_links(); ?>
	</div>

	<?php snax_get_template_part( 'items/nav' ); ?>

	<?php do_action( 'snax_after_content_single_item' ); ?>
</div>
