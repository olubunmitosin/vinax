<?php
/**
 * Item referral row.
 *
 * @package snax 1.11
 * @subpackage Theme
 */
?>

<?php
$snax_format = snax_get_format( get_the_ID() );

$disallow_adding_referrals = ! snax_allow_snax_authors_to_add_referrals( $snax_format ) && current_user_can( 'snax_author' );
if ( ! $disallow_adding_referrals ) :
?>
<p class="snax-new-item-row-referral">
	<input type="checkbox" name="snax-item-has-ref-link" /> <label for="snax-item-has-ref-link"><?php esc_html_e( 'Want to add referral link?', 'snax' ); ?></label>
	<input name="snax-item-ref-link"
	       type="text"
	       maxlength="<?php echo esc_attr( snax_get_item_ref_link_max_length() ); ?>"
	       placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"/>
</p>
<?php endif; ?>
