<?php
/**
 * Items Loop
 *
 * @package snax 1.11
 * @subpackage Votes
 */

?>

<?php
//add_action( 'snax_show_item_author', '__return_false', 99 );
add_action( 'snax_show_item_position', '__return_false', 99 );
?>
<?php do_action( 'snax_template_before_bp_items_loop' ); ?>

<div class="snax-items">

	<?php while ( snax_items() ) : snax_the_item(); ?>
		<?php snax_get_template_part( 'content', 'item' ); ?>
	<?php endwhile; ?>

</div>

<?php
remove_action( 'snax_show_item_author', '__return_false', 99 );
remove_action( 'snax_show_item_position', '__return_false', 99 );
?>
<?php do_action( 'snax_template_after_bp_items_loop' ); ?>
