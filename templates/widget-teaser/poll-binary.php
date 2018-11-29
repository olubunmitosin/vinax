<?php
/**
 * Snax binary Poll Teaser widget
 *
 * @package snax 1.11
 * @subpackage Theme
 */

$post_id = get_query_var( 'snax_widget_teaser_post_id' );
$id = get_query_var( 'snax_widget_teaser_id' );
$questions = snax_get_poll_questions( $post_id );?>
<div class="snax-teaser-binary widget snax snax-teaser-<?php echo esc_attr( snax_get_poll_setting( 'answers_set' ) );?>">
	<a href="<?php echo esc_url( get_the_permalink( $post_id ) );?>">
		<div class="snax-teaser-binary-images">
				<?php echo wp_get_attachment_image( $questions[0]['media']['id'], 'medium' );?>
				<div class="snax-teaser-binary-slogan">
					<div><?php esc_html_e( 'Hot', 'snax' ); ?></div> 
					<div><?php esc_html_e( 'or', 'snax' ); ?></div> 
					<div><?php esc_html_e( 'Not', 'snax' ); ?></div> 
				</div>
		</div>
	</a>
	<h4 class="snax-teaser-binary-post-title">
		<?php echo esc_html( get_the_title( $post_id ) );?>
	</h4>
	<a class="snax-teaser-binary-button" href="<?php echo esc_url( get_the_permalink( $post_id ) );?>">
		<?php esc_html_e( 'Take the poll', 'snax' ); ?>
	</a>
</div>
