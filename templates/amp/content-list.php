<?php
/**
 * Snax List Content Template Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax snax-post-container">
<?php
$snax_items_args = array();
?>

<?php if ( snax_has_items( snax_get_post_id(), 'list', $snax_items_args ) ) : ?>

	<div class="snax-items">

	<?php $snax_index = 0; ?>

	<?php while ( snax_items() ) : snax_the_item(); ?>

		<article <?php post_class( 'snax-item' ); ?>>
			<header class="snax-item-header"></header>
				<?php
				// Filtering this with snax_amp_force_amp_permalinks causes memory error, bug in AMP plugin?
				snax_render_item_title();?>
			</header>

			<?php snax_get_template_part( 'items/media' ); ?>

			<p class="snax-item-meta">
				<?php snax_amp_render_item_author(); ?>
				<?php snax_render_item_date(); ?>
			</p>

			<div class="snax-item-actions">
				<?php snax_amp_render_voting_box(); ?>
				<?php snax_amp_render_referral_link(); ?>
			</div>

			<?php snax_amp_render_comments_box(); ?>
		</article>

		<?php $snax_index++; ?>

	<?php endwhile; ?>

	</div>

<?php endif;
snax_amp_render_new_item_form();
?>
</div>
