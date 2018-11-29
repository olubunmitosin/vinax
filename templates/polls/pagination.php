<?php if ( snax_poll_has_next_page() ): ?>

	<nav class="snax-poll-pagination">
			<?php snax_poll_next_page( __( 'Next question', 'snax' ), array( 'snax-poll-pagination-next', 'g1-arrow', 'g1-arrow', 'g1-arrow-right', 'g1-arrow-solid', 'g1-arrow-disabled', 'next' ) ); ?>
	</nav>

<?php endif; ?>