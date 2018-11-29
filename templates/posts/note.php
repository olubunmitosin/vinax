<?php
/**
 * Single Item Content Part for Displaying Notes
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<?php if ( snax_post_submitted() ) : ?>

	<?php snax_get_template_part( 'posts/note-submission-success' ); ?>

<?php endif; ?>
