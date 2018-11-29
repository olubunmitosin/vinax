<?php
/**
 * Snax Post Row Meme Bottom Text
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-meme-bottom-text">
	<input name="snax-post-meme-bottom-text"
	       id="snax-post-meme-bottom-text"
	       type="text"
	       value="<?php echo esc_attr( snax_get_field_values( 'meme-bottom-text' ) ) ?>"
	       placeholder="<?php esc_html_e( 'Bottom text&hellip;', 'snax' ) ?>"
	       autocomplete="off"
		/>
</div>
