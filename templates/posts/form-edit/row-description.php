<?php
/**
 * Snax News Post Row Tags
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-description">
	<label for="snax-post-description"><?php esc_html_e( 'Description', 'snax' ); ?></label>
	<textarea id="snax-post-description"
	          class="froala-editor-simple"
	          name="snax-post-description"
	          rows="3"
	          cols="40"
	          maxlength="<?php echo esc_attr( snax_get_post_description_max_length() ); ?>"
	          placeholder="<?php esc_attr_e( 'Enter some description&hellip;', 'snax' ); ?>"><?php echo esc_textarea( snax_get_field_values( 'description' ) ); ?></textarea>
</div>
