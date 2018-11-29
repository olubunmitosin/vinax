<?php
/**
 * Demo image
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<?php
$snax_demo_post_id = snax_get_demo_post_id( 'image' );

if ( false === $snax_demo_post_id ) {
	return;
}
?>
<div class="snax-demo-format snax-demo-format-image">
	<a class="snax-demo-format-content" href="">

		<span><?php echo get_the_post_thumbnail( $snax_demo_post_id, 'thumbnail', array( 'data-snax-media-id' => get_post_thumbnail_id( $snax_demo_post_id ) ) ); ?></span>
	</a>

	<a href="#" class="snax-demo-button"><?php esc_html_e( 'Start with example', 'snax' ); ?></a>

	<p>
		<?php esc_html_e( 'or', 'snax' ); ?>
	</p>
</div>

