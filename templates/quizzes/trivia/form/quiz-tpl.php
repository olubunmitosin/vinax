<?php
/**
 * Questions form
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_tab_index             = (int) filter_input( INPUT_COOKIE, 'snax_quiz_' . get_the_ID() . '_active_tab' );
$snax_settings_tab_active   = apply_filters( 'snax_quiz_settings_tab_active', true );
?>

<div id="quizzard" class="quizzard-quiz">

	<p class="nav-tab-wrapper quizzard-tab-wrapper">
		<a href="" class="nav-tab<?php echo 0 === $snax_tab_index ? ' nav-tab-active' : ''; ?>"><?php echo esc_html_e( 'Questions', 'snax' ); ?></a>

		<a href="" class="nav-tab<?php echo 1 === $snax_tab_index ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Results', 'snax' ); ?></a>

		<?php if ( $snax_settings_tab_active ) : ?>
		<a href="" class="nav-tab<?php echo 2 === $snax_tab_index ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'snax' ); ?></a>
		<?php endif; ?>
	</p>


	<div class="quizzard-questions quizzard-tab-content<?php echo 0 === $snax_tab_index ? ' quizzard-tab-content-active' : ''; ?>">
		<div class="quizzard-questions-header">
			<h2><?php esc_html_e( 'Questions', 'snax' );?></h2>

			<a href="#" class="button button-secondary button-small button-disabled quizzard-questions-collapse-all"><?php esc_html_e( 'Collapse all', 'snax' ); ?></a>
			<a href="#" class="button button-secondary button-small quizzard-questions-expand-all"><?php esc_html_e( 'Expand all', 'snax' ); ?></a>
		</div>

		<ul class="quizzard-q-items" id="quizzard-q-items">
			<li class="quizzard-q-item quizzard-next-q-item">
				<?php snax_get_template_part( 'quizzes/trivia/form/question-next-tpl' ); ?>
			</li>
		</ul><!-- .quizzards-q-items -->
	</div>

	<div class="quizzard-results quizzard-tab-content<?php echo 1 === $snax_tab_index ? ' quizzard-tab-content-active' : ''; ?>" id="quizzard-results">

		<div class="quizzard-results-header">
			<h2><?php esc_html_e( 'Results', 'snax' );?></h2>

			<a href="#" class="button button-secondary button-small quizzard-results-reset"><?php esc_html_e( 'Generate', 'snax' ); ?></a>
		</div>

		<ul class="quizzard-r-items" id="quizzard-r-items">
			<li class="quizzard-r-item quizzard-next-r-item">
				<?php snax_get_template_part( 'quizzes/trivia/form/result-next-tpl' ); ?>
			</li>
		</ul><!-- .quizzards-r-items -->

	</div>

	<?php if ( $snax_settings_tab_active ) : ?>
	<div class="quizzard-settings quizzard-tab-content<?php echo 2 === $snax_tab_index ? ' quizzard-tab-content-active' : ''; ?>">
		<?php snax_get_template_part( 'quizzes/trivia/form/settings' ); ?>
	</div>
	<?php endif; ?>

</div><!-- #quizzard -->


<input type="hidden" id="quizzard-quiz-nonce" value="<?php echo esc_attr( wp_create_nonce( 'quizzard-quiz' ) ); ?>" />
<input type="hidden" name="snax_quiz" value="trivia" />

<script type="text/template" id="quizzard-question-tpl">
	<?php snax_get_template_part( 'quizzes/trivia/form/question-tpl' ); ?>
</script>

<script type="text/template" id="quizzard-answer-tpl">
	<?php snax_get_template_part( 'quizzes/trivia/form/answer-tpl' ); ?>
</script>

<script type="text/template" id="quizzard-result-tpl">
	<?php snax_get_template_part( 'quizzes/trivia/form/result-tpl' ); ?>
</script>

<?php $snax_quiz_id = get_the_ID(); ?>
<script>
	(function (ctx) {
		if ( typeof ctx.snax_quizzes === 'undefined' ) {
			ctx.snax_quizzes = {};
		}

		ctx.snax_quizzes.quiz = {
			id: 		<?php echo absint( $snax_quiz_id ); ?>,
			type: 		'<?php echo esc_html( snax_get_trivia_quiz_type() ); ?>',
			questions:	<?php echo wp_json_encode( snax_get_questions( $snax_quiz_id ) ); ?>,
			results:	<?php echo wp_json_encode( snax_get_results( $snax_quiz_id ) ); ?>,
			ajax_url:   '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
			scores:		<?php echo wp_json_encode( snax_get_trivia_quiz_scores() ); ?>
		};
	})(window);
</script>
