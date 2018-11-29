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

<?php do_action( 'snax_template_before_video_loop' ); ?>

<?php while ( snax_cards() ) : snax_the_card(); ?>

	<?php
	switch( snax_get_item_format() ) {
		case 'embed':
			snax_get_template_part( 'content-embed' );
			break;

		case 'video':
			snax_get_template_part( 'content-video' );
			break;
	}
	?>

<?php endwhile; ?>

<?php do_action( 'snax_template_after_video_loop' ); ?>
