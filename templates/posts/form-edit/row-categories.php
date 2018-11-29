<?php
/**
 * Snax News Post Row Categories
 *
 * @package snax 1.11
 * @subpackage Theme
 */

global $snax_post_format;
$snax_categories_classes = array();

if ( snax_has_field_errors( 'category_id' ) ) {
	$snax_categories_classes[] =  'snax-validation-error';
}

if ( snax_is_category_field_required( $snax_post_format ) ) {
	$snax_categories_classes[] = 'snax-field-required';
}
?>

<div class="snax-edit-post-row-categories <?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_categories_classes ) ); ?>">

	<!-- Visible only if wrapper has error class added -->
	<span class="snax-validation-tip"><?php esc_html_e( 'This field is required', 'snax' ); ?></span>

	<label for="snax-post-category"><?php esc_html_e( 'Category', 'snax' ); ?></label>
	<?php
	wp_dropdown_categories( array(
		'name'             	=> 'snax-post-category[]',
		'id'               	=> 'snax-post-category',
		'selected'         	=> snax_get_field_values( 'category_id' ),
		'show_option_none' 	=> esc_html__( '- Select category -', 'snax' ),
		'include'          	=> snax_get_post_included_categories( $snax_post_format ),
		'exclude'          	=> snax_get_post_excluded_categories(),
		'orderby'          	=> 'name',
		'hide_empty'		=> '0',
		'hierarchical'       => 'true',
	) );
	?>
	<?php // @todo - remove from here. ?>
	<?php if ( snax_is_category_multi_allowed( $snax_post_format ) ) : ?>
	<script>
		(function ($) {
			$('#snax-post-category').attr('multiple', 'multiple').attr('size', 5);
			$('#snax-post-category option:first-child').prop('selected', false);
		})(jQuery);
	</script>
	<?php endif; ?>
</div>
