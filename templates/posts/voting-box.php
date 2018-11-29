<?php
/**
 * Snax Post Voting Box Template Part
 *
 * @package snax 1.11
 * @subpackage Theme
 */

?>

<div class="snax-voting-container">
	<h2 class="snax-voting-container-title"><?php esc_html_e( 'Leave your vote', 'snax' ); ?></h2>
	<?php snax_render_voting_box( null, 0, 'snax-voting-large' ); ?>
</div>
