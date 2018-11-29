<?php
/**
 * Cards Loop
 *
 * @package snax 1.11
 * @subpackage Cards
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php do_action( 'snax_template_before_embeds_loop' ); ?>

<?php while ( snax_cards() ) : snax_the_card(); ?>

	<?php snax_get_template_part( 'content-embed' ); ?>

<?php endwhile; ?>

<?php do_action( 'snax_template_after_embeds_loop' ); ?>
