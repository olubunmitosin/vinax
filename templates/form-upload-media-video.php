<?php
/**
 * Upload media form
 *
 * @package snax
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_upload_dir = wp_upload_dir();

if ( ! wp_is_writable( $snax_upload_dir['basedir'] ) ) {
	snax_get_template_part( 'notes/uploads-dir-not-writable' );
	return;
}
?>

<div class="snax-upload">

	<?php do_action( 'snax_before_upload_form' ); ?>

	<input type="hidden" name="snax-add-media-item-nonce"
	       value="<?php echo esc_attr( wp_create_nonce( 'snax-add-media-item' ) ); ?>"/>

	<input type="hidden" name="snax-media-item-type" value="video" />

	<?php snax_media_upload_form( array(
		'media_type'           => 'video',
		'image_upload_allowed' => false,
		'audio_upload_allowed' => false,
		'upload_by_url'        => false,
	) ); ?>

	<?php do_action( 'snax_after_upload_form' ); ?>

</div>
