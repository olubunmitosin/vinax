<?php
/**
 * Note - WP uploads dir is not writable
 *
 * @package snax 1.11
 * @subpackage Plugin
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-note snax-note-warning">
	<div class="snax-note-icon"></div>

	<h2 class="snax-note-title"><?php esc_html_e( 'The uploads directory is not writable.', 'snax' ); ?></h2>

	<p>
		<?php esc_html_e( 'Please contact with site\'s owner.', 'snax' ); ?>
	</p>
</div>

<?php if ( current_user_can( 'administrator' ) ) : ?>
	<p>
		<?php esc_html_e( 'Please make the below directory writable for your web server:.', 'snax' ); ?>
		<?php
		$snax_upload_dir = wp_upload_dir();

		echo esc_html( $snax_upload_dir['basedir'] );
		?>
	</p>
	<p>
		<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank"><?php esc_html_e( 'Read more about correct file permissions.', 'snax' ); ?></a>
	</p>
<?php endif; ?>
