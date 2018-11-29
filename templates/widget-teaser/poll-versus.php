<?php
/**
 * Snax Versus Poll Teaser widget
 *
 * @package snax 1.11
 * @subpackage Theme
 */

$post_id = get_query_var( 'snax_widget_teaser_post_id' );
$id = get_query_var( 'snax_widget_teaser_id' );
$questions = snax_get_poll_questions( $post_id );?>
<style>
.snax-teaser-versus .snax-teaser-versus-images:after{
	content:"<?php echo esc_html__( 'Vs', 'snax' );?>";
}
</style>
<div class="snax-teaser-versus widget snax ">
	<a href="<?php echo esc_url( get_the_permalink( $post_id ) );?>">
		<div class="snax-teaser-versus-images">
				<?php echo wp_get_attachment_image( $questions[0]['answers'][0]['media']['id'], 'medium' );?>
				<?php echo wp_get_attachment_image( $questions[0]['answers'][1]['media']['id'], 'medium' );?>
		</div>
	</a>
	<h4 class="snax-teaser-versus-post-title">
		<?php echo esc_html( get_the_title( $post_id ) );?>
	</h4>
	<a class="snax-teaser-versus-button" href="<?php echo esc_url( get_the_permalink( $post_id ) );?>">
		<?php esc_html_e( 'Take the poll', 'snax' ); ?>
	</a>
</div>
