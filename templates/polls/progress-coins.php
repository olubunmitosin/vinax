<a class="snax-poll-progress-coin snax-poll-progress-coin-checked">1</a>
<a class="snax-poll-progress-coin snax-poll-progress-coin-checked snax-poll-progress-coin-right">2</a>
<a class="snax-poll-progress-coin snax-poll-progress-coin-checked snax-poll-progress-coin-wrong">3</a>

<div class="snax-poll-progress">
	<div class="snax-poll-progress-coins">

		<?php for ( $i = 1; $i <= snax_get_poll_questions_count(); $i ++ ) : ?>
			<?php
			$snax_coin_class = array(
				'snax-poll-progress-coin',
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