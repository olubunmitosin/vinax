<?php
/**
 * Snax News Post Row Actions
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-actions">

	<?php if ( current_user_can( 'snax_publish_posts' ) ) : ?>

		<input type="submit" value="<?php esc_attr_e( 'Publish', 'snax' ); ?>"
		       class="snax-button snax-button-publish-post" />

	<?php else : ?>

		<input type="submit" value="<?php esc_attr_e( 'Submit for Review', 'snax' ); ?>"
		       class="snax-button snax-button-submit-post" />

	<?php endif; ?>

	<a href="<?php echo esc_url( snax_get_frontend_submission_page_url() );?>" class="snax-cancel-submission" data-snax-cancel-nonce="<?php echo wp_create_nonce( 'snax-cancel' ); ?>"><?php esc_html__( 'Cancel', 'snax' ); ?></a>

</div>
