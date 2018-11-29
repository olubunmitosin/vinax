<?php
/**
 * Gallery items Loop
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

global $page;

$snax_items_per_page = snax_get_items_per_page();
$snax_items_offset   = $snax_items_per_page * ( (int) $page - 1 );

$snax_items_args = array(
	'posts_per_page' => $snax_items_per_page,
	'offset'         => $snax_items_offset,
);
?>

<?php do_action( 'snax_before_items_loop' ); ?>

<?php if ( snax_has_gallery_items( snax_get_post_id(), $snax_items_args ) ) : ?>

	<?php snax_get_template_part( 'loop', 'items' ); ?>

<?php endif; ?>

<?php do_action( 'snax_after_items_loop' ); ?>
