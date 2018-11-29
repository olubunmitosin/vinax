<?php
/**
 * Personality result template part
 *
 * @package snax 1.11
 * @subpackage Poll
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
?>

<?php
$snax_result  = get_post();
$snax_poll_id = $snax_result->post_parent;
?>

<div class="snax-poll-result snax-poll-result-locked">
	<div class="snax-poll-result-header">
		<h2 class="snax-poll-result-title"><?php esc_html_e( 'Share to see poll results', 'snax' ); ?></h2>
	</div>

	<div class="snax-poll-result-body">
		<div class="snax-poll-result-share">
			<?php snax_render_poll_share_to_unlock( $snax_poll_id, array(
				'description' => snax_get_poll_share_description(),
			) ); ?>
		</div>
	</div>
</div>
