<?php
/**
 * Snax News Post Row Title
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<div class="snax-edit-post-row-title<?php echo snax_has_field_errors( 'title' ) ? ' snax-validation-error' : ''; ?>">
	<label for="snax-post-title"><?php esc_html_e( 'Title', 'snax' ); ?></label>

	<!-- Visible only if wrapper has error class added -->
	<span class="snax-validation-tip"><?php esc_html_e( 'This field is required', 'snax' ); ?></span>

	<h1 id="snax-post-title-editable" contenteditable="true" data-snax-placeholder="<?php esc_attr_e( 'Enter title here&hellip;', 'snax' ); ?>"></h1>

	<input style="display: none;" name="snax-post-title"
		   id="snax-post-title"
		   type="text"
		   value="<?php echo esc_attr( snax_get_field_values( 'title' ) ); ?>"
		   placeholder="<?php esc_attr_e( 'Enter title&hellip;', 'snax' ); ?>"
		   autocomplete="off"
		   maxlength="<?php echo esc_attr( snax_get_post_title_max_length() ); ?>"
	/>
</div>

