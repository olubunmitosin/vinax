<?php
/**
 * Template for displaying single item title
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php if ( snax_show_item_author() || snax_show_item_date() ) : ?>
	<p>
		<?php esc_html_e( 'Posted', 'snax' ); ?>
		<?php if ( snax_show_item_author() ) : ?>
			<?php esc_html_e( 'by', 'snax' ); ?> <a href="<?php echo esc_url( snax_get_item_author_url() ); ?>">
				<strong><?php echo esc_html( get_the_author() ); ?></strong>
			</a>,
		<?php endif; ?>

		<?php if ( snax_show_item_date() ) : ?>
			<?php esc_attr_e( 'at' ) ?> <?php echo esc_html( get_the_time( get_option( 'date_format' ) ) . ', ' . get_the_time( get_option( 'time_format' ) ) ); ?>
		<?php endif; ?>
	</p>
<?php endif; ?>
