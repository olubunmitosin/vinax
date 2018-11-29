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

	<?php if ( snax_has_field_errors( 'title' ) ) : ?>
		<span class="snax-validation-tip"><?php echo esc_html( snax_get_field_errors( 'title' ) ); ?></span>
	<?php endif; ?>

	<input name="snax-post-title"
	       id="snax-post-title"
	       type="text"
	       value="<?php echo esc_attr( snax_get_field_values( 'title' ) ); ?>"
	       placeholder="<?php esc_attr_e( 'Enter title&hellip;', 'snax' ); ?>"
	       autocomplete="off"
	       maxlength="<?php echo esc_attr( snax_get_post_title_max_length() ); ?>"
	       required
	/>
</div>

