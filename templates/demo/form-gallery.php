<?php
/**
 * Demo images
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<?php
$snax_demo_post_id = snax_get_demo_post_id( 'gallery' );

if ( false === $snax_demo_post_id ) {
	return;
}

$snax_demo_items = snax_get_items( $snax_demo_post_id );
?>
<div class="snax-demo-format snax-demo-format-images">
	<a class="snax-demo-format-content" href="">

		<?php foreach ( $snax_demo_items as $snax_demo_item ) : ?>

		<span><?php echo get_the_post_thumbnail( $snax_demo_item->ID, 'thumbnail', array( 'data-snax-media-id' => get_post_thumbnail_id( $snax_demo_item->ID ) ) ); ?></span>

		<?php endforeach; ?>
	</a>

	<a href="#" class="snax-demo-button"><?php esc_html_e( 'Start with example', 'snax' ); ?></a>

	<p>
		<?php esc_html_e( 'or', 'snax' ); ?>
	</p>
</div>
<script type="text/javascript">
	(function(ctx) {
		var config = {};

		<?php foreach ( $snax_demo_items as $snax_demo_item ) : ?>

		<?php $snax_demo_item_media_id = get_post_thumbnail_id( $snax_demo_item->ID ); ?>

		config[<?php echo intval( $snax_demo_item_media_id ); ?>] = <?php echo json_encode( array(
			'title'         => $snax_demo_item->post_title,
			'source'        => snax_get_item_source( $snax_demo_item ),
			'refLink'       => snax_get_item_ref_link( $snax_demo_item ),
			'description'   => $snax_demo_item->post_content,
		) ); ?>;

		<?php endforeach; ?>

		ctx.snaxDemoItemsConfig = config;
	})(window);
</script>