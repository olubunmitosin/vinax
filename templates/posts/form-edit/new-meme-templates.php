<?php
/**
 * New image part of the new post form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
$templates = get_posts( array(
	'posts_per_page'   => -1,
	'post_type' => snax_get_meme_template_post_type(),
	)
);
?>
<ul>
<?php foreach ( $templates as $template ) :
	if ( has_post_thumbnail( $template ) ) :
	?>
	<div href="#" class="snax-meme-template snax-meme-template-<?php echo esc_attr( $template->ID ); ?>" data-snax-template="<?php echo esc_attr( $template->ID ); ?>" data-snax-template-img="<?php echo esc_attr( get_post_thumbnail_id( $template ) ); ?>">
		<li><div class="snax-meme-templates-item">
			<div class="snax-meme-templates-item-image"><?php echo get_the_post_thumbnail( $template, 'medium' );?></div>
			<h2 class="snax-meme-templates-item-title"><?php echo esc_html( $template->post_title ); ?></h2>
		</div></li>
	</div>
<?php
	endif;
endforeach;?>
</ul>
