<?php
/**
 * Poll results
 *
 * @package snax 1.11
 * @subpackage Forms
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<div class="quizzard-results quizzard-tab-content" id="quizzard-results">

	<div class="quizzard-results-header">
		<h2><?php esc_html_x( 'Results', 'result collection', 'snax' );?></h2>

		<button class="button button-secondary button-small quizzard-results-reset"><?php esc_html_e( 'Generate', 'snax' ); ?></button>
	</div>

	<ul class="quizzard-r-items" id="quizzard-r-items">
		<li class="quizzard-r-item quizzard-next-r-item">
			<?php snax_get_template_part( 'polls/versus/form-frontend/result-next-tpl' ); ?>
		</li>
	</ul><!-- .quizzards-r-items -->

</div>