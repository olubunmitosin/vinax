<?php
/**
 * Cards Loop
 *
 * @package snax
 * @subpackage Cards
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php do_action( 'snax_template_before_audio_loop' ); ?>

<?php while ( snax_cards() ) : snax_the_card(); ?>

	<?php
	switch( snax_get_item_format() ) {
		case 'embed':
			snax_get_template_part( 'content-embed' );
			break;

		case 'audio':
			snax_get_template_part( 'content-audio' );
			break;
	}
	?>

<?php endwhile; ?>

<?php do_action( 'snax_template_after_audio_loop' ); ?>
