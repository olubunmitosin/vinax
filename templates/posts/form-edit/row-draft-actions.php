<?php
/**
 * Snax News Post Row Actions
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>

<div class="snax-draft-post-row-actions">
	<?php $snax_preview_url = snax_get_post_preview_url(); ?>
	<input type="submit" name="snax-save-draft" value="<?php esc_attr_e( 'Save Draft', 'snax' ); ?>" class="snax-button snax-button-save-post" />
	<button data-snax-preview-url="<?php echo esc_url( $snax_preview_url ); ?>" class="snax-button snax-button-preview"<?php disabled( empty( $snax_preview_url ) ); ?>><?php esc_attr_e( 'Preview', 'snax' ); ?></button>
</div>
