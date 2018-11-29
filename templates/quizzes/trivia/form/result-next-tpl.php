<?php
/**
 * Next result template part
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>
<div class="quizzard-result quizzard-next-result">

	<div class="quizzard-result-header">
		<input class="quizzard-result-title" type="text" size="5" data-quizzard-placeholder-first="<?php echo esc_attr_x( 'Enter first result here', 'Placeholder', 'snax' ); ?>" data-quizzard-placeholder="<?php echo esc_attr_x( 'Enter next result here', 'Placeholder', 'snax' ); ?>" placeholder="<?php echo esc_attr_x( 'Enter next result here', 'Placeholder', 'snax' ); ?>" />
		<a href="#" class="button button-secondary button-disabled quizzard-add"><?php esc_html_e( 'Add', 'snax' ); ?></a>
	</div>
</div>
