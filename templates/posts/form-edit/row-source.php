<?php
/**
 * Snax News Post Row Tags
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-edit-post-row-source">
	<?php $snax_source = snax_get_field_values( 'source' ); ?>
	<input id="snax-post-has-source" type="checkbox" name="snax-post-has-source" <?php checked( ! empty( $snax_source ) ); ?> /> <label for="snax-post-has-source"><?php esc_html_e( 'Not your original work? Note the source', 'snax' ); ?></label>
	<input id="snax-post-source"
	       type="text"
	       name="snax-post-source"
	       placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"
	       value="<?php echo esc_url( snax_get_field_values( 'source' ) ); ?>"/>
</div>