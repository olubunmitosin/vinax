<a class="snax-quiz-progress-coin snax-quiz-progress-coin-checked">1</a>
<a class="snax-quiz-progress-coin snax-quiz-progress-coin-checked snax-quiz-progress-coin-right">2</a>
<a class="snax-quiz-progress-coin snax-quiz-progress-coin-checked snax-quiz-progress-coin-wrong">3</a>

<div class="snax-quiz-progress">
	<div class="snax-quiz-progress-coins">

		<?php for ( $i = 1; $i <= snax_get_questions_count(); $i ++ ) : ?>
			<?php
			$snax_coin_class = array(
				'snax-quiz-progress-coin',
			);
			?>

			<?php if ( true ) : ?>
				<a class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_coin_class  ) ); ?>" href=""><?php echo (int) $i; ?></a>
			<?php else : ?>
				<span class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_coin_class  ) ); ?>"><?php echo (int) $i; ?></span>
			<?php endif; ?>

		<?php endfor; ?>
	</div>
</div>