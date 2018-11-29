<?php
/**
 * Snax Post Row Referral Link
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<?php
global $snax_post_format;

$disallow_adding_referrals = ! snax_allow_snax_authors_to_add_referrals( $snax_post_format ) && current_user_can( 'snax_author' );
if ( ! $disallow_adding_referrals ) :
?>
<div class="snax-edit-post-row-referral">
	<?php $snax_ref_link = snax_get_field_values( 'ref_link' ); ?>
	<input id="snax-post-has-ref-link" type="checkbox" name="snax-post-has-ref-link" <?php checked( ! empty( $snax_ref_link ) ); ?> /> <label for="snax-post-has-ref-link"><?php esc_html_e( 'Want to add referral link?', 'snax' ); ?></label>
	<input id="snax-post-ref-link"
		type="text"
		name="snax-post-ref-link"
		placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"
		value="<?php echo esc_url( snax_get_field_values( 'ref_link' ) ); ?>"/>
</div>
<?php endif; ?>
