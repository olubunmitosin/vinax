<?php
/**
 * Votes Loop
 *
 * @package snax 1.11
 * @subpackage Votes
 */

?>

<?php
add_action( 'snax_show_item_author', '__return_false', 99 );
add_action( 'snax_show_item_position', '__return_false', 99 );
?>
<?php do_action( 'snax_template_before_bp_votes_loop' ); ?>

<div class="snax-votes">

	<?php while ( snax_votes() ) : snax_the_vote(); ?>

		<?php snax_get_template_part( 'buddypress/votes/loop-vote' ); ?>

	<?php endwhile; ?>

</div>

<?php
remove_action( 'snax_show_item_author', '__return_false', 99 );
remove_action( 'snax_show_item_position', '__return_false', 99 );
?>
<?php do_action( 'snax_template_after_bp_votes_loop' ); ?>
