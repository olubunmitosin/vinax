<?php
/**
 * New item form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<form id="snax-new-item-embed" class="snax-form snax-form-without-media snax-form-prior-media snax-new-item">
	<?php if ( 2 > count( snax_get_new_item_forms() ) ) : ?>
		<h2 class="snax-new-item-title"><?php esc_html_e( 'Add New Embed', 'snax' ); ?></h2>
	<?php endif; ?>

	<?php snax_get_template_part( 'items/form-edit/row-title' ); ?>

	<?php // @csstodo - zmienic snax-media na snax-media-row. ?>
	<div class="snax-media">

		<span class="snax-validation-tip"><?php esc_html_e( 'This field is required', 'snax' ); ?></span>

		<div class="snax-upload-preview">
			<div class="snax-upload-preview-inner"></div>
			<a href="#" class="snax-upload-preview-delete"><?php esc_html_e( 'Delete', 'snax' ); ?></a>
		</div>

		<p class="snax-new-item-row-embed-code">
			<label><?php esc_html_e( 'Embed URL', 'snax' ); ?></label>
			<span class="snax-validation-tip"></span>
			<textarea name="snax-item-embed-code" rows="3" cols="40"
		          placeholder="<?php esc_attr_e( 'Paste link or embed code&hellip;', 'snax' ); ?>" autocomplete="off"></textarea>
			<span class="snax-hint"><?php esc_html_e( 'e.g.: https://www.youtube.com/watch?v=WwoKkq685Hk', 'snax' ); ?></span>
		</p>

		<?php snax_embed_supported_services();?>

		<div class="snax-upload-icon"><?php esc_html_e( 'Processing...', 'snax' ); ?></div>

	</div>

	<?php snax_get_template_part( 'items/form-edit/row-source' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-description' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-referral' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-legal' ); ?>



	<p class="snax-new-item-row-actions">
		<?php if ( current_user_can( 'snax_publish_items' ) ) : ?>
			<input type="submit" id="snax-add-embed-item" value="<?php esc_attr_e( 'Publish', 'snax' ); ?>"/>
		<?php else : ?>
			<input type="submit" id="snax-add-embed-item" value="<?php esc_attr_e( 'Submit for Review', 'snax' ); ?>"/>
		<?php endif; ?>
	</p>

	<?php do_action( 'snax_end_form_edit_item_embed' ); ?>
</form>
