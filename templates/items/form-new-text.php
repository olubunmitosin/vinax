<?php
/**
 * New item form
 *
 * @package snax
 * @subpackage Theme
 */

?>
<form id="snax-new-item-text" class="snax-form snax-form-without-media snax-new-item">
	<?php if ( 2 > count( snax_get_new_item_forms() ) ) : ?>
		<h2 class="snax-new-item-title"><?php esc_html_e( 'Add New Text', 'snax' ); ?></h2>
	<?php endif; ?>

	<?php snax_get_template_part( 'items/form-edit/row-title' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-description' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-referral' ); ?>
	<?php snax_get_template_part( 'items/form-edit/row-legal' ); ?>

	<p class="snax-new-item-row-actions">
		<?php if ( current_user_can( 'snax_publish_items' ) ) : ?>
			<input type="submit" id="snax-add-text-item" value="<?php esc_attr_e( 'Publish', 'snax' ); ?>"/>
		<?php else : ?>
			<input type="submit" id="snax-add-text-item" value="<?php esc_attr_e( 'Submit for Review', 'snax' ); ?>"/>
		<?php endif; ?>
	</p>

	<?php do_action( 'snax_end_form_edit_item_text' ); ?>
</form>
