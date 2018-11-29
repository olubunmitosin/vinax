<?php
/**
 * Twitter share link
 *
 * @package snax 1.11
 * @subpackage Share
 */

// Prevent direct script access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct script access allowed' );
}
error_log( print_r( get_the_ID(), true ) );
?>

<div class="snax-poll-answers-share"><?php
echo esc_html__( 'Share your vote on', 'snax' );
$links = apply_filters( 'snax_poll_share_links', array( 'facebook', 'twitter' ) );
foreach ( $links as $link_id ) {
	snax_get_template_part( 'share-links/poll-' . $link_id );
}
?>
</div>
