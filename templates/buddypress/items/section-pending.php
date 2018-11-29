<?php
/**
 * User Pending Items
 *
 * @package snax 1.11
 * @subpackage Templates
 */

?>
<div class="snax">
	<?php do_action( 'snax_template_before_user_pending_items' ); ?>

	<div id="snax-user-pending-items" class="snax-user-pending-items">
		<div class="snax-user-section">

			<?php if ( snax_has_user_pending_items( 'list' ) ) : ?>

				<?php snax_get_template_part( 'buddypress/items/pagination', 'top' ); ?>

				<?php snax_get_template_part( 'buddypress/items/loop-items' ); ?>

				<?php snax_get_template_part( 'buddypress/items/pagination', 'bottom' ); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'There are no items yet', 'snax' ); ?></p>

			<?php endif; ?>

		</div>
	</div>

	<?php do_action( 'snax_template_after_user_pending_items' ); ?>
</div>
