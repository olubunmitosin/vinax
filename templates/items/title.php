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

$snax_item_permalink = snax_get_item_permalink();
?>

<h3 class="snax-item-title">
<?php if ( $snax_item_permalink ) : ?>
	<?php $snax_link_target = isset( $snax_item_permalink['target'] ) ? ' target="' . $snax_item_permalink['target'] . '"' : ''; ?>

	<a href="<?php echo esc_url( $snax_item_permalink['url'] ); ?>"<?php echo esc_attr( $snax_link_target ); ?> id="snax-itemli-<?php echo (int) get_the_ID(); ?>" rel="bookmark">
<?php endif; ?>
	<?php
		snax_render_item_position();
		the_title( '', '' );
	?>
<?php if ( $snax_item_permalink ) : ?>
	</a>
<?php endif; ?>
</h3>
