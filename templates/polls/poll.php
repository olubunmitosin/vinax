<?php
/**
 * Poll template part
 *
 * @package snax 1.11
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php
$poll_type = get_post_meta( $post->ID, '_snax_poll_type', true );
$snax_poll_class = array(
	'poll',
	'poll-' . $poll_type,
);
$snax_poll_class[] = 'snax-poll-without-start-trigger';

if ( 'binary' === $poll_type ) {
	$snax_poll_class[] = 'poll-binary-' . snax_get_poll_setting( 'answers_set' );
}
$snax_poll_class[] = 'poll-reveal-' . snax_get_poll_setting( 'reveal_correct_wrong_answers' );
$snax_poll_class[] = 'poll-pagination-' . snax_get_poll_setting( 'one_question_per_page' );
?>

<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_poll_class ) ); ?>">

	<?php snax_get_template_part( 'polls/loop-questions' ); ?>

	<?php snax_get_template_part( 'polls/pagination' ); ?>

	<div class="snax-poll-results snax-poll-results-hidden">
		<?php if ( snax_poll_share_to_unlock() && snax_is_poll_last_page() ) : ?>

			<?php snax_get_template_part( 'polls/locked-result' ) ?>

		<?php endif; ?>
	</div>

	<?php if ( snax_poll_one_question_per_page() && snax_is_poll_last_page() ) : ?>

		<div class="snax-poll-check-answers snax-poll-check-answers-hidden">
			<h2><?php esc_html_e( 'Check your answers:', 'snax' ); ?></h2>

		</div>

	<?php endif; ?>

</div><!-- .poll -->

<?php do_action( 'snax_enqueue_fb_sdk' ); ?>
