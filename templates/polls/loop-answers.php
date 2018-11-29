<?php do_action( 'snax_template_before_poll_answers_loop' ); ?>

<?php
$snax_answers_class = array(
	'snax-poll-answers',
	'snax-poll-answers-tpl-' . get_post_meta( $post->ID, '_snax_answers_tpl', true ),
);
?>
<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_answers_class ) ); ?>">

	<?php $snax_a_query = snax_get_poll_answers_query(); ?>

	<?php if ( $snax_a_query->have_posts() ) : ?>
		<ul class="snax-poll-answers-items">

			<?php while ( $snax_a_query->have_posts() ) : $snax_a_query->the_post(); ?>
				<li class="snax-poll-answers-item">

					<?php
					do_action( 'snax_before_poll_answer', get_post(), $snax_a_query->current_post );

					snax_get_template_part( 'polls/content-answer' );

					do_action( 'snax_after_poll_answer', get_post(), $snax_a_query->current_post );
					?>

				</li>
			<?php endwhile; ?>
		</ul>
	<?php endif; ?>
	<?php wp_reset_postdata(); ?>
</div><!-- .quizzard-answers -->

