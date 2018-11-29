<?php
/**
 * Item legal row.
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>
<?php if ( snax_legal_agreement_required() ) : ?>
	<p class="snax-new-item-row-legal">
		<label>
			<input type="checkbox" name="snax-item-legal" required autocomplete="off" /> <?php esc_html_e( 'I agree with the terms and conditions.', 'snax' ); ?>
		</label>

		<span class="snax-validation-tip"><?php esc_html_e( 'This field is required', 'snax' ); ?></span>

		<?php snax_render_legal_page_link(); ?>
	</p>
<?php endif;
