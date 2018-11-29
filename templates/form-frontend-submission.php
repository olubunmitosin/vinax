<?php
/**
 * New post form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php if ( 1 < snax_get_format_count() ) : ?>
	<?php
	$snax_class = array(
		'snax-formats',
		'snax-formats-' . snax_get_format_count(),
	);
	?>
	<ul class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
		<?php foreach ( snax_get_active_formats() as $snax_format_id => $snax_format_args ) : ?>
			<li>
				<a class="snax-format snax-format-<?php echo sanitize_html_class( $snax_format_id ); ?>"
				   href="<?php echo esc_url( $snax_format_args['url'] ); ?>">
					<i class="snax-format-icon"></i>
					<h3 class="snax-format-label"><?php echo esc_html( $snax_format_args['labels']['add_new'] ); ?></h3>
					<p class="snax-format-desc"><?php echo esc_html( $snax_format_args['description'] ); ?></p>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
