<?php
/**
 * User Approved Posts
 *
 * @package snax 1.11
 * @subpackage Templates
 */

?>
<div class="snax">
	<?php do_action( 'snax_template_before_user_approved_posts' ); ?>

	<div id="snax-user-approved-posts" class="snax-user-approved-posts">
		<div class="snax-user-section">

			<?php if ( snax_has_user_approved_posts( bp_displayed_user_id() ) ) : ?>

				<?php snax_get_template_part( 'buddypress/posts/pagination', 'top' ); ?>

				<?php snax_get_template_part( 'buddypress/posts/loop-posts' ); ?>

				<?php snax_get_template_part( 'buddypress/posts/pagination', 'bottom' ); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'There are no posts yet', 'snax' ); ?></p>

			<?php endif; ?>

		</div>
	</div>

	<?php do_action( 'snax_template_after_user_approved_posts' ); ?>
</div>
