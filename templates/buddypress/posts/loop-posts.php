<?php
/**
 * Post items Loop
 *
 * @package snax 1.11
 * @subpackage Votes
 */

?>

<?php do_action( 'snax_template_before_bp_posts_loop' ); ?>

<div class="snax-posts">

	<?php while ( snax_user_posts() ) : snax_the_post(); ?>

		<?php snax_get_template_part( 'content' ); ?>

	<?php endwhile; ?>

</div>

<?php do_action( 'snax_template_after_bp_posts_loop' ); ?>
