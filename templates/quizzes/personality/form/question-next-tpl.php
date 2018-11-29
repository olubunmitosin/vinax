<?php
/**
 * Next question template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<div class="quizzard-question quizzard-next-question">

	<div class="quizzard-question-header">
		<input class="quizzard-question-title" type="text" data-quizzard-placeholder-first="<?php echo esc_attr_x( 'Enter first question here', 'Placeholder', 'snax' ); ?>" data-quizzard-placeholder="<?php echo esc_attr_x( 'Enter next question here', 'Placeholder', 'snax' ); ?>" placeholder="<?php echo esc_attr_x( 'Enter next question here', 'Placeholder', 'snax' ); ?>" />
		<a href="#" class="button button-secondary button-disabled quizzard-add"><?php esc_html_e( 'Add', 'snax' ); ?></a>
	</div>
</div>
