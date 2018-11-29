<?php
/**
 * Snax List Content Template Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */
$items = snax_get_items();

global $post;
$orig_post = $post;

foreach ( $items as $item ) {
	$post = $item;
	setup_postdata( $item );

	snax_get_template_part( 'fb-instant-articles/items/title' );
	snax_get_template_part( 'fb-instant-articles/items/media' );
	snax_get_template_part( 'fb-instant-articles/items/meta' );
}

wp_reset_postdata();
$post = $orig_post;
