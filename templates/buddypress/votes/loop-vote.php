<?php
/**
 * Single vote in loop
 *
 * @package snax 1.11
 * @subpackage Votes
 */

?>

<?php if ( snax_get_item_post_type() === get_post_type() ) : ?>
	<?php snax_get_template_part( 'content', 'item' ); ?>
<?php else : ?>
	<?php snax_get_template_part( 'content' ); ?>
<?php endif; ?>
