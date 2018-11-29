<?php
/**
 * Poll result template part
 *
 * @package snax 1.11
 * @subpackage Poll
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php
global $snax_poll_result_data;
$snax_data = $snax_poll_result_data;

$snax_result  = get_post();
$snax_poll_id = $snax_result->post_parent;
$poll_title   = get_the_title( $snax_poll_id );
?>


<div class="snax-poll-result snax-poll-result-unlocked">
	<div class="snax-poll-result-header">
		<p class="snax-poll-result-parent"><strong><?php echo wp_kses_post( $poll_title ); ?></strong></p>

		<h2 class="snax-poll-result-score"><?php echo wp_kses_post( sprintf( __( 'You got <strong>%d</strong> out of <strong>%d</strong> right!', 'snax' ), absint( 0 ), absint( $snax_data['all'] ) ) ); ?></h2>
		<h3 class="snax-poll-result-title"><?php echo wp_kses_post( get_the_title() ); ?></h3>
	</div>

	<figure class="snax-poll-result-media">
		<?php the_post_thumbnail(); ?>
	</figure>

	<div class="snax-poll-result-body">
		<div class="snax-poll-result-desc">
			<?php the_content(); ?>
		</div>

		<div class="snax-poll-result-share">
			<h3><?php esc_html_e( 'Share your result', 'snax' ); ?></h3>

			<?php
				$snax_share_data = array(
					'title'       => $poll_title,
					'description' => sprintf( __( 'I got %1$d out of %2$d right! How do you measure up?', 'snax' ), absint( 0 ), absint( $snax_data['all'] ) ),
				);

				// Use result image as share picture, if set. If not, poll featured media will be used.
				$snax_result_thumb = get_the_post_thumbnail_url();

				if ( $snax_result_thumb ) {
					$snax_share_data['thumb'] = $snax_result_thumb;
				}
			?>

			<?php snax_render_poll_share( $snax_poll_id, $snax_share_data ); ?>
		</div>
	</div>
</div>
