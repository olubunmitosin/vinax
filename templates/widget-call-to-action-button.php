<?php
/**
 * Snax Call to action widget
 *
 * @package snax 1.11
 * @subpackage Theme
 */
$url = snax_get_frontend_submission_page_url();
$url = explode( '?', $url );
$prefix = snax_get_url_var_prefix();
if ( $prefix ) {
	$prefix .= '_';
}
?>
<div class="snax-cta">
	<?php do_action( 'snax_cta_start' ); ?>

	<div class="snax-cta-body">

		<form action="<?php echo esc_url( $url[0] ); ?>" method="get">
			<button class="snax-button snax-button-create snax-format-<?php echo esc_attr( $snax_cta_icon );?>"><i class="snax-format-icon"></i><?php echo esc_html( $snax_cta_button_label ); ?></button>
			<?php if ( 'all' !== $snax_cta_type ) :?>
				<input hidden name="<?php echo esc_attr( $prefix );?>_format" value="<?php echo esc_attr( $snax_cta_type ); ?>">
			<?php endif;?>
			<?php if ( '' !== $snax_cta_list_type ) :?>
				<input hidden name="type" value="<?php echo esc_attr( $snax_cta_list_type ); ?>">
			<?php endif;?>
			<?php if ( $url[1] ) :
				$param = explode( '=', $url[1] );
			?>
				<input hidden name="<?php echo esc_attr( $param[0] ); ?>" value="<?php echo esc_attr( $param[1] ); ?>">
			<?php endif;?>
		</form>
	</div>

	<?php do_action( 'snax_cta_end' ); ?>
</div>
