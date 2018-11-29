<?php
/**
 * Items Loop
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php do_action( 'snax_template_before_items_loop' ); ?>

<div class="snax-items">

	<?php $snax_index = 0; ?>

	<?php while ( snax_items() ) : snax_the_item(); ?>

		<?php do_action( 'snax_before_item', get_post(), $snax_index ); ?>

		<?php snax_get_template_part( 'content', 'item' ); ?>

		<?php do_action( 'snax_after_item', get_post(), $snax_index ); ?>

		<?php $snax_index++; ?>

	<?php endwhile; ?>

</div>

<?php do_action( 'snax_template_after_items_loop' ); ?>
