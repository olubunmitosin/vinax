<?php
/**
 * Post origin
 *
 * @package snax 1.11
 * @subpackage Theme
 * @since 1.1.0
 */

?>
<p class="snax-post-origin"><?php esc_html_e( 'This post was created with our nice and easy submission form.', 'snax' ); ?> <a href="<?php echo esc_url( snax_get_frontend_submission_page_url() ); ?>"><?php esc_html_e( 'Create your post!', 'snax' ); ?></a></p>
