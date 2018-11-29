<?php
/**
 * Template part for displaying item navigation.
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<nav class="snax-item-nav" role="navigation">
	<h2 class="screen-reader-text"><?php esc_html_e( 'Item navigation', 'snax' ); ?></h2>

	<p>
		<?php if ( snax_get_previous_item_id() ) : ?>
			<a class="snax-item-prev" href="<?php the_permalink( snax_get_previous_item_id() ); ?>"><?php esc_html_e( 'Previous', 'snax' ); ?></a>
		<?php endif; ?>

		<a class="snax-item-back" href="<?php the_permalink( snax_get_item_parent_id() ); ?>"><?php esc_html_e( 'View full list', 'snax' ); ?></a>

		<?php if ( snax_get_next_item_id() ) : ?>
			<a class="snax-item-next" href="<?php the_permalink( snax_get_next_item_id() ); ?>"><?php esc_html_e( 'Next', 'snax' ); ?></a>
		<?php endif; ?>
	</p>
</nav>


