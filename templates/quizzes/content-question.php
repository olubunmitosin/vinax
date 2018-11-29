<?php
$snax_question_class = array(
	'snax-quiz-question',
	'snax-quiz-question-' . get_the_ID(),
	'snax-quiz-question-hidden',
	'snax-quiz-question-unanswered',
	'snax-quiz-question-title-' . ( snax_get_title_hide() ? 'hide' : 'show' ),
	'snax-quiz-question-answer-title-' . ( snax_get_answers_labels_hide() ? 'hide' : 'show' ),
);
?>

<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_question_class ) ); ?>" data-quizzard-question-id="<?php echo absint( get_the_ID() ); ?>">
	<?php the_title( '<h2 class="snax-quiz-question-title"><span class="snax-quiz-question-counter"><span class="snax-quiz-question-counter-value"></span></span>', '</h2>' ); ?>

	<figure class="snax-quiz-question-media">
		<?php the_post_thumbnail(); ?>
	</figure>

	<?php snax_get_template_part('quizzes/loop-answers' ); ?>

</div><!-- .snax-quiz-question -->
