<?php
/**
 * Snax Latest Votes widget
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<div id="<?php echo esc_attr( $snax_latest_votes_id ); ?>" class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_latest_votes_classes ) ); ?>">
	<?php if ( ! empty( $snax_latest_votes ) ) : ?>

		<ul class="snax-links">
			<?php foreach ( $snax_latest_votes as $snax_vote ) : ?>
				<li>
					<span class="snax-upvote<?php echo (int) $snax_vote->vote === snax_get_upvote_value() ? ' snax-user-voted' : ''; ?>">+</span>
					<span class="snax-downvote<?php echo (int) $snax_vote->vote === snax_get_downvote_value() ? ' snax-user-voted' : ''; ?>">-</span>

					<div class="snax-link-header">
						<?php if ( 'global' === $snax_latest_votes_type ) : ?>
							<span class="snax-meta">
								<?php
								$author_link = '<a href="' . esc_url( get_author_posts_url( $snax_vote->author_id ) ) . '">' . esc_html( get_the_author_meta( 'display_name', $snax_vote->author_id ) ) . '</a>';
								echo sprintf( esc_html__( '%1$s on', 'snax' ), $author_link );
								?>
							</span>
						<?php endif; ?>

						<p class="snax-link-title"><strong><a href="<?php echo esc_url( get_the_permalink( $snax_vote->post_id ) ); ?>"><?php echo get_the_title( $snax_vote->post_id ); ?></a></strong></p>
					</div>
				</li>

			<?php endforeach; ?>
		</ul>

		<?php if ( $snax_latest_votes_view_all_url ) : ?>
			<p class="snax-more-results">
				<a class="snax-link" href="<?php echo esc_url( $snax_latest_votes_view_all_url ); ?>"><?php esc_html_e( 'View all votes', 'snax' ); ?></a>
			</p>
		<?php endif; ?>


	<?php else : ?>
		<p class="snax-no-results">
			<?php esc_html_e( 'Sorry. No data so far.', 'snax' ); ?>
		</p>
	<?php endif; ?>
</div>
