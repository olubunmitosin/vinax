<?php
/**
 * Results template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_poll_results          = snax_get_poll_results( get_the_ID() );
$snax_poll_questions        = $snax_poll_results['questions'];
$snax_poll_questions_count  = count( $snax_poll_questions );
$snax_poll_total_votes      = (int) $snax_poll_results['total'];
?>

<h3>
	<?php if ( $snax_poll_questions_count > 0 ): ?>
		<?php printf( esc_html__( 'Users voted %d times.', 'snax' ), abs( $snax_poll_total_votes / $snax_poll_questions_count ) ); ?>
	<?php else: ?>
		<?php esc_html_e( 'No user voted so far.', 'snax' ); ?>
	<?php endif; ?>
</h3>
<table class="form-table">
	<tbody>
	<?php foreach ( $snax_poll_questions as $snax_question_id => $snax_question ): ?>
	<tr>
		<th>
			<?php echo 'Q: ' . esc_html( get_the_title ($snax_question_id ) ); ?>
		</th>
		<td>
			<ul>
				<?php foreach ( $snax_question['answers'] as $snax_answer_id => $snax_answer_count ): ?>
				<li>
					<?php $snax_answer_percentage = round( $snax_answer_count / $snax_question['total'] * 100 ); ?>
					<?php printf( '%s (%s votes) - %d%%', esc_html( get_the_title ($snax_answer_id ) ), $snax_answer_count, $snax_answer_percentage ); ?>
				</li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
</table>



