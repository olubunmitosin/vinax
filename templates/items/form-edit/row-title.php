<?php
/**
 * Item title row.
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>
<p class="snax-new-item-row-title">
	<label><?php esc_html_e( 'Title', 'snax' ); ?></label>

	<input name="snax-item-title"
	       type="text"
	       value=""
	       maxlength="<?php echo esc_attr( snax_get_item_title_max_length() ); ?>"
	       placeholder="<?php esc_attr_e( 'Enter title&hellip;', 'snax' ); ?>"
	       autocomplete="off"
	/>
</p>
