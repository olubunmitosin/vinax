<?php
/**
 * New item form
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<p class="snax-quiz-actions">
	<a class="g1-button g1-button-l g1-button-wide g1-button-solid" href="<?php
		$anchor = '#snax-new-item-wrapper-' . $post->ID;
		echo esc_attr( get_permalink( ) . $anchor );
	?>">
		<?php esc_html_e( 'Add your submission', 'snax' ); ?>
	</a>
</p>
