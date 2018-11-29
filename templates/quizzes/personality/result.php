<?php
/**
 * Personality result template part
 *
 * @package snax 1.11
 * @subpackage Quiz
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php
global $snax_quiz_result_data;
$snax_data = $snax_quiz_result_data;

$snax_result  = get_post();
$snax_quiz_id = $snax_result->post_parent;
$quiz_title   = get_the_title( $snax_quiz_id );
?>

<div class="snax-quiz-result snax-quiz-result-unlocked">
	<div class="snax-quiz-result-header">
		<p class="snax-quiz-result-parent"><strong><?php echo esc_html( $quiz_title ); ?></strong></p>

		<h2 class="snax-quiz-result-title"><?php echo wp_kses_post( sprintf( __( 'You got: %s', 'snax' ), get_the_title() ) ); ?></h2>
	</div>

	<figure class="snax-quiz-result-media">
		<?php the_post_thumbnail(); ?>
	</figure>

	<div class="snax-quiz-result-body">
		<div class="snax-quiz-result-desc">
			<?php the_content(); ?>
		</div>

		<div class="snax-quiz-result-share">
			<h3><?php esc_html_e( 'Share your result', 'snax' ); ?></h3>

			<?php
			$snax_share_data = array(
				'title'       => sprintf( __( 'I got %s', 'snax' ), get_the_title() ) . '. ' . $quiz_title,
				'description' => $snax_data['share_description'],
			);

			// Use result image as share picture, if set. If not, quiz featured media will be used.
			$snax_result_thumb = get_the_post_thumbnail_url();

			if ( $snax_result_thumb ) {
				$snax_share_data['thumb'] = $snax_result_thumb;
			}
			?>

			<?php snax_render_quiz_share( $snax_quiz_id, $snax_share_data ); ?>
		</div>
	</div>
</div>
