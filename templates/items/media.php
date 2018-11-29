<?php
/**
 * Template for displaying single item media
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
/**
 * Fires before the display of the item media
 */
do_action( 'snax_before_item_media' );
?>

<div class="snax-item-media">
	<div class="snax-item-media-container">
		<?php switch ( snax_get_item_format() ) {
			case 'image':
				snax_image_media( array(
					'size'          => snax_get_item_image_size(),
					'class'         => 'snax-item-media-link',
					'allow_video'   => true,
				) );
				?>
				<?php if ( snax_item_has_source() ) : ?>
				<p class="snax-item-media-meta">
					<a href="<?php echo esc_url( snax_get_item_source() ); ?>" target="_blank"
					   rel="nofollow"><?php esc_html_e( 'Source', 'snax' ); ?></a>
				</p>
				<?php endif; ?>
				<?php
				break;

			case 'audio':
				snax_audio_media();
				?>
				<?php if ( snax_item_has_source() ) : ?>
				<p class="snax-item-media-meta">
					<a href="<?php echo esc_url( snax_get_item_source() ); ?>" target="_blank"
					   rel="nofollow"><?php esc_html_e( 'Source', 'snax' ); ?></a>
				</p>
				<?php endif; ?>
				<?php
				break;

			case 'video':
				snax_video_media();
				?>
				<?php if ( snax_item_has_source() ) : ?>
				<p class="snax-item-media-meta">
					<a href="<?php echo esc_url( snax_get_item_source() ); ?>" target="_blank"
					   rel="nofollow"><?php esc_html_e( 'Source', 'snax' ); ?></a>
				</p>
				<?php endif; ?>
				<?php
				break;

			case 'embed':
				?>
				<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', snax_get_item_embed_code_classes() ) ); ?>">
					<?php snax_render_item_embed_code(); ?>
				</div>
				<?php if ( snax_item_has_source() ) : ?>
				<p class="snax-item-media-meta">
					<a href="<?php echo esc_url( snax_get_item_source() ); ?>" target="_blank"
					   rel="nofollow"><?php esc_html_e( 'Source', 'snax' ); ?></a>
				</p>
				<?php endif; ?>
				<?php
				break;
		} ?>
	</div>

	<?php if ( snax_show_item_media_description() ) : ?>
		<div class="snax-item-media-desc">
			<?php snax_item_description(); ?>
		</div>
	<?php endif; ?>
</div><!-- .snax-item-media -->

<?php
/**
 * Fires after the display of the item media
 */
do_action( 'snax_after_item_media' );
?>
