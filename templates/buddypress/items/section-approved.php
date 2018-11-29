<?php
/**
 * User Approved Items
 *
 * @package snax 1.11
 * @subpackage Templates
 */

?>
<div class="snax">
	<?php do_action( 'snax_template_before_user_approved_items' ); ?>

	<div id="snax-user-approved-items" class="snax-user-approved-items">
		<div class="snax-user-section">

			<?php if ( snax_has_user_approved_items( 'list', bp_displayed_user_id() ) ) : ?>

				<?php snax_get_template_part( 'buddypress/items/pagination', 'top' ); ?>

				<?php snax_get_template_part( 'buddypress/items/loop-items' ); ?>

				<?php snax_get_template_part( 'buddypress/items/pagination', 'bottom' ); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'There are no items yet', 'snax' ); ?></p>

			<?php endif; ?>

		</div>
	</div>

	<?php do_action( 'snax_template_after_user_approved_items' ); ?>
</div>
