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

<?php switch ( snax_get_item_format() ) {
	case 'image':
		snax_image_media( array(
			'size' => snax_get_item_image_size(),
		) );
		?>
		<?php if ( snax_item_has_source() ) : ?>
		<p>
			<a href="<?php echo esc_url( snax_get_item_source() ); ?>"><?php esc_html_e( 'Source', 'snax' ); ?></a>
		</p>
	<?php endif; ?>
		<?php
		break;

	case 'embed':
		snax_render_item_embed_code();
		break;
	}
?>

<?php if ( snax_show_item_media_description() ) : ?>
	<?php snax_item_description(); ?>
<?php endif; ?>
