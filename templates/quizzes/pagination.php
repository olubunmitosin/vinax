<?php if ( snax_quiz_has_next_page() ): ?>

	<nav class="snax-quiz-pagination">
			<?php snax_quiz_next_page( __( 'Next question', 'snax' ), array( 'snax-quiz-pagination-next', 'g1-arrow', 'g1-arrow', 'g1-arrow-right', 'g1-arrow-solid', 'g1-arrow-disabled', 'next' ) ); ?>
	</nav>

<?php endif; ?>