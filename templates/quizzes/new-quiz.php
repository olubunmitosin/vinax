<?php
/**
 * New quiz type selection template part
 *
 * @package snax 1.11
 * @subpackage Forms
 *
 * @todo This belongs to admin. It shouldn't be inside the "templates" directory.
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="wrap">
	<?php
	$snax_object = get_post_type_object( snax_get_quiz_post_type() );
	?>
	<h1 class="wp-heading-inline"><?php echo esc_html( $snax_object->labels->add_new_item ); ?></h1>
	<hr class="wp-header-end">



	<style>

		.quizzard-new-quizzes {
			margin: 0 -10px;
			display: flex;
			flex-wrap: wrap;
			align-items: stretch;
		}

		.quizzard-new-quiz {
			box-sizing: border-box;

			max-width: 420px;
			padding: 30px;
			margin: 20px 10px;

			flex: 1 1 auto;

			text-align: center;
			text-decoration: none;

			background-color: #fff;
		}

		.quizzard-new-quiz-icon:before {
			font-size: 68px;
			line-height: 1;
			font-family: "snaxicon";
		}

		.quizzard-new-quiz-icon {
			display: inline-block;
			width: 68px;
			height: 68px;

			font-style: normal;
		}

		.quizzard-new-quiz-title {
			color: inherit;
		}

		.quizzard-new-quiz-desc {
			color: #72777c;
		}

		.quizzard-new-quiz-trivia .quizzard-new-quiz-icon:before        { content: "\e01a"; }
		.quizzard-new-quiz-personality .quizzard-new-quiz-icon:before   { content: "\e003"; }



	</style>

	<div class="quizzard-new-quizzes">
		<a class="quizzard-new-quiz quizzard-new-quiz-trivia" href="<?php echo esc_url( snax_get_new_trivia_quiz_page_url() ); ?>">
			<i class="quizzard-new-quiz-icon"></i>
			<h2 class="quizzard-new-quiz-title"><?php esc_html_e( 'Trivia Quiz', 'snax' ); ?></h2>
			<p class="quizzard-new-quiz-desc"><?php echo esc_html_e( 'Series of questions with right and wrong answers that intends to check knowledge', 'snax' ); ?></p>
		</a>
		<a class="quizzard-new-quiz quizzard-new-quiz-personality" href="<?php echo esc_url( snax_get_new_personality_quiz_page_url() ); ?>">
			<i class="quizzard-new-quiz-icon"></i>
			<h2 class="quizzard-new-quiz-title"><?php esc_html_e( 'Personality Quiz', 'snax' ); ?></h2>
			<p class="quizzard-new-quiz-desc"><?php esc_html_e( 'Series of questions that intends to reveal something about the personality', 'snax' ); ?></p>
		</a>
	</div>

</div>


