<?php
/**
 * Snax News Post Row Tags
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-tags">
	<label for="snax-post-tags"><?php esc_html_e( 'Tags', 'snax' ); ?></label>
	<input name="snax-post-tags"
	       id="snax-post-tags"
	       type="text"
	       value="<?php echo esc_attr( snax_get_field_values( 'tags' ) ) ?>"
	       placeholder="<?php esc_html_e( 'Add tags&hellip;', 'snax' ) ?>"
	       autocomplete="off"
	/>
	<div class="snax-autocomplete"></div>
	<span class="snax-hint"><?php esc_html_e( 'Separate tags with Enter', 'snax' ); ?></span>
</div>
