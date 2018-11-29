<?php
/**
 * Snax Audio Card
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

<div class="snax-card snax-card-audio snax-card-blur" data-snax-id="<?php the_ID(); ?>">
	<div class="snax-card-position"><?php snax_the_card_position(); ?></div>

	<div class="snax-card-header">
		<div class="snax-card-arrows">
			<a class="snax-card-up" href="#"
			   title="<?php esc_attr_e( 'Move up', 'snax' ); ?>"><?php esc_html_e( 'Move up', 'snax' ); ?></a>
			<a class="snax-card-down" href="#"
			   title="<?php esc_attr_e( 'Move down', 'snax' ); ?>"><?php esc_html_e( 'Move down', 'snax' ); ?></a>
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

		<?php do_action( 'snax_before_card_media' ); ?>

		<div class="snax-card-media">
			<?php snax_the_card_audio(); ?>
		</div>

		<?php do_action( 'snax_after_card_media' ); ?>

		<p class="snax-card-source">
			<?php $snax_card_source = snax_get_the_card_source(); ?>
			<input id="snax-card-<?php the_ID(); ?>-has-source" name="snax-has-source" type="checkbox"<?php checked( ! empty( $snax_card_source ) ); ?> /> <label for="snax-card-<?php the_ID(); ?>-has-source"><?php esc_html_e( 'Not your original work? Note the source', 'snax' ); ?></label>
			<input name="snax-source"
			       type="text"
			       placeholder="<?php esc_attr_e( 'http://', 'snax' ) ?>"
			       maxlength="<?php echo esc_attr( snax_get_item_source_max_length() ); ?>"
			       value="<?php echo esc_url( snax_get_the_card_source() ); ?>"/>
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
