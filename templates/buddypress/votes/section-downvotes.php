<?php
/**
 * User Upvotes
 *
 * @package snax 1.11
 * @subpackage Templates
 */

?>
<div class="snax">
	<?php do_action( 'snax_template_before_user_downvotes' ); ?>

	<div id="snax-user-upvotes" class="snax-user-upvotes">
		<div class="snax-user-section">

			<?php if ( snax_has_user_downvotes( bp_displayed_user_id() ) ) : ?>

				<?php snax_get_template_part( 'buddypress/votes/pagination', 'top' ); ?>

				<?php snax_get_template_part( 'buddypress/votes/loop-votes' ); ?>

				<?php snax_get_template_part( 'buddypress/votes/pagination', 'bottom' ); ?>

			<?php else : ?>

				<p><?php esc_html_e( 'There are no votes yet', 'snax' ); ?></p>

			<?php endif; ?>

		</div>
	</div>

	<?php do_action( 'snax_template_after_user_downvotes' ); ?>
</div>
