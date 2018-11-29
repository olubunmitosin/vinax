<?php
/**
 * Item source row.
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>
<p class="snax-new-item-row-source">
	<input type="checkbox" name="snax-item-has-source" /> <label for="snax-item-has-source"><?php esc_html_e( 'Not your original work? Note the source', 'snax' ); ?></label>
	<input name="snax-item-source"
	       type="text"
	       maxlength="<?php echo esc_attr( snax_get_item_source_max_length() ); ?>"
	       placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"/>
</p>