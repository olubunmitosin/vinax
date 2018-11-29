<?php
/**
 * Snax Text Card
 *
 * @package snax
 * @subpackage FrontendSubmission
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}

$snax_parent_format =snax_get_item_parent_format( get_the_ID() );
?>

<div class="snax-card snax-card-text snax-card-blur" data-snax-id="<?php the_ID(); ?>">
	<div class="snax-card-position"><?php snax_the_card_position(); ?></div>

	<div class="snax-card-header">
		<div class="snax-card-arrows">
			<a class="snax-card-up" href="#"><?php esc_html_e( 'Up', 'snax' ); ?></a>
			<a class="snax-card-down" href="#"><?php esc_html_e( 'Down', 'snax' ); ?></a>
		</div>

		<?php snax_render_item_delete_link( array(
			'classes' => array(
				'snax-card-action',
				'snax-card-action-delete',
			),
		) ); ?>
	</div>

	<div class="snax-card-body">
		<?php
		$snax_card_title_id = 'snax-card-title-' . get_the_ID();
		?>
		<p class="snax-card-title">
			<label for="<?php echo esc_attr( $snax_card_title_id ); ?>"><?php esc_html_e( 'Title', 'snax' ); ?></label>
			<input name="snax-title"
			       id="<?php echo esc_attr( $snax_card_title_id ); ?>"
			       type="text"
			       placeholder="<?php esc_html_e( 'Enter title&hellip;', 'snax' ); ?>"
			       maxlength="<?php echo esc_attr( snax_get_item_title_max_length() ); ?>"
			       value="<?php echo esc_attr( snax_get_the_card_title() ); ?>"/>
		</p>

		<p class="snax-card-description">
			<?php
			$snax_card_description_id = 'snax-card-description-' . get_the_ID();
			?>
			<label
				for="<?php echo esc_attr( $snax_card_description_id ); ?>"><?php esc_html_e( 'Description', 'snax' ); ?></label>
			<textarea id="<?php echo esc_attr( $snax_card_description_id ); ?>"
			          <?php if ( snax_froala_for_items() ) { echo 'class="froala-editor-simple"'; }?>
			          rows="4"
			          maxlength="<?php echo esc_attr( snax_get_item_content_max_length() ); ?>"
			          placeholder="<?php esc_html_e( 'Enter some description&hellip;', 'snax' ); ?>"><?php echo esc_textarea( snax_get_the_card_description() ); ?></textarea>
		</p>

		<?php
		$disallow_adding_referrals = ! snax_allow_snax_authors_to_add_referrals( $snax_parent_format ) && current_user_can( 'snax_author' );
		if ( ! $disallow_adding_referrals ) :
		?>
		<p class="snax-card-referral">
			<?php $snax_card_ref_link = snax_get_the_card_ref_link(); ?>
			<input id="snax-card-<?php the_ID(); ?>-has-ref-link" name="snax-has-ref-link" type="checkbox"<?php checked( ! empty( $snax_card_ref_link ) ); ?> /> <label for="snax-card-<?php the_ID(); ?>-has-ref-link"><?php esc_html_e( 'Want to add referral link?', 'snax' ); ?></label>
			<input name="snax-ref-link"
			       type="text"
			       placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"
			       maxlength="<?php echo esc_attr( snax_get_item_ref_link_max_length() ); ?>"
			       value="<?php echo esc_url( snax_get_the_card_ref_link() ); ?>"/>
		</p>
		<?php endif; ?>

	</div>
</div>
