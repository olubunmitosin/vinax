<?php
/**
 * Snax Post Row Meme Top Text
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-meme-top-text">
	<input name="snax-post-meme-top-text"
	       id="snax-post-meme-top-text"
	       type="text"
	       value="<?php echo esc_attr( snax_get_field_values( 'meme-top-text' ) ) ?>"
	       placeholder="<?php esc_html_e( 'Top text&hellip;', 'snax' ) ?>"
	       autocomplete="off"
		/>
</div>
