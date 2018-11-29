<?php
/**
 * New item form
 *
 * @package snax
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php do_action( 'snax_frontend_submission_form_before_new_text' ); ?>

<input type="hidden" name="snax-add-text-item-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snax-add-text-item' ) ); ?>"/>

<div class="snax-new-text-item">

	<p class="snax-new-text-item-title">
		<input type="text" class="snax-text-item-title snax-do-not-submit" placeholder="<?php esc_attr_e( 'Enter title&hellip;', 'snax' ); ?>" maxlength="<?php echo esc_attr( snax_get_item_title_max_length() ); ?>" />
	</p>

	<p class="snax-new-text-item-description">
		<textarea
	          class="snax-text-item-description"
	          name="snax-item-description"
	          rows="3"
	          cols="40"
	          maxlength="<?php echo esc_attr( snax_get_post_description_max_length() ); ?>"
	          placeholder="<?php esc_attr_e( 'Enter some description&hellip;', 'snax' ); ?>"><?php echo esc_textarea( snax_get_field_values( 'description' ) ); ?></textarea>
	</p>

	<p class="snax-new-text-actions">
		<a href="#" class="g1-button g1-button-simple g1-button-m snax-add-text-item"><?php esc_html_e( 'Add', 'snax' ); ?></a>
	</p>
</div>

<?php do_action( 'snax_frontend_submission_form_after_new_text' ); ?>
