<?php
/**
 * Pagination for pages of submitted items
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<?php do_action( 'snax_template_before_pagination_loop' ); ?>

<div id="pag-bottom" class="pagination no-ajax">
	<div class="pag-count">
		<?php snax_votes_pagination_count(); ?>
	</div>

	<div class="pagination-links">
		<?php snax_votes_pagination_links(); ?>
	</div>
</div>

<?php do_action( 'snax_template_after_pagination_loop' ); ?>
