<?php
/**
 * New item form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<form id="snax-new-item-image" class="snax-form snax-form-without-media snax-form-prior-media snax-new-item" xmlns="http://www.w3.org/1999/html">
	<?php if ( 2 > count( snax_get_new_item_forms() ) ) : ?>
		<h2 class="snax-new-item-title"><?php esc_html_e( 'Add New Image', 'snax' ); ?></h2>
	<?php endif; ?>

	<?php snax_get_template_part( 'items/form-edit/row-title' ); ?>

	<?php // @csstodo - zmienic snax-media na snax-media-row. ?>
	<div class="snax-media">

		<span class="snax-validation-tip"><?php esc_html_e( 'This field is required', 'snax' ); ?></span>

		<div class="snax-upload-preview" data-snax-media-id="<?php echo esc_attr( snax_get_user_uploaded_media_id( 'image' ) ); ?>">
			<div class="snax-upload-preview-inner"></div>
			<a href="#" class="snax-upload-preview-delete"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
		</div>

		<?php snax_get_template_part( 'form-upload-media', 'image' ); ?>

		<div class="snax-upload-icon"><?php esc_html_e( 'Processing...', 'snax' ); ?></div>

		<input type="hidden" name="snax-delete-media-nonce"
		       value="<?php echo esc_attr( wp_create_nonce( 'snax-delete-media' ) ); ?>"/>
	</div>

	<?php snax_get_template_part( 'items/form-edit/row-source' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-description' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-referral' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-legal' ); ?>



	<p class="snax-new-item-row-actions">
		<?php if ( current_user_can( 'snax_publish_items' ) ) : ?>
			<input type="submit" id="snax-add-image-item" value="<?php esc_attr_e( 'Publish', 'snax' ); ?>"/>
		<?php else : ?>
			<input type="submit" id="snax-add-image-item" value="<?php esc_attr_e( 'Submit for Review', 'snax' ); ?>"/>
		<?php endif; ?>
	</p>

	<?php do_action( 'snax_end_form_edit_item_image' ); ?>
</form>

