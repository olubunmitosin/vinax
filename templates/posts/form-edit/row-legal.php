<?php
/**
 * Snax News Post Row Legal
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<?php if ( snax_legal_agreement_required() ) : ?>
	<div class="snax-edit-post-row-legal<?php echo snax_has_field_errors( 'legal' ) ? ' snax-validation-error' : ''; ?>">

		<?php if ( snax_has_field_errors( 'legal' ) ) : ?>
			<span class="snax-validation-tip"><?php echo esc_html( snax_get_field_errors( 'legal' ) ); ?></span>
		<?php endif; ?>

		<label>
			<input type="checkbox" id="snax-post-legal" name="snax-post-legal" required <?php checked( snax_get_field_values( 'legal' ) ); ?> /> <?php esc_html_e( 'I agree with the terms and conditions.', 'snax' ); ?>
		</label>

		<?php snax_render_legal_page_link(); ?>
	</div>
<?php endif; ?>
