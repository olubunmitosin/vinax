<?php
/**
 * Snax Image Card
 *
 * @package snax 1.11
 * @subpackage FrontendSubmission
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="snax-link snax-object" data-snax-id="<?php the_ID(); ?>">

	<div class="snax-object-container">

		<?php do_action( 'snax_before_card_media' ); ?>

		LINK HERE

		<?php do_action( 'snax_after_card_media' ); ?>

	</div>

	<div class="snax-object-actions">
		<?php snax_render_item_delete_link( array(
			'classes' => array(
				'snax-object-action',
				'snax-link-action',
				'snax-link-action-delete',
			),
		) ); ?>
	</div>
</div>
