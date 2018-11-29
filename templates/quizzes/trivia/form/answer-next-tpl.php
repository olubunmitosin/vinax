<?php
/**
 * Next answer template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<div class="quizzard-answer quizzard-next-answer">
	<div class="quizzard-answer-header">
	</div>

	<div class="quizzard-answer-media quizzard-answer-without-media">
	</div>

	<div class="quizzard-answer-body">
		<input type="text" class="quizzard-answer-title" placeholder="<?php echo esc_html_x( 'Enter next answer here', 'Placeholder', 'snax' ); ?>" />
		<a class="button button-disabled quizzard-add" href="#"><?php echo esc_html_e( 'Add', 'snax' ); ?></a>
	</div>
</div>
