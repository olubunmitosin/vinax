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
$snax_result  = get_post();
$snax_quiz_id = $snax_result->post_parent;
?>

<div class="snax-quiz-result snax-quiz-result-locked">
	<div class="snax-quiz-result-header">
		<h2 class="snax-quiz-result-title"><?php esc_html_e( 'Share the quiz to view your results!', 'snax' ); ?></h2>
	</div>

	<div class="snax-quiz-result-body">
		<div class="snax-quiz-result-share">
			<?php snax_render_quiz_share_to_unlock( $snax_quiz_id, array(
				'description' => snax_get_share_description(),
			) ); ?>
		</div>
	</div>
</div>
