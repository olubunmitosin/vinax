<?php
/**
 * Item description row.
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>
<p class="snax-new-item-row-description">
	<label><?php esc_html_e( 'Description', 'snax' ); ?></label>
		<textarea name="snax-item-description"
				  <?php if ( snax_froala_for_list_items() ) { echo 'class="froala-editor-simple"'; }?>
		          rows="3"
		          cols="40"
		          maxlength="<?php echo esc_attr( snax_get_item_content_max_length() ); ?>"
		          placeholder="<?php esc_attr_e( 'Enter some description&hellip;', 'snax' ); ?>"></textarea>
</p>
