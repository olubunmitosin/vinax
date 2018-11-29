<?php
/**
 * User Pending Posts
 *
 * @package snax 1.11
 * @subpackage Templates
 */
$is_current_user = get_current_user_id() === bp_displayed_user_id();
?>
<div class="snax">
	<?php do_action( 'snax_template_before_user_pending_posts' ); ?>

	<div id="snax-user-pending-posts" class="snax-user-pending-posts">
		<div class="snax-user-section">

			<?php if ( snax_has_user_pending_posts( bp_displayed_user_id() ) && $is_current_user ) : ?>

				<?php snax_get_template_part( 'buddypress/posts/pagination', 'top' ); ?>

				<?php snax_get_template_part( 'buddypress/posts/loop-posts' ); ?>

				<?php snax_get_template_part( 'buddypress/posts/pagination', 'bottom' ); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'There are no items yet', 'snax' ); ?></p>

			<?php endif; ?>

		</div>
	</div>

	<?php do_action( 'snax_template_after_user_pending_posts' ); ?>
</div>
