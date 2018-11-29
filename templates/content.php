<?php
/**
 * Single Item Content Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>
<article <?php post_class(); ?>>
	<?php $snax_post_id = wp_get_post_parent_id( $post->ID ); ?>

	<div class="snax-item-box">
		<header class="snax-item-header">
			<?php snax_render_item_position(); ?>

			<?php the_title( sprintf( '<h3 class="snax-item-title"><a href="%s" id="snax-item-%d" rel="bookmark">', esc_url( get_permalink() ), intval( get_the_ID() ) ), '</a></h3>' ); ?>

			<?php if ( current_user_can( 'snax_edit_posts', get_the_ID() ) ): ?>
				<a href="<?php echo esc_url( snax_get_post_edit_url() ); ?>"><?php esc_html_e( 'Edit', 'snax' ); ?></a>
			<?php endif; ?>
		</header>

		<?php do_action( 'snax_before_item_media' ); ?>

		<div class="snax-item-media">
			<div class="snax-item-media-container">
				<?php switch ( snax_get_item_format() ) {
					case 'image':
						?>
						<a class="snax-item-media-link" href="<?php echo esc_url( get_permalink() ); ?>">
							<?php the_post_thumbnail( snax_get_item_image_size() ); ?>
						</a>

						<?php if ( snax_item_has_source() ) : ?>
						<p class="snax-item-media-meta">
							<a href="<?php echo esc_url( snax_get_item_source() ); ?>" target="_blank"
							   rel="nofollow"><?php esc_html_e( 'Source', 'snax' ); ?></a>
						</p>
					<?php endif; ?>
						<?php
						break;

					case 'embed':
						?>
						<div class="snax-item-embed-code">
							<?php snax_render_item_embed_code(); ?>
						</div>
						<?php
						break;
}					if ( 'post' === $post->post_type ) {?>
						<a class="snax-item-media-link" href="<?php echo esc_url( get_permalink() ); ?>">
							<?php the_post_thumbnail( snax_get_item_image_size() ); ?>
						</a>

					<?php 	}
				?>
			</div>

			<?php if ( snax_has_item_description() ) : ?>
				<div class="snax-item-media-desc">
					<?php snax_item_description(); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'snax_after_item_media' ); ?>

		<p class="snax-item-meta">
			<?php
				snax_render_item_author( array(), true );
				snax_render_item_date( true );
			?>
		</p>
	</div>

	<?php if ( is_single( $snax_post_id ) ) : ?>
		<div class="snax-item-actions">
			<?php
			if ( snax_is_post_open_for_voting() ) :
				snax_render_voting_box();
			endif;
			?>

			<div class="snax-item-share">
				<a class="snax-item-share-toggle" href="#"><?php esc_html_e( 'Share', 'snax' ); ?></a>
				<div class="snax-item-share-content">
					<?php snax_item_share_links(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</article>
<hr/>
