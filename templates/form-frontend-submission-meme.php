<?php
/**
 * New post form for format "Meme"
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php
global $snax_post_format;
$snax_post_format = 'meme';
$snax_has_memes = snax_has_user_cards( $snax_post_format );

// HTML classes of the form.
$snax_class = array(
	'snax',
	'snax-meme',
	'snax-form-frontend',
	'snax-form-frontend-format-meme',
);
if ( ! $snax_has_memes ) {
	$snax_class[] = 'snax-form-frontend-without-media';
	add_filter( 'snax_form_file_upload_no_media', '__return_true' );
}
?>

<?php do_action( 'snax_before_frontend_submission_form', $snax_post_format ); ?>

	<form action="" method="post" class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
		<?php do_action( 'snax_frontend_submission_form_start', $snax_post_format ); ?>

		<div class="snax-form-main">
			<h2 class="snax-form-main-title screen-reader-text"><?php esc_html_e( 'Share your story', 'snax' ); ?></h2>

			<?php snax_get_template_part( 'posts/form-edit/row-title' ); ?>

			<div class="snax-edit-post-row-media">
				<p class="snax-tabs-nav">
				<?php if ( defined( 'BTP_DEV' ) && BTP_DEV ) :?>
					<a class="snax-tabs-nav-item snax-tabs-nav-meme-templates snax-tabs-nav-item-current"><?php esc_html_e( 'Select meme', 'snax' ); ?></a>
					<a class="snax-tabs-nav-item snax-tabs-nav-meme-image"><?php esc_html_e( 'Upload your image', 'snax' ); ?></a>
				<?php else:?>
					<a class="snax-tabs-nav-item snax-tabs-nav-meme-image snax-tabs-nav-item-current"><?php esc_html_e( 'Upload your image', 'snax' ); ?></a>
				<?php endif;?>
				</p>
				<?php
				if ( defined( 'BTP_DEV' ) && BTP_DEV ) {
				$snax_key = 'meme-templates';

				$snax_class = array(
					'snax-tab-content',
					'snax-tab-content-' . $snax_key,
					'snax-tab-content-' . ( $snax_has_memes ? 'hidden' : 'visible' ),
				);

				$snax_class[] = 'snax-tab-content-current';
				?>

				<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
					<?php snax_get_template_part( 'posts/form-edit/new', $snax_key ); ?>
				</div>
				<?php
					}
					$snax_key = 'image';

					$snax_class = array(
						'snax-tab-content',
						'snax-tab-content-' . $snax_key,
						'snax-tab-content-' . ( $snax_has_memes ? 'hidden' : 'visible' ),
					);

					if ( ! defined( 'BTP_DEV' ) || ! BTP_DEV ) {
						$snax_class[] = 'snax-tab-content-current';
					}
				?>
				<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
					<?php snax_get_template_part( 'posts/form-edit/new', $snax_key ); ?>
				</div>
			</div>

			<div class="snax-edit-post-row-image">
				<?php if ( $snax_has_memes ) : ?>

					<?php snax_get_template_part( 'loop-images' ); ?>

				<?php endif; ?>
			</div>

			<?php snax_get_template_part( 'posts/form-edit/row-source' ); ?>
			<?php
			if ( snax_is_memes_content_enabled() ) {
				snax_get_template_part( 'posts/form-edit/row-description' );
			} ?>
			<?php snax_get_template_part( 'posts/form-edit/row-referral' ); ?>

		</div><!-- .snax-form-main -->

		<div class="snax-form-side">
			<h2 class="snax-form-side-title screen-reader-text"><?php esc_html_e( 'Publish Options', 'snax' ); ?></h2>

			<input type="hidden" name="snax-post-format" value="meme"/>

			<?php snax_get_template_part( 'posts/form-edit/row-meme-top-text' ); ?>

			<?php snax_get_template_part( 'posts/form-edit/row-meme-bottom-text' ); ?>

			<?php
			if ( snax_meme_show_featured_media_field() ) {
				snax_get_template_part( 'posts/form-edit/row-featured-image' );
			}
			?>

			<?php
			if ( snax_meme_show_category_field() ) {
				snax_get_template_part( 'posts/form-edit/row-categories' );
			}
			?>

			<?php snax_get_template_part( 'posts/form-edit/row-tags' ); ?>

			<?php snax_get_template_part( 'posts/form-edit/row-legal' ); ?>

			<?php snax_get_template_part( 'posts/form-edit/row-actions' ); ?>
		</div><!-- .snax-form-side -->

		<?php do_action( 'snax_frontend_submission_form_end', $snax_post_format ); ?>
	</form>

<?php do_action( 'snax_after_frontend_submission_form', $snax_post_format ); ?>
