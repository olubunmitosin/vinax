<?php
/**
 * Snax Post Row Content
 *
 * @package snax 1.11
 * @subpackage Plugin
 */
?>
<div class="snax-edit-post-row-content">
	<textarea id="snax-post-description"
			  class="snax-content-editor"
			  name="snax-post-description"
			  rows="3"
			  cols="40"
			  maxlength="<?php echo esc_attr( snax_get_post_content_max_length() ); ?>"
			  placeholder="<?php esc_attr_e( 'Enter some text&hellip;', 'snax' ); ?>"><?php echo esc_textarea( snax_get_field_values( 'description' ) ); ?></textarea>

	<input type="hidden" id="snax-media-form-nonce" value="<?php echo esc_attr( wp_create_nonce( 'media-form' ) ); ?>"/>
	<input type="hidden" name="snax-delete-media-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snax-delete-media' ) ); ?>"/>
</div>
