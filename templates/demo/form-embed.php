<?php
/**
 * Demo embed
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<?php
$snax_demo_post_id = snax_get_demo_post_id( 'embed' );

if ( false === $snax_demo_post_id ) {
	return;
}

$snax_demo_embed_url = snax_get_first_url_in_content( $snax_demo_post_id );
?>
<div class="snax-demo-format snax-demo-format-embed">
	<a class="snax-demo-format-content" href="<?php echo esc_url( $snax_demo_embed_url ); ?>">

		<span><?php echo get_the_post_thumbnail( $snax_demo_post_id, 'thumbnail' ); ?></span>
	</a>

	<a href="<?php echo esc_url( $snax_demo_embed_url ); ?>" class="snax-demo-button"><?php esc_html_e( 'Start with example', 'snax' ); ?></a>

	<p>
		<?php esc_html_e( 'or', 'snax' ); ?>
	</p>
</div>
